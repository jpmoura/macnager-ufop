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
        Route::get('apply', ['as' => 'applyChanges','uses' => 'PfsenseController@applyChanges']);
        Route::resource('ldapuser', 'UserController', ['except' => 'show']);

        Route::group(['prefix' => 'request'], function() {
            Route::get('list/all/{type}', ['as' => 'indexAllRequisicao', 'uses' => 'RequisicaoController@allIndex']);
            Route::post('approve', ['as' => 'approveRequisicao', 'uses' => 'RequisicaoController@approve']);
            Route::post('block', ['as' => 'blockRequisicao', 'uses' => 'RequisicaoController@block']);
            Route::post('disable', ['as' => 'disableRequisicao', 'uses' => 'RequisicaoController@disable']);
            Route::post('deny', ['as' => 'denyRequisicao', 'uses' => 'RequisicaoController@deny']);
            Route::get('reactive/{requisicao}', ['as' => 'reactiveRequisicao', 'uses' => 'RequisicaoController@reactive']);
        });

        Route::group(['prefix' => 'device'], function(){
            Route::get('add', ['as' => 'createDevice', 'uses' => 'RequisicaoController@createDevice']);
            Route::post('add', ['as' => 'storeDevice', 'uses' => 'RequisicaoController@storeDevice']);
            Route::get('edit/{requisicao}', ['as' => 'editDevice', 'uses' => 'RequisicaoController@editDevice']);
            Route::post('edit', ['as' => 'updateDevice', 'uses' => 'RequisicaoController@updateDevice']);
            Route::get('list/{status}', ['as' => 'indexDevice', 'uses' => 'RequisicaoController@indexDevice']);

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
            Route::get('ips/{subrede}', ['as' => 'availableIps' , 'uses' => 'SubredeController@getAvailableIps']);
        });

        Route::group(['prefix' => 'user/type'], function(){
            Route::get('add', ['as' => 'createTipoUsuario', 'uses' => 'TipoUsuarioController@create']);
            Route::post('add', ['as' => 'storeTipoUsuario', 'uses' => 'TipoUsuarioController@store']);
            Route::get('list', ['as' => 'indexTipoUsuario', 'uses' => 'TipoUsuarioController@index']);
            Route::get('edit/{tipousuario}', ['as' => 'editTipoUsuario', 'uses' => 'TipoUsuarioController@edit']);
            Route::post('edit', ['as' => 'updateTipoUsuario', 'uses' => 'TipoUsuarioController@update']);
            Route::get('delete/{tipousuario}', ['as' => 'deleteTipoUsuario', 'uses' => 'TipoUsuarioController@delete']);
        });

        Route::group(['prefix' =>'export'], function(){
            Route::get('config', ['as' => 'exportConfig', 'uses' => 'PfsenseController@exportConfig']);
        });
    });

    Route::get('/', ['as' => 'home', 'uses' => 'PagesController@home']);
    Route::get('/home', 'PagesController@home');
    Route::get('/sobre', ['as' => 'about', 'uses' => 'PagesController@about']);
    Route::post('/searchperson', ['as' => 'searchLdapUser', 'uses' => 'UserController@searchPerson']);

    Route::group(['prefix' => 'request'], function(){
        Route::get('add', ['as' => 'createRequisicao', 'uses' => 'RequisicaoController@create']);
        Route::post('add', ['as' => 'storeRequisicao', 'uses' => 'RequisicaoController@store']);
        Route::get('show/{requisicao}', ['as' => 'showRequisicao', 'uses' => 'RequisicaoController@show']);
        Route::post('delete', ['as' => 'deleteRequisicao', 'uses' => 'RequisicaoController@delete']);
        Route::get('edit/{requisicao}', ['as' => 'editRequisicao', 'uses' => 'RequisicaoController@edit']);
        Route::post('edit', ['as' => 'updateRequisicao', 'uses' => 'RequisicaoController@update']);
        Route::get('list/user', ['as' => 'indexUserRequisicao', 'uses' => 'RequisicaoController@userIndex']);
        Route::get('term/{filepath}', ['as' => 'showTermRequisicao', 'uses' => 'RequisicaoController@showTerm']);
    });
});

Route::get('/login', ['as' => 'showLogin', 'uses' => 'Auth\LoginController@showLogin']);
Route::post('/login', ['as' => 'login', 'uses' => 'Auth\LoginController@postLogin']);
Route::get('token/generate', ['as' => 'loginViaMiddleware', 'uses' => 'Auth\LoginController@generateMeuIceaToken']);
Route::get('token/login/{token}', ['as' => 'loginViaToken', 'uses' => 'Auth\LoginController@tokenLogin']);
Route::get('/sair', ['as' => 'logout', 'uses' => 'Auth\LoginController@logout']);
