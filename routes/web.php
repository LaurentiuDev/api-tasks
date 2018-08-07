<?php

define("API_VERSION", 'v1');
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version() . ' - ' . 'Current API version: ' . API_VERSION;
});

/** CORS */
$router->options(
    '/{any:.*}', [
    'middleware' => ['cors'],
    function () {
        return response('OK', 200);
    }
]);

/** Routes that doesn't require auth */
$router->group(['namespace' => API_VERSION, 'prefix' => API_VERSION, 'middleware' => ['cors']], function () use ($router) {
    $router->post('/login', ['uses' => 'UserController@login']);
    $router->post('/logout',['uses' => 'UserController@logout']);
    $router->post('/register', ['uses' => 'RegistrationController@register']);
    $router->post('/linkResetPassword',['uses' =>'ResetPasswordController@sentEmail']);
    $router->post('/resetPassword/{link}',['uses' =>'ResetPasswordController@reset']);

});

/** Routes with auth */
$router->group(['namespace' => API_VERSION, 'prefix' => API_VERSION, 'middleware' => 'cors|jwt'], function () use ($router) {
    $router->post('/changeInfo',['uses' => 'UserController@chengeInfo']);
    $router->put('/task/{id}/addComment' ,['uses' => 'CommentController@addComment']);
    $router->put('/confirmAccount/{id}',['uses' => 'AdminController@activateAccount']);
    $router->post('/addRole',['uses' => 'AdminController@addRole']);
    $router->put('/editRole/{id}',['uses' => 'AdminController@editRole']);
    $router->delete('/deleteRole/{id}',['uses' => 'AdminController@deleteRole']);
    $router->post('/createUser',['uses' => 'AdminController@createUser']);
    $router->put('/editUser/{id}',['uses' => 'AdminController@editUser']);
    $router->delete('/deleteUser/{id}',['uses' => 'AdminController@deleteUser']);
    $router->get('/getTasks',['uses' => 'TasksController@getTasks']);
    $router->post('/addTask',['uses' => 'TasksController@addTask']);
    $router->put('/editTask/{id}',['uses' => 'TasksController@editTask']);
    $router->delete('/delete/{id}',['uses' => 'TasksController@delete']);
    $router->put('/editComment/{id}',['uses' => 'CommentController@editComment']);
    $router->delete('/deleteComment/{id}',['uses' => 'CommentController@deleteComment']);
});