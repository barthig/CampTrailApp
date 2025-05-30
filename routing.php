<?php
declare(strict_types=1);



/**
 * Simple Router class to register and dispatch HTTP routes.
 */
class Router
{
    /** @var array<string, array<string, array{controller:string,method:string}>> */
    private static array $routes = [
        'GET'  => [],
        'POST' => [],
    ];

    /**
     * Register a GET route.
     * @param string $path
     * @param string $controllerClass
     * @param string $method
     */
    public static function get(string $path, string $controllerClass, string $method): void
    {
        self::$routes['GET'][$path] = ['controller' => $controllerClass, 'method' => $method];
    }

    /**
     * Register a POST route.
     * @param string $path
     * @param string $controllerClass
     * @param string $method
     */
    public static function post(string $path, string $controllerClass, string $method): void
    {
        self::$routes['POST'][$path] = ['controller' => $controllerClass, 'method' => $method];
    }

    /**
     * Dispatch the current request to the matched controller action.
     * @param string $path
     * @param PDO $pdo
     */
    public static function run(string $path, PDO $pdo): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $routes = self::$routes[$method] ?? [];

        if (!isset($routes[$path])) {
            // No route matched -> 404
            require_once __DIR__ . '/src/Controllers/AppController.php';
            require_once __DIR__ . '/src/Controllers/DefaultController.php';
            $controller = new \src\Controllers\DefaultController($pdo);
            $controller->error404();
            return;
        }

        $handler = $routes[$path];
        // Dynamically include controller file
        $controllerFile = __DIR__ . '/src/Controllers/'
            . basename(str_replace('\\', '/', $handler['controller']))
            . '.php';
        require_once $controllerFile;

        $controllerClass = $handler['controller'];
        $controller = new $controllerClass($pdo);
        $action = $handler['method'];

        // Execute controller action
        $controller->$action();
    }

    /**
     * Return all registered routes.
     * @return array<string, array<string, array{controller:string,method:string}>>
     */
    public static function getRoutes(): array
    {
        return self::$routes;
    }
}
