<?php

namespace App\Controllers;

use Hleb\Base\Controller;
use MatthiasMullie\Minify;
use App\Models\{FacetModel, SearchModel, BrokenLinkModel};
use App\Models\ItemModel;
use App\Content\CatalogService;
use Msg, Html, Meta, Parser;

use Loupe\Loupe\Config\TypoTolerance;
use Loupe\Loupe\Configuration;
use Loupe\Loupe\LoupeFactory;

use S2\Rose\Stemmer\PorterStemmerRussian;
use S2\Rose\Stemmer\PorterStemmerEnglish;
use S2\Rose\Entity\Indexable;
use S2\Rose\Indexer;

use S2\Rose\Entity\ExternalId;

class BuildController extends Controller
{
	protected $path;

	public function __construct()
	{
		$this->path = HLEB_GLOBAL_DIR . config('general', 'path_html');
	}

	public function index(): void
	{
		$this->buildCss(HLEB_GLOBAL_DIR . '/resources/views/assets/css/build.css', 'style');

		// Generic js
		foreach (config('general', 'path_js_admin') as $key => $putch) {
			$this->buildJs(HLEB_GLOBAL_DIR . $putch, $key);
		}

		// Separate style files that may not be included in the templates (example: catalog.css, rtl.css)
		// Отдельные файлы стилей, которые могут не войти в шаблоны (пример: catalog.css, rtl.css)
		foreach (config('general', 'path_css_admin') as $key => $putch) {
			$this->buildCss(HLEB_GLOBAL_DIR . $putch, $key);
		}

		// Автоперенос ассетов (CSS/JS/картинки) на статический сайт
		$this->copyAssetsToStatic();

		Msg::redirect(__('msg.change_saved'), 'success', url('tools'));
	}

	public function tools()
	{
		render(
			'/admin/pages/tools',
			[
				'meta'  => Meta::get(__('app.tools')),
				'data'  => [
					'sheet'         => 'tools',
				]
			]
		);
	}

	// Проверка источников фактов (HEAD-запрос). Обрабатывает порцию непроверенных.
	public function sourceCheck(): void
	{
		$sourceModel = new \App\Models\SourceModel();
		$checked = 0;

		foreach ($sourceModel->unchecked() as $source) {
			$result = $sourceModel->checkUrl($source['url']);
			$sourceModel->updateResult((int)$source['id'], $result['status'], $result['code'], (string)($result['title'] ?? ''));
			$checked++;
		}

		$stats = $sourceModel->stats();

		Msg::add(
			__('msg.sources_checked', [
				'checked' => $checked,
				'ok' => $stats['ok'],
				'broken' => $stats['broken'],
				'timeout' => $stats['timeout'],
				'left' => $stats['unchecked'],
			]),
			'success'
		);

		redirect(url('tools'));
	}

	// Генерация sitemap.xml
	public function buildSitemap(): void
	{
		$base = config('general', 'url_html');

		// Категории (главные страницы разделов)
		$facets = FacetModel::getTree('category', 'all');
		// Удалённые категории не включаем в sitemap
		$facets = array_filter($facets, fn($f) => (int)($f['facet_is_deleted'] ?? 0) !== 1);
		$urls = '';

		$urls .= "  <url><loc>{$base}/</loc><changefreq>daily</changefreq><priority>1.0</priority></url>\n";

		foreach ($facets as $facet) {
			$urls .= "  <url><loc>{$base}/" . trim($facet['facet_path'], '/') . "/</loc><priority>0.8</priority></url>\n";
		}

		// Страницы фактов
		$items = ItemModel::getItemAll();
		foreach ($items as $item) {
			$dir = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
			$path = trim($dir[2] ?? '', '/');
			if ($path === '') {
				continue;
			}
			$urls .= "  <url><loc>{$base}/{$path}/" . $item['item_slug'] . ".html</loc></url>\n";
		}

		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n" . $urls . "</urlset>";

		file_put_contents($this->path . '/sitemap.xml', $xml);

		// без редиректа — вызывается и из инкрементальной сборки
	}

	// Кнопка в админке: пересобрать sitemap и показать сообщение
	public function sitemapAction(): void
	{
		$base = config('general', 'url_html');
		$facets = FacetModel::getTree('category', 'all');
		$facets = array_filter($facets, fn($f) => (int)($f['facet_is_deleted'] ?? 0) !== 1);
		$items = ItemModel::getItemAll();

		$this->buildSitemap();

		Msg::redirect(__('msg.sitemap_built', ['total' => count($items) + count($facets) + 1]), 'success', url('tools'));
	}

	// Строим CSS для админки
	protected function buildCss($putch, $key)
	{
		$minifier = new Minify\CSS($putch);
		$minifier->minify(HLEB_PUBLIC_DIR . '/assets/css/' . $key . '.css');

		return true;
	}

	// Строим JS для админки. Для статики JS нет.
	protected function buildJs($putch, $key)
	{
		$minifier = new Minify\JS($putch . $key . '.js');
		$minifier->minify(HLEB_PUBLIC_DIR . '/assets/js/' . $key . '.js');

		return true;
	}

	public function path()
	{
		$facets = FacetModel::getFacetsAll();

		foreach ($facets as $value) {

			$tree =  FacetModel::breadcrumb($value['facet_id']);

			// Найдем последний элемент
			end($tree);
			$last_item_key   = key($tree);

			// Сделаем ссылку
			$show_last = true;

			$arr = [];
			foreach ($tree as $key => $item) {

				if ($key != $last_item_key) {
					// Покажем все элементы, кроме последнего 
					$arr[] =  $item['link'] . '/';
				} elseif ($show_last) {
					// Отобразим последний элемент 
				}
			}

			// Объединим пути и запишем значение в таблицу facets поле facet_path
			$path_arr = [implode('', $arr)];
			if (!empty($arr[0])) {

				// записываем
				FacetModel::rebuildPath($value['facet_id'], $path_arr[0] . $value['facet_slug']);
			} else {
				FacetModel::rebuildPath($value['facet_id'], $value['facet_slug']);
			}
		}


		Msg::redirect(__('msg.successfully'), 'success', url('tools'));
	}

	public static function searchIndex()
	{
		$storage = SearchModel::PdoStorage();

		$storage->erase();

		$stemmer = new \S2\Rose\Stemmer\PorterStemmerRussian(new \S2\Rose\Stemmer\PorterStemmerEnglish());

		$indexer = new Indexer($storage, $stemmer);

		$items = SearchModel::getIndexAll();

		foreach ($items as $item) {

			// Main parameters
			$indexable = new Indexable(
				$item['item_id'],
				$item['item_title'],
				markdown($item['item_content']),
				1 // 1 - факты
			);

			$indexable
				// ->setKeywords($item['item_keywords'] ?? '') 
				->setDescription(markdown($item['item_content']))
				//->setDate($item['item_modified'])
				->setDate(new \DateTime($item['item_modified']))
				//->setUrl($item['facet_list'])

				->setUrl(json_encode([
					// 'url' => $item['item_url'],
					'item_id' => $item['item_id'],
					'slug' => $item['item_slug'],
					'facets' => $item['facet_list'],
				], JSON_UNESCAPED_UNICODE))

				->setRelevanceRatio(3.14)
			;

			$indexer->index($indexable);
		}

		Msg::redirect(__('msg.successfully'), 'success', url('tools'));
	}

	public function transfer(): void
	{
		// Копируем только папку uploads (фото) в публичное пространство.
		// CSS/JS/ассеты копируются автоматически при пересборке (copyAssetsToStatic).
		$this->transferUploads();
	}

	public function copyDirectFile(): void
	{
		$source = HLEB_PUBLIC_DIR;
		$dest = $this->path;

		// Пример: copyDirect("dir1 - откуда", "dir2 - куда");
		$this->copyDirect($source, $dest, $over = false);

		// Убираем из статики админские JS, которые могли остаться от прошлых переносов
		$old = [
			$dest . '/assets/js/admin.js',
			$dest . '/assets/js/app.js',
			$dest . '/assets/js/common.js',
			$dest . '/assets/js/la.js',
			$dest . '/assets/js/tag',
			$dest . '/assets/js/cropper',
			$dest . '/assets/js/editor',
			$dest . '/assets/js/prism',
		];
		foreach ($old as $path) {
			if (is_file($path)) {
				unlink($path);
			} elseif (is_dir($path)) {
				$this->rmdirRecursive($path);
			}
		}
	}

	/**
	 * Перенос ТОЛЬКО картинок (uploads) на статику.
	 * Используется кнопкой «Перенести фото».
	 */
	public function transferUploads(): void
	{
		$source = HLEB_PUBLIC_DIR . '/uploads';
		$dest   = $this->path . '/uploads';

		// Перезаписываем — фото могли быть обновлены под тем же именем
		$this->copyDirect($source, $dest, $over = true);

		Msg::redirect(__('msg.change_saved'), 'success', url('tools'));
	}

	/**
	 * Автокопирование ассетов (assets/css, assets/js, assets/images, assets/svg)
	 * на статику. Вызывается после пересборки CSS/JS и генерации индекса/рандомных фактов,
	 * чтобы стили и скрипты сразу оказывались на статическом сайте без ручного переноса.
	 */
	public function copyAssetsToStatic(): void
	{
		$dest = $this->path . '/assets';

		// Перезаписываем: пересобранные CSS/JS обязаны попасть на статику.
		// copyDirect с $over=true не может удалять лишние КАТАЛОГИ внутри assets (editor/tag/...),
		// поэтому админские JS-подпапки чистим отдельно ниже.
		$over = true;

		foreach (['css', 'js', 'images', 'svg'] as $sub) {
			$source = HLEB_PUBLIC_DIR . '/assets/' . $sub;
			if (is_dir($source)) {
				$this->copyDirect($source, $dest . '/' . $sub, $over);
			}
		}

		// Убираем из статики админские JS, которые могли попасть при прошлых переносах
		$old = [
			$dest . '/js/admin.js',
			$dest . '/js/app.js',
			$dest . '/js/common.js',
			$dest . '/js/la.js',
			$dest . '/js/tag',
			$dest . '/js/cropper',
			$dest . '/js/editor',
			$dest . '/js/prism',
		];
		foreach ($old as $path) {
			if (is_file($path)) {
				unlink($path);
			} elseif (is_dir($path)) {
				$this->rmdirRecursive($path);
			}
		}
	}


	// Страница «Лучшие факты» (/best/index.html) с пагинацией
	public function buildBestPage(): void
	{
		$sideNav = CatalogService::sidebar();
		$limit   = 20;
		$total   = ItemModel::countBest();
		$pages   = max(1, (int)ceil($total / $limit));
		$dirPath = $this->path . 'best';

		if (!is_dir($dirPath)) {
			mkdir($dirPath, 0755, true);
		}

		for ($page = 1; $page <= $pages; $page++) {
			$items = ItemModel::getBestFacts($page, $limit);
			$file  = ($page === 1) ? $dirPath . '/index.html' : $dirPath . '/page-' . $page . '.html';

			file_put_contents($file, view('/site/pages/best.php', [
				'items'      => $items,
				'total'      => $total,
				'pagesCount' => $pages,
				'pNum'       => $page,
				'sideNav'    => $sideNav,
				'meta'       => Meta::get(__('app.best_facts_page')),
				'preview'    => false,
			]));
		}
	}

	// Строим центральную страницу и части страниц, например: footer
	public function buildHtmlHome()
	{
		$items = ItemModel::feedItem(false, false, 1, 5, 'main');

		Html::pageNumber();

		$temp_home =  view('/site/pages/home.php', [
			'meta' => Meta::home(),
			'items' => $items,
			'featuredBig' => ItemModel::getFeaturedBig(),
			'bestFact' => ItemModel::getBestFact(),
			'didYouKnow' => ItemModel::getFactsDidYouKnow(5),
			'sections' => CatalogService::sections(),
		]);
		file_put_contents($this->path . '/index.html', $temp_home);
		
		// Переносим общий подвал
		$temp_footer = view('/site/layouts/footer.php');
		file_put_contents($this->path . '/assets/footer.shtml',$temp_footer);

		// Случайные факты для кнопки «Случайный факт»
		$this->buildRandomFactsFile();
		
	}

	// Генерирует assets/js/random-facts.js со случайными URL фактов
	protected function buildRandomFactsFile(): void
	{
		$urls = [];

		// Случайные факты из живых категорий — без перебора всего каталога в PHP
		foreach (ItemModel::getRandomFacts(100) as $item) {
			$chunks = array_chunk(preg_split('/(@)/', (string)($item['facet_list'] ?? '')), 4);

			// Путь первой категории факта (все они уже живые — фильтр в SQL)
			$path = trim($chunks[0][2] ?? '', '/');
			if ($path === '') {
				continue;
			}
			$urls[] = '/' . $path . '/' . $item['item_slug'] . '.html';
		}

		$js = "window.__RANDOM_FACTS__ = " . json_encode($urls, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ";";
		file_put_contents($this->path . '/assets/js/random-facts.js', $js);

		// Также в public — чтобы админка/предпросмотр не ловили 404
		file_put_contents(HLEB_PUBLIC_DIR . '/assets/js/random-facts.js', $js);
	}

	// Генерирует assets/js/search-index.js — JSON-индекс всех публикуемых статей
	// для клиентского поиска на статике (MiniSearch).
	protected function buildSearchIndex(): void
	{
		$docs = [];
		foreach (SearchModel::getIndexAll() as $item) {
			$chunks = array_chunk(preg_split('/(@)/', (string)($item['facet_list'] ?? '')), 4);
			$path = trim($chunks[0][2] ?? '', '/');
			if ($path === '' || empty($item['item_slug'])) {
				continue;
			}

			$docs[] = [
				'id'    => (int)$item['item_id'],
				'title' => (string)$item['item_title'],
				'path'  => $path,
				'slug'  => (string)$item['item_slug'],
				'url'   => '/' . $path . '/' . $item['item_slug'] . '.html',
				'text'  => (string)$item['item_content'],
			];
		}

		$js = "window.__SEARCH_INDEX__ = " . json_encode($docs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ";";
		file_put_contents($this->path . '/assets/js/search-index.js', $js);
		file_put_contents(HLEB_PUBLIC_DIR . '/assets/js/search-index.js', $js);
	}

	// Кнопка в админке: пересобрать search-index.js
	public function buildSearchIndexAction(): void
	{
		$this->buildSearchIndex();
		Msg::redirect(__('msg.successfully'), 'success', url('tools'));
	}

	public function buildDir()
	{
		$facets = FacetModel::getTree('category', 'all');

		// Удалённые категории не строим (в базе остаются, но в статику не попадают)
		$facets = array_filter($facets, fn($f) => (int)($f['facet_is_deleted'] ?? 0) !== 1);

		foreach ($facets as $facet) {

			$directoryPath = $this->path . $facet['facet_path'];

			if (!file_exists($directoryPath)) {

				mkdir($directoryPath, 0755, true);
			}
		}

		Msg::redirect(__('msg.change_saved'), 'success', url('tools'));
	}

	public function buildHtmlDir(): void
	{
		// Создает домашнюю страницу и css
		$this->buildHtmlHome();

		// Клиентский поисковый индекс для статики
		$this->buildSearchIndex();

		$facets = FacetModel::getTree('category', 'all');

		// Удалённые категории не строим (в базе остаются, но в статику не попадают)
		$facets = array_filter($facets, fn($f) => (int)($f['facet_is_deleted'] ?? 0) !== 1);

		$sideNav = CatalogService::sidebar();

		foreach ($facets as $facet) {
			$this->rebuildCategory((int)$facet['facet_id'], $sideNav);
		}

		// Отдельные страницы-подборки
		$this->buildBestPage();

		Msg::redirect(__('msg.change_saved'), 'success', url('tools'));
	}

	// Пересборка одной категории: index.html + page-N.html (+ автосоздание папки)
	protected function rebuildCategory(int $facetId, array $sideNav): void
	{
		$facet = FacetModel::get($facetId, 'id');
		if (empty($facet) || (int)($facet['facet_is_deleted'] ?? 0) === 1) {
			return;
		}

		$tree = FacetModel::breadcrumb($facetId);
		$breadcrumb = Html::breadcrumbDir($tree, 'static');
		$meta = Meta::category($facet);
		$dirPath = $this->path . $facet['facet_path'];

		// Шаблон списка категории (статьи/сайты/факты) — единая логика
		$temp_dit = CatalogService::categoryTemplate((string)$facet['facet_path']);

		// Автосоздание папки категории
		if (!is_dir($dirPath)) {
			mkdir($dirPath, 0755, true);
		}

		// Страница 1 = index.html
		$catalog = CatalogService::data($facetId, 1, 'static');
		file_put_contents($dirPath . '/index.html', view($temp_dit, [
			'items' =>  $catalog['items'],
			'breadcrumb' => $breadcrumb,
			'childrens' => $catalog['childrens'],
			'facet' => $facet,
			'meta' => $meta,
			'pNum' => 1,
			'pagesCount' => $catalog['pagesCount'],
			'sideNav' => $sideNav,
		]));

		// Страницы 2..N = page-N.html
		for ($page = 2; $page <= $catalog['pagesCount']; $page++) {
			$catalog = CatalogService::data($facetId, $page, 'static');
			file_put_contents($dirPath . '/page-' . $page . '.html', view($temp_dit, [
				'items' =>  $catalog['items'],
				'breadcrumb' => $breadcrumb,
				'childrens' => $catalog['childrens'],
				'facet' => $facet,
				'meta' => $meta,
				'pNum' => $page,
				'pagesCount' => $catalog['pagesCount'],
				'sideNav' => $sideNav,
			]));
		}
	}

	public function buildHtmlView(): void
	{
		$items = ItemModel::getItemAll();
		$sideNav = CatalogService::sidebar();

		foreach ($items as $item) {
			$this->renderItemFile($item, $sideNav);
		}

		$this->scanBrokenLinks();

		Msg::redirect(__('msg.change_saved'), 'success', url('tools'));
	}

	// Инкрементальная сборка: перегенерирует изменённые факты и всё, что от них зависит
	// (категории, главная, sitemap, random-facts).
	public function buildHtmlIncremental(): void
	{
		$items = ItemModel::getItemAll();
		$sideNav = CatalogService::sidebar();
		$built = 0;
		$skipped = 0;
		$affectedFacets = [];

		foreach ($items as $item) {
			$dir = preg_split('/(@)/', (string)$item['facet_list'] ?? false);
			$path = $this->path . ($dir[2] ?? '') . '/' . $item['item_slug'] . '.html';

			$modified = strtotime((string)($item['item_modified'] ?? $item['item_date'] ?? 'now'));
			$fileTime = is_file($path) ? filemtime($path) : 0;

			// Файл есть и он новее записи — пропускаем
			if ($fileTime && $fileTime >= $modified) {
				$skipped++;
				continue;
			}

			$this->renderItemFile($item, $sideNav);
			$built++;

			// Категории, к которым привязан изменённый факт — их тоже пересобрать
			$chunks = array_chunk(preg_split('/(@)/', (string)($item['facet_list'] ?? '')), 4);
			foreach ($chunks as $chunk) {
				$fid = (int)($chunk[0] ?? 0);
				if ($fid > 0) {
					$affectedFacets[$fid] = $fid;
				}
			}
		}

		// Пересборка зависимых категорий
		if ($affectedFacets) {
			foreach ($affectedFacets as $fid) {
				$this->rebuildCategory($fid, $sideNav);
			}

			// Изменились факты → обновляем главную, sitemap и случайные факты
			$this->buildHtmlHome();
			$this->buildSitemap();
		}

		$this->scanBrokenLinks();

		Msg::redirect(
			__('msg.build_incremental', ['built' => $built, 'skipped' => $skipped]),
			'success',
			url('tools')
		);
	}

	// Рендер одного факта/страницы в статический HTML
	protected function renderItemFile(array $item, ?array $sideNav = null): void
	{
		$storage = SearchModel::PdoStorage();
		$similar = $storage->getSimilar(new ExternalId($item['item_id'], 1), false, 1, 3, 3);

		$dir = preg_split('/(@)/', (string)$item['facet_list'] ?? false);

		$tree = FacetModel::breadcrumb((int)$dir[0]);
		$breadcrumb = Html::breadcrumbDir($tree, 'static');

		// Обратные ссылки «Сюда ссылаются»: статьи, ссылающиеся на этот факт
		$backlinks = \App\Models\BacklinkModel::referring(
			(int)$item['item_id'],
			(string)($dir[2] ?? ''),
			(string)$item['item_slug']
		);

		// Выбор шаблона записи (факт/статья/сайт/страница) — единая логика
		$tmp = \App\Content\CatalogService::itemTemplate($item, (string)($dir[2] ?? ''));

		$img_url = Parser::miniature($item['item_content']);

		file_put_contents($this->path . $dir[2] . '/' . $item['item_slug'] . '.html', view($tmp, [
			'item' =>  $item,
			'similar' => $similar,
			'related' => ItemModel::getRelatedItems((int)$item['item_id'], 'article'),
			'dir' => $dir,
			'meta' => Meta::view($item, $dir[2], $img_url),
			'breadcrumb' => $breadcrumb,
			'faq' => \App\Models\FaqModel::forItem((int)$item['item_id']),
			'backlinks' => $backlinks,
			'sideNav' => $sideNav ?? CatalogService::sidebar(),
		]));
	}

	public function deletion(): void
	{
		$filesToKeep = ['uploads', 'assets', 'favicon.ico', '.osp']; // Список файлов, которые нужно оставить

		// Получить список всех файлов в директории
		$files = scandir($this->path);

		foreach ($files as $file) {
			// Пропускаем текущий каталог и родительский каталог
			if ($file == '.' || $file == '..') {
				continue;
			}

			$filePath = $this->path . $file;

			// Проверяем, есть ли файл в списке "оставить"
			if (in_array($file, $filesToKeep)) {
				continue; // Если есть, пропускаем его
			}

			$this->rmdirRecursive($filePath);
		}

		Msg::redirect(__('msg.change_saved'), 'success', url('tools'));
	}

	public function rmdirRecursive($dir)
	{
		if (!is_dir($dir)) {
			return false;
		}
		$items = scandir($dir);
		if ($items === false) {
			return false;
		}
		foreach ($items as $item) {
			if ($item == '.' || $item == '..') {
				continue;
			}
			$path = $dir . '/' . $item;
			if (is_dir($path)) {
				$this->rmdirRecursive($path);
			} else {
				unlink($path);
			}
		}
		rmdir($dir);
	}

	public function copyDirect($source, $dest, $over = false)
	{
		// Файлы, которые не должны попадать в статику (служебные и админские).
		$filesToProhibit = ['.htaccess', 'index.php'];

		// Подпапки public/assets/js, используемые ТОЛЬКО в админке (редактор, теги, кроппер, подсветка).
		// Статике (сайту посетителя) нужен только theme.js.
		$adminJsDirs = ['assets/js/tag', 'assets/js/cropper', 'assets/js/editor', 'assets/js/prism'];

		if (!is_dir($dest))
			mkdir($dest);
		if ($handle = opendir($source)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != '.' && $file != '..') {

					if (in_array($file, $filesToProhibit)) {
						continue;
					}

					$path = $source . '/' . $file;
					$rel  = trim(str_replace('\\', '/', str_replace(HLEB_PUBLIC_DIR, '', realpath($path) ?: $path)), '/');

					// Пропускаем админские JS-подпапки
					foreach ($adminJsDirs as $skip) {
						if ($rel === $skip || str_starts_with($rel, $skip . '/')) {
							continue 2;
						}
					}

					// Статике не нужны админские скрипты (только theme.js)
					if ($rel === 'assets/js/admin.js' || $rel === 'assets/js/app.js'
						|| $rel === 'assets/js/common.js' || $rel === 'assets/js/la.js') {
						continue;
					}

					if (is_file($path)) {
						if (!is_file($dest . '/' . $file || $over))
							if (!copy($path, $dest . '/' . $file)) {
								echo "('.$path.') Ошибка!!! ";
							}
					} elseif (is_dir($path)) {
						if (!is_dir($dest . '/' . $file))
							mkdir($dest . '/' . $file);
						$this->copyDirect($path, $dest . '/' . $file, $over);
					}
				}
			}
			closedir($handle);
		}
	}

	/**
	 * Проверка битых внутренних ссылок между фактами.
	 * Сканирует item_content всех статей, находит ссылки вида /path/slug.html
	 * и сверяет с реально существующими URL. Результат — в таблицу broken_links.
	 * Возвращает количество найденных битых ссылок.
	 */
	public function scanBrokenLinks(): int
	{
		// Существующие URL: /path/slug.html для всех опубликованных фактов
		$existing = [];
		foreach (ItemModel::getItemAll() as $item) {
			$dir = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
			$path = trim($dir[2] ?? '', '/');
			if ($path === '') {
				continue;
			}
			$existing['/' . $path . '/' . $item['item_slug'] . '.html'] = true;
		}

		$broken = [];

// Сканируем все статьи
		$items = ItemModel::getItemAll();
		foreach ($items as $item) {
			$haystack = (string)$item['item_content'];

			// Авто-ссылки [[Термин]]: применяем резолвер, чтобы получить реальные URL.
			// Оставшиеся [[...]] (не резолвленные) считаем потенциально битыми терминами.
			if (preg_match_all('/\[\[[^\[\]]+\]\]/u', $haystack, $wikiAll)) {
				[$resolved] = Parser::wikiLinks($haystack); // возвращает [контент, redLinks]
				// Найденные после резолва ссылки-кандидаты (битые или нет)
				if (preg_match_all('/\[([^\]]*)\]\((\/[^)\s]+\.html)\)/u', $resolved, $wr, PREG_SET_ORDER)) {
					foreach ($wr as $w) {
						$url = $w[2] ?? '';
						$key = (int)$item['item_id'] . '|' . $url;
						if (!str_starts_with($url, '/') || isset($broken[$key])) {
							continue;
						}
						$broken[$key] = [
							'item_id'       => (int)$item['item_id'],
							'article_title' => $item['item_title'],
							'link'          => $url,
							'word'          => trim((string)($w[1] ?? '')),
						];
					}
				}
				// Оставшиеся в $resolved непереведённые [[Термин]] — красные ссылки.
				// Используем ЦЕЛЬ (правую часть при pipe, иначе сам термин),
				// т.к. резолвер пытался найти именно её.
				if (preg_match_all('/\[\[([^\[\]|]+?)(?:\|([^\[\]]+?))?\]\]/u', $resolved, $uw, PREG_SET_ORDER)) {
					foreach ($uw as $u) {
						$word = trim((string)($u[2] ?? $u[1] ?? ''));
						$key  = (int)$item['item_id'] . '|[[word]]' . $word;
						if ($word === '' || isset($broken[$key])) {
							continue;
						}
						$broken[$key] = [
							'item_id'       => (int)$item['item_id'],
							'article_title' => $item['item_title'],
							'link'          => '',
							'word'          => $word,
						];
					}
				}
			}

			// Обычные ссылки [text](/path/slug.html)
			if (preg_match_all('/\[([^\]]*)\]\((\/[^)\s]+\.html)\)/u', $haystack, $m, PREG_SET_ORDER)) {
				foreach ($m as $match) {
					$url = $match[2] ?? '';
					// Только внутренние (не http/https)
					if (!str_starts_with($url, '/')) {
						continue;
					}
					// Уже есть в списке битых — не дублируем
					$key = (int)$item['item_id'] . '|' . $url;
					if (isset($broken[$key])) {
						continue;
					}
					$broken[$key] = [
						'item_id'       => (int)$item['item_id'],
						'article_title' => $item['item_title'],
						'link'          => $url,
						'word'          => trim((string)($match[1] ?? '')),
					];
				}
			}
		}

		// Оставляем только битые (нет в existing)
		$broken = array_filter($broken, fn($b) => !isset($existing[$b['link']]));

		BrokenLinkModel::replace(array_values($broken));

		return count($broken);
	}

	// Отдельная проверка по кнопке на странице битых ссылок (с редиректом)
	public function checkBrokenLinks(): void
	{
		$count = $this->scanBrokenLinks();

		Msg::redirect(
			__('msg.broken_links_checked', ['count' => $count]),
			'success',
			url('broken.links')
		);
	}
}
