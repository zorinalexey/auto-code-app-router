<?php

namespace AutoCode\AppRouter\Common;

use AutoCode\AppRouter\Abstractions\AbstractRoute;
use Closure;

final class ResponseValidated
{

    private readonly AbstractRoute $route;

    public function __construct(AbstractRoute $route)
    {
        $this->route = $route;

        $this->response();
    }

    private function response():void
    {
        $action = $this->route->getAction();

        if(( $action instanceof Closure) || is_callable($action)){
            $action();
        }
    }
}