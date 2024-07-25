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
    $router->group(['prefix' => 'company'], function () use ($router) {
        $router->post('/login-password', ['middleware' => 'throttle', 'uses' => 'Auth\CompanyAuthController@loginPassword']);
        $router->post('/register-personal-info', ['uses' => 'Auth\CompanyRegisterController@registerPersonalInfo']);
        $router->post('/register-company-info', ['uses' => 'Auth\CompanyRegisterController@registerCompanyInfo']);
        $router->get('/verify/{token}', ['uses' => 'Auth\CompanyRegisterController@verify']);
        $router->post('/request-otp', ['middleware' => 'throttle', 'uses' => 'Auth\CompanyAuthController@requestOtp']);
        $router->post('/login/verify-otp', ['middleware' => 'throttle', 'uses' => 'Auth\CompanyAuthController@verifyOtp']);
    });
});

$router->group(['middleware' => 'log.user.activity'], function () use ($router) {

    $router->get('home/carousel', ['uses' => 'HomeController@carousel']);
    $router->get('home/statistic', ['uses' => 'HomeController@statistic']);
    $router->get('home/category', ['uses' => 'HomeController@category']);
    $router->get('home/popular-category', ['uses' => 'HomeController@popularCategory']);
    $router->get('home/company', ['uses' => 'HomeController@company']);
    $router->get('home/product', ['uses' => 'HomeController@product']);
    $router->get('home/video', ['uses' => 'HomeController@video']);
    $router->get('home/news', ['uses' => 'HomeController@news']);
    $router->post('home/contact-us', ['uses' => 'HelpController@contactUs']);

    $router->get('faq-home', ['uses' => 'HelpController@faqHome']);
    $router->get('privacy-policy', ['uses' => 'HelpController@privacyPolicy']);
    $router->get('term-condition', ['uses' => 'HelpController@termCondition']);

    $router->get('profile/faq', ['uses' => 'HelpController@faqProfile']);
    //Company
    $router->get('company', ['uses' => 'CompanyController@list']);

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

    //Company
    $router->get('company/{slug}', ['uses' => 'CompanyController@detail']);
    $router->get('company/section/{slug}', ['uses' => 'CompanyController@sectionDetail']);
    $router->post('company/inquiry', ['uses' => 'CompanyController@addInquiry']);

    //users
    $router->group(['middleware' => 'auth'], function () use ($router) {
        //Profile
        $router->get('profile', ['uses' => 'UserController@detail']);
        $router->post('profile/edit', ['uses' => 'UserController@editProfile']);
        $router->post('profile/edit/detail', ['uses' => 'UserController@editProfileDetail']);
        $router->post('profile/bio', ['uses' => 'UserController@editProfileBio']);
        $router->post('profile/background', ['uses' => 'UserController@editProfileBackground']);
        $router->post('profile/change-password', ['uses' => 'UserController@changePassword']);
        //Company
        $router->get('business-card', ['uses' => 'UserController@businesscard']);
        $router->get('favorite/company', ['uses' => 'UserController@favorite']);
        //Company Action
        $router->post('create-favorite', ['uses' => 'CompanyController@addFavorite']);
        $router->post('sent-business-card', ['uses' => 'CompanyController@addBusinessCard']);
    });

    //company
    $router->group(['middleware' => 'company.auth'], function () use ($router) {
        $router->group(['prefix' => 'api/company'], function () use ($router) {
            $router->get('/dashboard-card', ['uses' => 'CompanyDashboardController@card']);
            $router->get('/dashboard-list-of-visitor', ['uses' => 'CompanyDashboardController@listVisitor']);
            $router->get('/dashboard-list-of-inquiry', ['uses' => 'CompanyDashboardController@listInquiry']);
            $router->post('/action-inquiry', ['uses' => 'CompanyDashboardController@approveInquiry']);
            $router->get('/dashboard-list-of-businesscard', ['uses' => 'CompanyDashboardController@listBusinessCard']);
            $router->get('/dashboard-visit-analyst', ['uses' => 'CompanyDashboardController@visitAnalyst']);
            $router->get('/dashboard-asset-analyst', ['uses' => 'CompanyDashboardController@assetAnalyst']);

            $router->get('/company-information', ['uses' => 'CompanyInformationController@index']);
            $router->post('/company-information', ['uses' => 'CompanyInformationController@store']);

            $router->get('/company-address', ['uses' => 'CompanyAddressController@index']);
            $router->post('/company-address', ['uses' => 'CompanyAddressController@store']);
            $router->put('/company-address/{id}', ['uses' => 'CompanyAddressController@update']);
            $router->delete('/company-address/{id}', ['uses' => 'CompanyAddressController@delete']);
        });
    });

    $router->post('check-email', ['uses' => 'UserController@checkEmail']);
});
