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

$router->get('countries', 'CountryStateCityController@getCountries');
$router->get('countries/{countryId}/states', 'CountryStateCityController@getStates');
$router->get('countries/{countryId}/states/{stateId}/cities', 'CountryStateCityController@getCities');

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

            $router->get('/company-representative', ['uses' => 'CompanyRepresentativeController@index']);
            $router->post('/company-representative', ['uses' => 'CompanyRepresentativeController@store']);
            $router->put('/company-representative/{id}', ['uses' => 'CompanyRepresentativeController@update']);
            $router->delete('/company-representative/{id}', ['uses' => 'CompanyRepresentativeController@delete']);


            // Company Products
            $router->get('/company-products', ['uses' => 'CompanyProductController@index']);
            $router->post('/company-products', ['uses' => 'CompanyProductController@store']);
            $router->get('/company-products/{slug}/edit', ['uses' => 'CompanyProductController@edit']);
            $router->post('/company-products-delete/{slug}', ['uses' => 'CompanyProductController@destroy']);
            $router->post('/company-products/{id}', ['uses' => 'CompanyProductController@update']);
            $router->post('/company-products-listing', ['uses' => 'CompanyProductController@listing']);

            //Company Projects
            $router->get('/company-projects', ['uses' => 'CompanyProjectController@index']);
            $router->post('/company-projects', ['uses' => 'CompanyProjectController@store']);
            $router->get('/company-projects/{slug}/edit', ['uses' => 'CompanyProjectController@edit']);
            $router->post('/company-projects-delete/{slug}', ['uses' => 'CompanyProjectController@destroy']);
            $router->post('/company-projects/{id}', ['uses' => 'CompanyProjectController@update']);
            $router->post('/company-projects-listing', ['uses' => 'CompanyProjectController@listing']);

            //Company News
            $router->get('/company-news', ['uses' => 'CompanyNewsController@index']);
            $router->post('/company-news', ['uses' => 'CompanyNewsController@store']);
            $router->get('/company-news/{slug}/edit', ['uses' => 'CompanyNewsController@edit']);
            $router->post('/company-news-delete/{slug}', ['uses' => 'CompanyNewsController@destroy']);
            $router->post('/company-news/{id}', ['uses' => 'CompanyNewsController@update']);
            $router->post('/company-news-listing', ['uses' => 'CompanyNewsController@listing']);

            //Company Media Resource
            $router->get('/company-media', ['uses' => 'CompanyMediaController@index']);
            $router->post('/company-media', ['uses' => 'CompanyMediaController@store']);
            $router->get('/company-media/{slug}/edit', ['uses' => 'CompanyMediaController@edit']);
            $router->post('/company-media-delete/{slug}', ['uses' => 'CompanyMediaController@destroy']);
            $router->post('/company-media/{id}', ['uses' => 'CompanyMediaController@update']);
            $router->post('/company-media-listing', ['uses' => 'CompanyMediaController@listing']);

            //Company Video
            $router->get('/company-videos', ['uses' => 'CompanyVideosController@index']);
            $router->post('/company-videos', ['uses' => 'CompanyVideosController@store']);
            $router->get('/company-videos/{slug}/edit', ['uses' => 'CompanyVideosController@edit']);
            $router->post('/company-videos-delete/{slug}', ['uses' => 'CompanyVideosController@destroy']);
            $router->post('/company-videos/{id}', ['uses' => 'CompanyVideosController@update']);
            $router->post('/company-videos-listing', ['uses' => 'CompanyVideosController@listing']);
        });
    });

    $router->post('check-email', ['uses' => 'UserController@checkEmail']);
});
