<?php

namespace App\Controllers;

use Hleb\Base\Controller;
use MatthiasMullie\Minify;
use App\Models\{FacetModel, SearchModel};
use App\Models\ItemModel;
use Msg, Html, Meta;

use Loupe\Loupe\Config\TypoTolerance;
use Loupe\Loupe\Configuration;
use Loupe\Loupe\LoupeFactory;

use S2\Rose\Stemmer\PorterStemmerRussian;
use S2\Rose\Stemmer\PorterStemmerEnglish;
use S2\Rose\Entity\Indexable;
use S2\Rose\Indexer;

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

		//$this->buildCss_html(config('general', 'path_css_build'));

		Msg::redirect(__('msg.change_saved'), 'success', url('tools'));
	}

	protected function buildCss($putch, $key)
	{
		$minifier = new Minify\CSS($putch);
		$minifier->minify(HLEB_PUBLIC_DIR . '/assets/css/' . $key . '.css');

		return true;
	}

	protected function buildCss_html($putch)
	{
		$minifier = new Minify\CSS($putch);
		$minifier->minify($this->path . 'style.css');

		return true;
	}

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
						'url' => $item['item_url'],
						'item_id' => $item['item_id'],
						'facets' => $item['facet_list'], 
					], JSON_UNESCAPED_UNICODE))
				
				->setRelevanceRatio(3.14)
			;

			$indexer->index($indexable);
		}

		Msg::redirect(__('msg.successfully'), 'success', url('tools'));
	}

	public function all(): void
	{
		// Создает домашнюю страницу и css
		$this->buildHtmlHome();

		// Строем категории
		$this->buildDir();

		// Копируем папку uploads в публичное пространство
		$this->copyDirectFile();

		// Строем страницы в категориях
		$this->buildHtml();

		Msg::redirect(__('msg.change_saved'), 'success', url('tools'));
	}

	public function copyDirectFile(): void
	{
		$source = HLEB_PUBLIC_DIR;
		$dest = $this->path;

		// Пример: copyDirect("dir1 - откуда", "dir2 - куда");
		$this->copyDirect($source, $dest, $over = false);
	}

	public function buildHtmlHome()
	{
		$items = ItemModel::feedItem(false, false, 1, 5);

		$temp =   '/templates/home.php';

		$nodes = Html::builder(0, 0, FacetModel::getTree());

		file_put_contents($this->path . '/index.html', view($temp, ['meta' => Meta::home(), 'items' => $items, 'nodes' => $nodes]));

		$minifier = new Minify\CSS(HLEB_GLOBAL_DIR . '/resources/views/assets/css/build.css');

		$minifier->minify($this->path . 'style.css');
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

	public function buildHtml(): void
	{
		// Создает домашнюю страницу и css
		$this->buildHtmlHome();

		$temp_dit =   '/templates/index.php';

		$facets = FacetModel::getTree('category', 'all');

		foreach ($facets as $facet) {

			$childrenForFeed =  FacetModel::childrenForFeed($facet['facet_id']);
			$items = ItemModel::feedItem($childrenForFeed, $facet['facet_id'], Html::pageNumber(), 1, 'all');


			$tree = FacetModel::breadcrumb($facet['facet_id']);
			$breadcrumb = self::breadcrumbDir($tree);

			$childrens = FacetModel::getChildrens($facet['facet_id']);

			$meta = Meta::category($facet);

			file_put_contents($this->path . $facet['facet_path'] . '/index.html', view($temp_dit, [
				'items' =>  $items,
				'breadcrumb' => $breadcrumb,
				'childrens' => $childrens,
				'meta' => $meta
			]));
		}

		Msg::redirect(__('msg.change_saved'), 'success', url('tools'));
	}


	public function deletion(): void
	{
		$filesToKeep = ['uploads', 'favicon.ico', '.osp', 'style.css']; // Список файлов, которые нужно оставить

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

	public static function breadcrumbDir($arr)
	{
		$home = [
			'name' => __('app.home'),
			'path' => config('general', 'url'),
			'home' => true,
		];

		array_unshift($arr, $home);

		$result = [];
		foreach ($arr as $row) {

			if (!empty($row['home']) === true) {
				$result[] = ["name" => $row['name'],  "path" => $row['path'], "link" => '/'];
			} else {
				$result[] = ["name" => $row['name'],  "path" => $row['path'], "link" => urlDir($row['path'])];
			}
		}

		return $result;
	}

	public function copyDirect($source, $dest, $over = false)
	{
		if (!is_dir($dest))
			mkdir($dest);
		if ($handle = opendir($source)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != '.' && $file != '..') {
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
