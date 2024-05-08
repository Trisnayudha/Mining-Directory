<?php
// Tambahkan ini di bagian atas file
use Illuminate\Support\Facades\Facade;

require_once __DIR__ . '/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

$app = new Laravel\Lumen\Application(dirname(__DIR__));

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

// Register Service Providers
$app->register(App\Providers\AppServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);
$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
$app->register(Fruitcake\Cors\CorsServiceProvider::class);
$app->register(\SwaggerLume\ServiceProvider::class);
$app->register(Laravel\Tinker\TinkerServiceProvider::class);

// Aliases, if needed
if (!class_exists('JWTAuth')) {
    class_alias(Tymon\JWTAuth\Facades\JWTAuth::class, 'JWTAuth');
    class_alias(Tymon\JWTAuth\Facades\JWTFactory::class, 'JWTFactory');
}

// Register Middleware
$app->middleware([
    Fruitcake\Cors\HandleCors::class,
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'throttle' => App\Http\Middleware\ThrottleLogins::class,
]);

// Load routes
$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__ . '/../routes/web.php';
});

return $app;
