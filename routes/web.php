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
    $router->post('/login/password', ['middleware' => 'throttle', 'uses' => 'Auth\AuthController@loginPassword']);
    $router->post('/request/OTP', ['middleware' => 'throttle', 'uses' => 'Auth\AuthController@requestOtp']);
    $router->post('/login/verify/OTP', ['middleware' => 'throttle', 'uses' => 'Auth\AuthController@verifyOtp']);
    $router->post('/register', ['uses' => 'Auth\RegisterController@register']); // Route untuk registrasi
    $router->get('/verify/{token}', ['uses' => 'Auth\RegisterController@verify']);
    $router->get('/check-token-data', ['uses' => 'Token\TokenController@checkTokenData']);
});

$router->get('home/carousel', ['uses' => 'HomeController@carousel']);
$router->get('home/statistic', ['uses' => 'HomeController@statistic']);
$router->get('home/category', ['uses' => 'HomeController@category']);
$router->get('home/popular-category', ['uses' => 'HomeController@popularCategory']);
$router->get('home/company', ['uses' => 'HomeController@company']);
$router->get('home/product', ['uses' => 'HomeController@product']);
$router->get('home/video', ['uses' => 'HomeController@video']);
$router->get('home/news', ['uses' => 'HomeController@news']);


//Search
$router->get('search', ['uses' => 'SearchController@index']);

//Product
$router->get('products/{slug}', ['uses' => 'ProductController@detail']);

//Media Resource
$router->get('media-resource/{slug}', ['uses' => 'MediaResourceController@detail']);

//Project
$router->get('project/{slug}', ['uses' => 'ProjectController@detail']);

//Videos
$router->get('videos/{slug}', ['uses' => 'VideoController@detail']);

//News
$router->get('news/{slug}', ['uses' => 'NewsController@detail']);
