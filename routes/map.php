<?php

use App\Middlewares\DefaultMiddleware;
use App\Bootstrap\Services\Auth\RegType;

use App\Controllers\{
	Auth\LoginController,
	Auth\RegisterController,
	Auth\RecoverController,
	
	HomeController,
	ViewingController,
	BuildController,
	FormController,
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
	Route::get('/manual/update/indexing')->controller(BuildController::class, 'searchIndex')->name('update.indexing');
	
	Route::get('/manual/deletion/dir')->controller(BuildController::class, 'deletion')->name('deletion.dir');
	
	Route::get('/add/item')->controller(AddItemController::class)->name('item.form.add');
	Route::get('/edit/item/{id}')->controller(EditItemController::class)->where(['id' => '[0-9]+'])->name('item.form.edit');
	 
	Route::get('/item/img/{id}/remove')->controller(EditItemController::class, 'thumbItemRemove')->where(['id' => '[0-9]+'])->name('delete.item.thumb');
	
	// возможно надо удалить
	Route::get('/viewing')->controller(ViewingController::class)->name('viewing');
	Route::get('/viewing-v')->controller(ViewingController::class, 'viewingHome')->name('viewing.home');
	 
	Route::get('/category')->controller(FacetController::class, 'structure')->name('structure');
	Route::get('/tools')->controller(BuildController::class, 'tools')->name('tools');
	
	Route::get('/add/facet/{type}')->controller(AddFacetController::class)->where(['type' => '[a-z]+'])->name('facet.form.add');
	Route::get('/edit/facet/{type}/{id}')->controller(EditFacetController::class)->where(['type' => '[a-z]+', 'id' => '[0-9]+'])->name('facet.form.edit'); 
	Route::get('/redirect/facet/{id}')->controller(RedirectController::class)->where(['id' => '[0-9]+'])->name('redirect.facet');
	
	Route::get('/items')->controller(ItemController::class)->name('items'); 
	Route::get('/item/view/{id}')->controller(ItemController::class, 'view')->where(['id' => '[0-9]+'])->name('view'); 
	
	
	Route::post('/search/select/{type}')->controller(FormController::class)->where(['type' => '[a-z]+']);
	Route::get('/logout')->controller(LoginController::class, 'logout')->name('logout');
	
		// Отправка на изменение
	Route::toGroup()->protect();
		Route::post('/edit/facet/{type}')->controller(EditFacetController::class, 'edit')->where(['type' => '[a-z]+'])->name('edit.facet');
		Route::post('/edit/facet/logo/{type}/{facet_id}')->controller(EditFacetController::class, 'logoEdit')->where(['type' => '[a-z]+', 'facet_id' => '[0-9]+'])->name('edit.logo.facet');
		Route::post('/add/facet/{type}')->controller(AddFacetController::class, 'add')->where(['type' => '[a-z]+'])->name('add.facet');
		
		Route::post('/add/item')->controller(AddItemController::class, 'add')->name('add.item');
		Route::post('/edit/item')->controller(EditItemController::class, 'edit')->name('edit.item');
	Route::endGroup();
	
Route::endGroup();	

Route::get('/')->controller(HomeController::class)->name('homepage');