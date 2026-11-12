<?php

use App\Middlewares\DefaultMiddleware;
use App\Bootstrap\Services\Auth\RegType;

use App\Controllers\{
	Auth\LoginController,
	Auth\RegisterController,
	Auth\RecoverController,
	
	HomeController,
	BuildController,
	PreviewController,
	FormController,
	SettingController,
	SearchController,
	SourceController,
	TemplateAdminController,
	BrokenLinkController,
	
	Facet\FacetController,
	Facet\EditFacetController,
	Facet\AddFacetController,
	Facet\RedirectController,
	
	Item\ItemController,
	Item\AddItemController,
	Item\EditItemController
};

 
/**
 * Access without authorization (TL0)
 * Доступ без авторизации (TL0)
 */
Route::toGroup()
	->prefix('/mod/admin/')
	->middleware(DefaultMiddleware::class, data: [RegType::USER_ZERO_LEVEL, '=']);
		Route::toGroup()->protect();
			Route::post('/login')->controller(LoginController::class)->name('authorization');
			Route::post('/recover/send')->controller(RecoverController::class)->name('recover.send'); 
			Route::post('/recover/send/pass')->controller(RecoverController::class, 'remindNew')->name('new.pass'); 
		Route::endGroup();
	 
		Route::get('/register')->controller(RegisterController::class, 'showRegisterForm')->name('register');
		Route::get('/recover')->controller(RecoverController::class, 'showPasswordForm')->name('recover');  
Route::endGroup();

/**
 * Access after authorization (TL1)
 * Доступ после авторизации (TL1)
 * ->middleware(Registrar::class, data: [RegType::UNDEFINED_USER, '>=']);
 */
Route::toGroup()
	->prefix('/mod/admin/')
	->middleware(DefaultMiddleware::class, data: [RegType::USER_FIRST_LEVEL, '>=']);
	
	Route::get('/facts/{type}')->controller(HomeController::class, 'facts')->where(['type' => '[a-z]+'])->name('facts');

	Route::get('/add/item')->controller(AddItemController::class)->name('item.form.add');
	Route::get('/edit/item/{id}')->controller(EditItemController::class)->where(['id' => '[0-9]+'])->name('item.form.edit');
	Route::get('/item/history/{id}')->controller(EditItemController::class, 'history')->where(['id' => '[0-9]+'])->name('item.history');
	Route::get('/item/history/{id}/show/{version}')->controller(EditItemController::class, 'historyShow')->where(['id' => '[0-9]+', 'version' => '[0-9]+'])->name('item.history.show');
	Route::get('/item/history/{id}/diff/{from}/{to}')->controller(EditItemController::class, 'historyDiff')->where(['id' => '[0-9]+', 'from' => '[0-9]+', 'to' => '[0-9]+'])->name('item.history.diff');

	Route::get('/setting')->controller(SettingController::class)->name('setting');
	Route::get('/setting/security')->controller(SettingController::class, 'securityForm')->name('setting.security');

	Route::get('/logout')->controller(LoginController::class, 'logout')->name('logout');
	Route::get('/search/go')->controller(SearchController::class, 'go')->name('search.go');
	Route::post('/search/select/{type}')->controller(FormController::class)->where(['type' => '[a-z]+']);
	Route::post('/editor/check-wikilinks')->controller(FormController::class, 'checkWikilinks')->name('editor.check-wikilinks');
	

		// Отправка на изменение
	Route::toGroup()->protect();
		Route::post('/add/item')->controller(AddItemController::class, 'add')->name('add.item');
		Route::post('/edit/item')->controller(EditItemController::class, 'edit')->name('edit.item');
		Route::post('/item/rollback/{id}/{version}')->controller(EditItemController::class, 'rollback')->where(['id' => '[0-9]+', 'version' => '[0-9]+'])->name('item.rollback');
		
		Route::post('/setting/edit')->controller(SettingController::class, 'edit')->name('setting.edit.profile');
		Route::post('/user/edit/security')->controller(SettingController::class, 'securityEdit')->where(['type' => '[a-z]+'])->name('setting.edit.security');
	Route::endGroup();


Route::endGroup();

 // Администрирование
Route::toGroup()
	->prefix('/mod/admin/')
	->middleware(DefaultMiddleware::class, data: [RegType::REGISTERED_ADMIN, '=']);
	
	Route::post('/backend/upload/{type}/{id}')->controller(EditItemController::class, 'uploadContentImage')->where(['type' => '[a-z-]+', 'id' => '[0-9]+']);

	Route::get('/manual/update/css')->controller(BuildController::class, 'index')->name('update.css'); 
	Route::get('/manual/update/path')->controller(BuildController::class, 'path')->name('update.path');
	Route::get('/manual/update/transfer')->controller(BuildController::class, 'transfer')->name('update.transfer');
	Route::get('/manual/update/dir')->controller(BuildController::class, 'buildDir')->name('update.dir');
	Route::get('/manual/update/html-dir')->controller(BuildController::class, 'buildHtmlDir')->name('update.html.dir');
	Route::get('/manual/update/html-view')->controller(BuildController::class, 'buildHtmlView')->name('update.html.view');
	Route::get('/manual/update/html-incremental')->controller(BuildController::class, 'buildHtmlIncremental')->name('update.html.incremental');
	Route::get('/manual/update/indexing')->controller(BuildController::class, 'searchIndex')->name('update.indexing');
	Route::get('/manual/update/search-index')->controller(BuildController::class, 'buildSearchIndexAction')->name('update.search-index');
	Route::get('/manual/sources/check')->controller(BuildController::class, 'sourceCheck')->name('update.sources');
	Route::get('/manual/update/sitemap')->controller(BuildController::class, 'sitemapAction')->name('update.sitemap');
	Route::get('/manual/sites/favicons')->controller(BuildController::class, 'faviconsAction')->name('update.favicons');
	Route::get('/manual/sites/tcy')->controller(BuildController::class, 'tcyAction')->name('update.tcy');
	Route::get('/manual/sites/favicons-new')->controller(BuildController::class, 'faviconsNewAction')->name('update.favicons.new');
	Route::get('/manual/sites/tcy-new')->controller(BuildController::class, 'tcyNewAction')->name('update.tcy.new');
	Route::get('/manual/sites/screens')->controller(BuildController::class, 'screensAction')->name('update.screens');
	Route::get('/manual/sites/screens-new')->controller(BuildController::class, 'screensNewAction')->name('update.screens.new');
	
	Route::get('/manual/deletion/dir')->controller(BuildController::class, 'deletion')->name('deletion.dir');
	
 
	Route::get('/item/img/{id}/remove')->controller(EditItemController::class, 'thumbItemRemove')->where(['id' => '[0-9]+'])->name('delete.item.thumb');
	
	Route::get('/category')->controller(FacetController::class, 'structure')->name('structure');
	Route::get('/structure')->controller(FacetController::class, 'structure')->name('structure.alias');
	Route::get('/sources')->controller(SourceController::class)->name('sources');

	// Шаблоны оформления
	Route::get('/templates')->controller(TemplateAdminController::class)->name('templates');
	Route::get('/templates/add')->controller(TemplateAdminController::class, 'form')->name('template.add');
	Route::get('/templates/edit/{id}')->controller(TemplateAdminController::class, 'form')->where(['id' => '[0-9]+'])->name('template.edit');

		Route::toGroup()->protect();
			Route::post('/templates/save')->controller(TemplateAdminController::class, 'save')->name('template.save');
			Route::post('/templates/delete')->controller(TemplateAdminController::class, 'delete')->name('template.delete');
			Route::post('/templates/field/save')->controller(TemplateAdminController::class, 'fieldSave')->name('template.field.save');
			Route::post('/templates/field/delete')->controller(TemplateAdminController::class, 'fieldDelete')->name('template.field.delete');
			Route::post('/templates/field/restore')->controller(TemplateAdminController::class, 'fieldRestore')->name('template.field.restore');
			Route::post('/templates/fields/order')->controller(TemplateAdminController::class, 'fieldsOrder')->name('template.fields.order');
		Route::endGroup();

	Route::get('/inline/delete/{id}')->controller(EditItemController::class, 'inlineDelete')->where(['id' => '[0-9]+'])->name('inline.delete');
	Route::get('/inline/list/{id}')->controller(EditItemController::class, 'inlineList')->where(['id' => '[0-9]+'])->name('inline.list');

	Route::get('/media/list')->controller(TemplateAdminController::class, 'mediaList')->name('media.list');
	Route::get('/media')->controller(TemplateAdminController::class, 'media')->name('media');

		Route::toGroup()->protect();
			Route::post('/media/save')->controller(TemplateAdminController::class, 'mediaSave')->name('media.save');
		Route::endGroup();
	Route::get('/broken-links')->controller(BrokenLinkController::class)->name('broken.links');
	Route::get('/broken-links/check')->controller(BuildController::class, 'checkBrokenLinks')->name('broken.links.check');
	Route::get('/terms/rebuild')->controller(BuildController::class, 'rebuildTermsAction')->name('terms.rebuild');
	Route::get('/terms/wanted')->controller(BrokenLinkController::class)->name('terms.wanted');
	Route::get('/tools')->controller(BuildController::class, 'tools')->name('tools');
	
	Route::get('/add/facet/{type}')->controller(AddFacetController::class)->where(['type' => '[a-z]+'])->name('facet.form.add');
	Route::get('/edit/facet/{type}/{id}')->controller(EditFacetController::class)->where(['type' => '[a-z]+', 'id' => '[0-9]+'])->name('facet.form.edit'); 
	Route::get('/redirect/facet/{id}')->controller(RedirectController::class)->where(['id' => '[0-9]+'])->name('redirect.facet');
	
	Route::get('/item/view/{id}')->controller(ItemController::class)->where(['id' => '[0-9]+'])->name('view');

	Route::get('/dir/...0-6')->controller(HomeController::class, 'dir')->name('dir');
	Route::get('/preview/site')->controller(PreviewController::class, 'site')->name('preview.site');
	Route::get('/preview/...0-6')->controller(PreviewController::class)->name('preview');

		// Отправка на изменение
	Route::toGroup()->protect();
		Route::post('/edit/facet/{type}')->controller(EditFacetController::class, 'edit')->where(['type' => '[a-z]+'])->name('edit.facet');
		Route::post('/edit/facet/logo/{type}/{facet_id}')->controller(EditFacetController::class, 'logoEdit')->where(['type' => '[a-z]+', 'facet_id' => '[0-9]+'])->name('edit.logo.facet');
		Route::post('/add/facet/{type}')->controller(AddFacetController::class, 'add')->where(['type' => '[a-z]+'])->name('add.facet');
		Route::post('/delete/facet')->controller(EditFacetController::class, 'delete')->name('delete.facet');
	Route::endGroup();
	
Route::endGroup();	

Route::get('/')->controller(HomeController::class)->name('homepage');
Route::get('/random')->controller(HomeController::class, 'random')->name('random');
Route::get('/best')->controller(HomeController::class, 'best')->name('best');