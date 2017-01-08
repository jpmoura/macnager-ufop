<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

// Rotas somente para usuários autenticados
Route::group(['middleware' => 'auth'], function() {

    // Rotas disponíveis somente para administradores
    Route::group(['middelaware' => 'can:administrate'], function() {
        Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

        Route::get('/force', ['as' => 'forceReload', 'uses' => 'RequisicaoController@forceReload']);
        Route::get('/listUsers/{id}', ['as' => 'showUsers', 'uses' => 'RequisicaoController@getUsersList']);

        Route::get('/requests/{type}', ['as' => 'showRequest', 'uses' => 'RequisicaoController@show']);
        Route::group(['prefix' => 'request'], function() {
            Route::get('/request/details/{id}', ['as' => 'detailsRequest', 'uses' => 'RequisicaoController@getRequestDetails']);
            Route::post('/request/approve', ['as' => 'doApproveRequest', 'uses' => 'RequisicaoController@doApproveRequest']);
            Route::post('/request/suspend', ['as' => 'doSuspendRequest', 'uses' => 'RequisicaoController@doSuspendRequest']);
            Route::post('/request/disable', ['as' => 'doDisableRequest', 'uses' => 'RequisicaoController@doDisableRequest']);
            Route::post('/request/deny', ['as' => 'doSuspendRequest', 'uses' => 'RequisicaoController@doDenyRequest']);
            Route::get('/request/reactive/{id}', ['as' => 'doReactiveRequest', 'uses' => 'RequisicaoController@doReactiveRequest']);
            Route::post('/request/delete', ['as' => 'doDeleteRequest', 'uses' => 'RequisicaoController@doDeleteRequest']);
            Route::get('/request/edit/{id}', ['as' => 'getEditRequest', 'uses' => 'RequisicaoController@getEditRequest']);
            Route::post('/request/edit', ['as' => 'doEditRequest', 'uses' => 'RequisicaoController@doEditRequest']);
        });
    });



    Route::get('/', ['as' => 'home', 'uses' => 'PagesController@home']);
    Route::get('/home', 'PagesController@home');


    Route::get('/addMac', ['as' => 'getAddMac', 'uses' => 'RequisicaoController@getAddMac']);
    Route::post('/addMac', ['as' => 'postAddMac', 'uses' => 'RequisicaoController@doAddMac']);

    Route::get('/editMac/{id}', ['as' => 'getEditMac', 'uses' => 'RequisicaoController@getEditMac']);
    Route::post('/editMac', ['as' => 'doEditMac', 'uses' => 'RequisicaoController@doEditMac']);

    Route::get('deleteMac/{id}', ['as' => 'deleteMac', 'uses' => 'RequisicaoController@deleteMac']);

    Route::get('/exportArp', ['as' => 'exportArp', 'uses' => 'PagesController@exportArp']);
    Route::get('/exportDhcpd', ['as' => 'exportDhcpd', 'uses' => 'PagesController@exportDhcpd']);

    Route::get('/listMac/{type}', ['as' => 'listMac', 'uses' => 'RequisicaoController@getListMac']);

    Route::get('/addRequest', ['as' => 'addRequest', 'uses' => 'RequisicaoController@getAddRequest']);
    Route::post('/addRequest', ['as' => 'addRequest', 'uses' => 'RequisicaoController@doAddRequest']);
    Route::get('/listUserRequests', ['as' => 'listUserRequests', 'uses' => 'RequisicaoController@getListUserRequests']);

    Route::post('/searchperson', ['as' => 'doSearch', 'uses' => 'UserController@searchPerson']);
    Route::get('/request/{filepath}', ['as' => 'showFile', 'uses' => 'RequisicaoController@showFile']);

    Route::get('/addUserType', ['as' => 'getAddUserType', 'uses' => 'TipoUsuarioController@getAddUserType']);
    Route::post('/addUserType', ['as' => 'doAddUserType', 'uses' => 'TipoUsuarioController@doAddUserType']);
    Route::get('/listUserType', ['as' => 'listUserType', 'uses' => 'TipoUsuarioController@listUserType']);
    Route::get('/editUserType/{id}', ['as' => 'getEditUserType', 'uses' => 'TipoUsuarioController@getEditUserType']);
    Route::post('/editUserType', ['as' => 'doEditUserType', 'uses' => 'TipoUsuarioController@doEditUserType']);
    Route::get('/deleteUserType/{id}', ['as' => 'deleteUserType', 'uses' => 'TipoUsuarioController@deleteUserType']);

    Route::get('/addDeviceType', ['as' => 'getAddDeviceType', 'uses' => 'TipoDispositivoController@getAddDeviceType']);
    Route::post('/addDeviceType', ['as' => 'doAddDeviceType', 'uses' => 'TipoDispositivoController@doAddDeviceType']);
    Route::get('/listDeviceType', ['as' => 'listDeviceType', 'uses' => 'TipoDispositivoController@listDeviceType']);
    Route::get('/editDeviceType/{id}', ['as' => 'getEditDeviceType', 'uses' => 'TipoDispositivoController@getEditDeviceType']);
    Route::post('/editDeviceType', ['as' => 'doEditDeviceType', 'uses' => 'TipoDispositivoController@doEditDeviceType']);
    Route::get('/deleteDeviceType/{id}', ['as' => 'deleteDeviceType', 'uses' => 'TipoDispositivoController@deleteDeviceType']);




});

Route::get('/login', ['as' => 'showLogin', 'uses' => 'Auth\LoginController@showLogin']);
Route::post('/login', ['as' => 'login', 'uses' => 'Auth\LoginController@postLogin']);
Route::get('/sair', ['as' => 'logout', 'uses' => 'Auth\LoginController@logout']);

Route::get('/teste', ['as' => 'teste', 'uses' => 'RequisicaoController@test']);
Route::get('/sobre', ['as' => 'about', 'uses' => 'PagesController@about']);