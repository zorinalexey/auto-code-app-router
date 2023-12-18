<?php

namespace AutoCode\AppRouter\Utils;

use AutoCode\AppRouter\Interfaces\SetRoutesInterface;
use AutoCode\AppRouter\Routes\Get;
use AutoCode\AppRouter\Routes\Lock;
use AutoCode\AppRouter\Routes\Post;
use AutoCode\AppRouter\Routes\Propfind;
use AutoCode\AppRouter\Routes\Purge;
use AutoCode\AppRouter\Routes\Unlock;
use AutoCode\AppRouter\Routes\View;
use Closure;

trait RouterStaticMethodsTrait
{
    final public static function get(string $url, Closure|callable|array|string|null $action): SetRoutesInterface
    {
        $route = new Get();
        $route->url($url)->action($action)->method(RequestMethodEnum::GET)->port(DEFAULT_PORTS);

        return self::setRoute($route);
    }

    final public static function post(string $url, Closure|callable|array|string|null $action): SetRoutesInterface
    {
        $route = new Post();
        $route->url($url)->action($action)->method(RequestMethodEnum::POST)->port(DEFAULT_PORTS);

        return self::setRoute($route);
    }

    final public static function view(string $url, Closure|callable|array|string|null $action): SetRoutesInterface
    {
        $route = new View();
        $route->url($url)->action($action)->method(RequestMethodEnum::VIEW)->port(DEFAULT_PORTS);

        return self::setRoute($route);
    }

    final public static function propfind(string $url, Closure|callable|array|string|null $action): SetRoutesInterface
    {
        $route = new Propfind();
        $route->url($url)->action($action)->method(RequestMethodEnum::PROPFIND)->port(DEFAULT_PORTS);

        return self::setRoute($route);
    }

    final public static function unlock(string $url, Closure|callable|array|string|null $action): SetRoutesInterface
    {
        $route = new Unlock();
        $route->url($url)->action($action)->method(RequestMethodEnum::UNLOCK)->port(DEFAULT_PORTS);

        return self::setRoute($route);
    }

    final public static function lock(string $url, Closure|callable|array|string|null $action): SetRoutesInterface
    {
        $route = new Lock();
        $route->url($url)->action($action)->method(RequestMethodEnum::LOCK)->port(DEFAULT_PORTS);

        return self::setRoute($route);
    }

    final public static function purge(string $url, Closure|callable|array|string|null $action): SetRoutesInterface
    {
        $route = new Purge();
        $route->url($url)->action($action)->method(RequestMethodEnum::PURGE)->port(DEFAULT_PORTS);

        return self::setRoute($route);
    }
}