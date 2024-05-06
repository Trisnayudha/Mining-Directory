<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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
    return $router->app->version();
});


$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('/login', ['middleware' => 'throttle', 'uses' => 'Auth\AuthController@login']);
    $router->post('/register', ['uses' => 'Auth\RegisterController@register']); // Route untuk registrasi
    $router->get('/check-token-data', ['uses' => 'Token\TokenController@checkTokenData']);
});

$router->get('home/carousel', ['uses' => 'HomeController@carousel']);
$router->get('home/statistic', ['uses' => 'HomeController@statistic']);
$router->get('home/category', ['uses' => 'HomeController@category']);
$router->get('home/company', ['uses' => 'HomeController@company']);
$router->get('home/product', ['uses' => 'HomeController@product']);
$router->get('home/video', ['uses' => 'HomeController@video']);
$router->get('home/news', ['uses' => 'HomeController@news']);
