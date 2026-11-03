<?php
/**
 * Front controller: TODAS las peticiones entran por aquí.
 * (En Laravel este archivo existe igual: public/index.php)
 */
declare(strict_types=1);

session_start();

define('BASE_PATH', dirname(__DIR__));
define('VIEWS', BASE_PATH . '/views');

// Carpeta desde la que se sirve el sitio ('' si está en la raíz del dominio,
// '/PortalArtesanoPHP/public' si está en una subcarpeta de XAMPP/Apache).
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
define('BASE_URL', rtrim(dirname($script), '/'));

// Autoload simple: App\Controllers\X  ->  app/Controllers/X.php
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = BASE_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

require BASE_PATH . '/app/helpers.php';

// Router mínimo. Las rutas se definen en routes/web.php
$router = new App\Router();
require BASE_PATH . '/routes/web.php';

$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';

// Quitar la subcarpeta de la ruta para que el router vea '/productos', no '/Carpeta/public/productos'
$bases = array_unique([BASE_URL, preg_replace('#/public$#', '', BASE_URL)]);
usort($bases, fn ($a, $b) => strlen($b) <=> strlen($a));
foreach ($bases as $b) {
    if ($b !== '' && str_starts_with($path . '/', $b . '/')) {
        $path = substr($path, strlen($b)) ?: '/';
        break;
    }
}
$path = preg_replace('#^/index\.php#', '', $path) ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($method, $path);
