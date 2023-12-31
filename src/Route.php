<?php

namespace AutoCode\AppRouter;

use AutoCode\AppRouter\Abstractions\AbstractRoute as AbstractRoutes;
use AutoCode\AppRouter\Common\Request;
use AutoCode\AppRouter\Common\Response;
use AutoCode\AppRouter\Interfaces\SetRoutesInterface;
use AutoCode\AppRouter\Routes\Group;
use AutoCode\AppRouter\Utils\RouterStaticMethodsTrait;

if (!defined('DEFAULT_PORTS')) {
    define('DEFAULT_PORTS', [80, 443]);
}

final class Route
{
    use RouterStaticMethodsTrait;

    private static self|null $instance = null;

    private static array $routes = [];

    private function __construct()
    {

    }

    public static function setRoute(AbstractRoutes $route): SetRoutesInterface
    {
        self::$routes[] = $route;

        return $route;
    }

    public static function group(string $prefix, array $routes): Group
    {
        $route = new Group($routes);
        $route->prefix($prefix);

        return $route;
    }

    public function run(): Response|null
    {
        $routes = $this->getRoutes();
        $request = Request::getInstance();

        foreach ($routes as $route) {
            if (
                $route instanceof AbstractRoutes &&
                ($info = $route->getInfo()) && isset($info['pattern']) &&
                $info['method']->value === $request->params->request_method &&
                preg_match($info['pattern'], $request->params->request_patch) &&
                in_array((int)$request->params->request_port, $info['ports'], true)
            ) {
                return new Response($route);
            }
        }

        return null;
    }

    public function getRoutes(): array
    {
        return self::$routes;
    }

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __clone()
    {

    }

}