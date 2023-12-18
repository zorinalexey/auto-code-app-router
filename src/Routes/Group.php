<?php

namespace AutoCode\AppRouter\Routes;

use AutoCode\AppRouter\Abstractions\AbstractRoute;

final class Group extends AbstractRoute
{
    private array $routes = [];

    public function __construct(array $routes)
    {
        $this->routes = $routes;
        $this->setParentGroup();
    }

    public function setParentGroup(): void
    {
        foreach ($this->routes as $route) {
            if (($route instanceof AbstractRoute) && !($route instanceof self)) {
                $route->setParent($this);
            }

            if ($route instanceof self) {
                $route->setParentGroup();
            }
        }
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}