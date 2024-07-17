<?php

use Illuminate\Support\Facades\Facade;

require_once __DIR__ . '/../vendor/autoload.php';

if (!function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param  string  $path
     * @return string
     */
    function public_path($path = '')
    {
        return rtrim(app()->basePath('public/' . $path), '/');
    }
}

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

// Enable facades
$app->withFacades();

// Enable Eloquent
$app->withEloquent();

// Register configurations
$app->configure('app');
$app->configure('cors');
$app->configure('jwt');
$app->configure('mail');
$app->configure('swagger-lume');
$app->configure('filesystems');

// Register Service Providers
$app->register(App\Providers\AppServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);
$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
$app->register(Fruitcake\Cors\CorsServiceProvider::class);
$app->register(\SwaggerLume\ServiceProvider::class);
$app->register(Laravel\Tinker\TinkerServiceProvider::class);
$app->register(Illuminate\Filesystem\FilesystemServiceProvider::class);
$app->register(Intervention\Image\ImageServiceProviderLumen::class);

// Aliases, if needed
if (!class_exists('JWTAuth')) {
    class_alias(Tymon\JWTAuth\Facades\JWTAuth::class, 'JWTAuth');
    class_alias(Tymon\JWTAuth\Facades\JWTFactory::class, 'JWTFactory');
}

class_alias('Intervention\Image\Facades\Image', 'Image');

// Register Middleware
$app->middleware([
    Fruitcake\Cors\HandleCors::class,
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'company.auth' => App\Http\Middleware\CompanyAuthenticate::class,
    'throttle' => App\Http\Middleware\ThrottleLogins::class,
    'log.user.activity' => App\Http\Middleware\LogUserActivity::class,
]);

// Load routes
$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__ . '/../routes/web.php';
});

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

return $app;
