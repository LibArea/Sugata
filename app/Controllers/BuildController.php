<?php

namespace App\Controllers;

use Hleb\Base\Controller;
use MatthiasMullie\Minify;
use App\Models\{FacetModel, SearchModel};
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

		foreach (config('general', 'path_css_build') as $key => $putch) {
			$this->buildCss_html(HLEB_GLOBAL_DIR . $putch, $key);
		}

		Msg::redirect(__('msg.change_saved'), 'success', url('tools'));
	}

	public function tools()
	{
		render(
			'/content/tools',
			[
				'meta'  => Meta::get(__('app.tools')),
				'data'  => [
					'sheet'         => 'tools',
				]
			]
		);
	}

	// Строим CSS для админки
	protected function buildCss($putch, $key)
	{
		$minifier = new Minify\CSS($putch);
		$minifier->minify(HLEB_PUBLIC_DIR . '/assets/css/' . $key . '.css');

		return true;
	}

	// Строим CSS для статики
	protected function buildCss_html($putch)
	{
		$minifier = new Minify\CSS($putch);
		$minifier->minify($this->path . '/assets/css/style.css');

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
		// Копируем папку uploads в публичное пространство
		$this->copyDirectFile();

		Msg::redirect(__('msg.change_saved'), 'success', url('tools'));
	}

	public function copyDirectFile(): void
	{
		$source = HLEB_PUBLIC_DIR;
		$dest = $this->path;

		// Пример: copyDirect("dir1 - откуда", "dir2 - куда");
		$this->copyDirect($source, $dest, $over = false);
	}


	// Строим центральную страницу и части страниц, например: footer
	public function buildHtmlHome()
	{
		$items = ItemModel::feedItem(false, false, 1, 5, 'main');

		Html::pageNumber();

		$temp_home =  view('/templates/home.php', ['meta' => Meta::home(), 'items' => $items]);
		file_put_contents($this->path . '/index.html', $temp_home);
		
		// Переносим общий подвал
		$temp_footer = view('/templates/footer.php');
		file_put_contents($this->path . '/assets/footer.shtml',$temp_footer);
		
	}

	public function buildDir()
	{
		$facets = FacetModel::getTree('category', 'all');

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

		$temp_dit =   '/templates/index.php';

		$facets = FacetModel::getTree('category', 'all');

		foreach ($facets as $facet) {

			$tree = FacetModel::breadcrumb($facet['facet_id']);
			$breadcrumb = Html::breadcrumbDir($tree, 'static');
			$meta = Meta::category($facet);
			$dirPath = $this->path . $facet['facet_path'];

			// Страница 1 = index.html
			$catalog = CatalogService::data((int)$facet['facet_id'], 1, 'static');
			file_put_contents($dirPath . '/index.html', view($temp_dit, [
				'items' =>  $catalog['items'],
				'breadcrumb' => $breadcrumb,
				'childrens' => $catalog['childrens'],
				'facet' => $facet,
				'meta' => $meta,
				'pNum' => 1,
				'pagesCount' => $catalog['pagesCount'],
			]));

			// Страницы 2..N = page-N.html
			for ($page = 2; $page <= $catalog['pagesCount']; $page++) {
				$catalog = CatalogService::data((int)$facet['facet_id'], $page, 'static');
				file_put_contents($dirPath . '/page-' . $page . '.html', view($temp_dit, [
					'items' =>  $catalog['items'],
					'breadcrumb' => $breadcrumb,
					'childrens' => $catalog['childrens'],
					'facet' => $facet,
					'meta' => $meta,
					'pNum' => $page,
					'pagesCount' => $catalog['pagesCount'],
				]));
			}
		}

		Msg::redirect(__('msg.change_saved'), 'success', url('tools'));
	}

	public function buildHtmlView(): void
	{
		$items = ItemModel::getItemAll();

		foreach ($items as $item) {
			$this->renderItemFile($item);
		}

		Msg::redirect(__('msg.change_saved'), 'success', url('tools'));
	}

	// Инкрементальная сборка: перегенерирует только те факты, чей HTML
	// отсутствует или старше, чем дата изменения записи (item_modified).
	public function buildHtmlIncremental(): void
	{
		$items = ItemModel::getItemAll();
		$built = 0;
		$skipped = 0;

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

			$this->renderItemFile($item);
			$built++;
		}

		Msg::redirect(
			__('msg.build_incremental', ['built' => $built, 'skipped' => $skipped]),
			'success',
			url('tools')
		);
	}

	// Рендер одного факта/страницы в статический HTML
	protected function renderItemFile(array $item): void
	{
		$temp_view = '/templates/view.php';
		$temp_page = '/templates/page.php';

		$storage = SearchModel::PdoStorage();
		$similar = $storage->getSimilar(new ExternalId($item['item_id'], 1), false, 1, 3, 3);

		$dir = preg_split('/(@)/', (string)$item['facet_list'] ?? false);

		$tree = FacetModel::breadcrumb((int)$dir[0]);
		$breadcrumb = Html::breadcrumbDir($tree, 'static');

		// Если slug = info (служебная категория) то другой шаблон
		$tmp = ($dir[2] == 'info') ? $temp_page : $temp_view;

		$img_url = Parser::miniature($item['item_content']);

		file_put_contents($this->path . $dir[2] . '/' . $item['item_slug'] . '.html', view($tmp, [
			'item' =>  $item,
			'similar' => $similar,
			'dir' => $dir,
			'meta' => Meta::view($item, $dir[2], $img_url),
			'breadcrumb' => $breadcrumb
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
		$filesToProhibit = ['.htaccess', 'index.php']; // Список файлов, которые нужно запретить переносить

		if (!is_dir($dest))
			mkdir($dest);
		if ($handle = opendir($source)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != '.' && $file != '..') {

					// Проверяем, есть ли файл в списке "Запретить"
					if (in_array($file, $filesToProhibit)) {
						continue; // Если есть, пропускаем его
					}

					$path = $source . '/' . $file;
					if (is_file($path)) {
						if (!is_file($dest . '/' . $file || $over))
							if (!@copy($path, $dest . '/' . $file)) {
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
}
