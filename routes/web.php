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
            Route::get('list/all/{type}', ['as' => 'showRequest', 'uses' => 'RequisicaoController@allIndex']);
            Route::post('approve', ['as' => 'approveRequest', 'uses' => 'RequisicaoController@approve']);
            Route::post('block', ['as' => 'blockRequest', 'uses' => 'RequisicaoController@block']);
            Route::post('disable', ['as' => 'disableRequest', 'uses' => 'RequisicaoController@disable']);
            Route::post('deny', ['as' => 'denyRequest', 'uses' => 'RequisicaoController@deny']);
            Route::get('reactive/{requisicao}', ['as' => 'reactiveRequest', 'uses' => 'RequisicaoController@reactive']);
        });

        Route::group(['prefix' => 'device'], function(){
            Route::get('add', ['as' => 'showAddDevice', 'uses' => 'RequisicaoController@createDevice']);
            Route::post('add', ['as' => 'addDevice', 'uses' => 'RequisicaoController@storeDevice']);
            Route::get('edit/{requisicao}', ['as' => 'showEditDevice', 'uses' => 'RequisicaoController@editDevice']);
            Route::post('edit', ['as' => 'editDevice', 'uses' => 'RequisicaoController@updateDevice']);
            Route::get('list/{status}', ['as' => 'listDevice', 'uses' => 'RequisicaoController@indexDevice']);

            Route::group(['prefix' => 'type'], function(){
                Route::get('add', ['as' => 'createTipoDispositivo', 'uses' => 'TipoDispositivoController@create']);
                Route::post('add', ['as' => 'storeTipoDispositivo', 'uses' => 'TipoDispositivoController@store']);
                Route::get('list', ['as' => 'indexTipoDispositivo', 'uses' => 'TipoDispositivoController@index']);
                Route::get('edit/{tipodispositivo}', ['as' => 'editTipoDispositivo', 'uses' => 'TipoDispositivoController@edit']);
                Route::post('edit', ['as' => 'updateTipoDispositivo', 'uses' => 'TipoDispositivoController@update']);
                Route::get('delete/{tipodispositivo}', ['as' => 'deleteTipoDispositivo', 'uses' => 'TipoDispositivoController@delete']);
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
        Route::get('add', ['as' => 'createRequest', 'uses' => 'RequisicaoController@create']);
        Route::post('add', ['as' => 'storeRequest', 'uses' => 'RequisicaoController@store']);
        Route::get('show/{requisicao}', ['as' => 'showRequest', 'uses' => 'RequisicaoController@show']);
        Route::post('delete', ['as' => 'deleteRequest', 'uses' => 'RequisicaoController@delete']);
        Route::get('edit/{requisicao}', ['as' => 'editRequest', 'uses' => 'RequisicaoController@edit']);
        Route::post('edit', ['as' => 'updateRequest', 'uses' => 'RequisicaoController@update']);
        Route::get('list/user', ['as' => 'indexUserRequests', 'uses' => 'RequisicaoController@userIndex']);
        Route::get('term/{filepath}', ['as' => 'showTermRequest', 'uses' => 'RequisicaoController@showTerm']);
    });

    Route::group(['prefix' =>'export'], function(){
        Route::get('config', ['as' => 'exportConfig', 'uses' => 'PagesController@exportConfig']);
    });
});

Route::get('/login', ['as' => 'showLogin', 'uses' => 'Auth\LoginController@showLogin']);
Route::post('/login', ['as' => 'login', 'uses' => 'Auth\LoginController@postLogin']);
Route::get('/sair', ['as' => 'logout', 'uses' => 'Auth\LoginController@logout']);

Route::get('test/{subrede}', 'SubredeController@getAvailableIps');
