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

        Route::group(['prefix' => 'request'], function() {
            Route::get('list/all/{type}', ['as' => 'showRequest', 'uses' => 'RequisicaoController@show']);
            Route::post('approve', ['as' => 'approveRequest', 'uses' => 'RequisicaoController@approve']);
            Route::post('suspend', ['as' => 'suspendRequest', 'uses' => 'RequisicaoController@suspend']);
            Route::post('disable', ['as' => 'disableRequest', 'uses' => 'RequisicaoController@disable']);
            Route::post('deny', ['as' => 'denyRequest', 'uses' => 'RequisicaoController@deny']);
            Route::get('reactive/{id}', ['as' => 'reactiveRequest', 'uses' => 'RequisicaoController@reactive']);
            Route::get('list/usage/{id}', ['as' => 'showUsageRequest', 'uses' => 'RequisicaoController@showUsage']);
        });

        Route::group(['prefix' => 'device'], function(){
            Route::get('add', ['as' => 'showAddDevice', 'uses' => 'RequisicaoController@showAddDevice']);
            Route::post('add', ['as' => 'addDevice', 'uses' => 'RequisicaoController@addDevice']);
            Route::get('edit/{id}', ['as' => 'showEditDevice', 'uses' => 'RequisicaoController@showEditDevice']);
            Route::post('edit', ['as' => 'editDevice', 'uses' => 'RequisicaoController@editDevice']);
            Route::get('list/{status}', ['as' => 'listDevice', 'uses' => 'RequisicaoController@listDevices']);

            Route::group(['prefix' => 'type'], function(){
                Route::get('add', ['as' => 'showAddDeviceType', 'uses' => 'TipoDispositivoController@showAdd']);
                Route::post('add', ['as' => 'addDeviceType', 'uses' => 'TipoDispositivoController@add']);
                Route::get('list', ['as' => 'listDeviceType', 'uses' => 'TipoDispositivoController@show']);
                Route::get('edit/{id}', ['as' => 'showEditDeviceType', 'uses' => 'TipoDispositivoController@showEdit']);
                Route::post('edit', ['as' => 'editDeviceType', 'uses' => 'TipoDispositivoController@edit']);
                Route::get('delete/{id}', ['as' => 'deleteDeviceType', 'uses' => 'TipoDispositivoController@delete']);
            });
        });

        Route::group(['prefix' => 'subnet'], function () {
            Route::get('list', ['as' => 'indexSubrede', 'uses' => 'SubredeController@index']);
            Route::get('add', ['as' => 'createSubrede', 'uses' => 'SubredeController@create']);
            Route::post('add', ['as' => 'storeSubrede', 'uses' => 'SubredeController@store']);
            Route::get('edit/{subrede}', ['as' => 'editSubrede', 'uses' => 'SubredeController@edit']);
            Route::post('edit', ['as' => 'updateSubrede', 'uses' => 'SubredeController@update']);
            Route::get('delete/{subrede}', ['as' => 'destroySubrede', 'uses' => 'SubredeController@destroy']);
        });

        Route::group(['prefix' => 'user/type'], function(){
            Route::get('add', ['as' => 'showAddUserType', 'uses' => 'TipoUsuarioController@showAdd']);
            Route::post('add', ['as' => 'addUserType', 'uses' => 'TipoUsuarioController@add']);
            Route::get('list', ['as' => 'listUserType', 'uses' => 'TipoUsuarioController@show']);
            Route::get('edit/{id}', ['as' => 'showEditUserType', 'uses' => 'TipoUsuarioController@showEdit']);
            Route::post('edit', ['as' => 'editUserType', 'uses' => 'TipoUsuarioController@edit']);
            Route::get('delete/{id}', ['as' => 'deleteUserType', 'uses' => 'TipoUsuarioController@delete']);
        });
    });

    Route::get('/', ['as' => 'home', 'uses' => 'PagesController@home']);
    Route::get('/home', 'PagesController@home');
    Route::get('/sobre', ['as' => 'about', 'uses' => 'PagesController@about']);
    Route::post('/searchperson', ['as' => 'searchLdapUser', 'uses' => 'UserController@searchPerson']);

    Route::group(['prefix' => 'request'], function(){
        Route::get('add', ['as' => 'showAddRequest', 'uses' => 'RequisicaoController@showAdd']);
        Route::post('add', ['as' => 'storeRequest', 'uses' => 'RequisicaoController@store']);
        Route::get('details/{id}', ['as' => 'detailsRequest', 'uses' => 'RequisicaoController@details']);
        Route::post('delete', ['as' => 'deleteRequest', 'uses' => 'RequisicaoController@delete']);
        Route::get('edit/{id}', ['as' => 'showEditRequest', 'uses' => 'RequisicaoController@showEdit']);
        Route::post('edit', ['as' => 'editRequest', 'uses' => 'RequisicaoController@edit']);
        Route::get('list/user', ['as' => 'listUserRequests', 'uses' => 'RequisicaoController@showFromUser']);
        Route::get('term/{filepath}', ['as' => 'showTermRequest', 'uses' => 'RequisicaoController@showTerm']);
    });

    Route::group(['prefix' =>'export'], function(){
        Route::get('config', ['as' => 'exportConfig', 'uses' => 'PagesController@exportConfig']);
    });
});

Route::get('/login', ['as' => 'showLogin', 'uses' => 'Auth\LoginController@showLogin']);
Route::post('/login', ['as' => 'login', 'uses' => 'Auth\LoginController@postLogin']);
Route::get('/sair', ['as' => 'logout', 'uses' => 'Auth\LoginController@logout']);

Route::get('test', 'RequisicaoController@refreshPfsense');
