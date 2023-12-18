<?php

namespace AutoCode\AppRouter\Common;

use AutoCode\AppRouter\Abstractions\AbstractRoute;
use AutoCode\AppRouter\Interfaces\ControllerInterface;
use Closure;

final class Response
{

    private readonly AbstractRoute $route;

    private static array $contents = [
        'document/html',
        'application/xml',
        'application/json',
    ];

    public function __construct(AbstractRoute $route)
    {
        $this->route = $route;

        $this->response();
    }

    private function response(): void
    {
        $action = $this->route->getAction();
        $headers = Request::getInstance()->headers;
        $contentType = mb_strtolower(($headers['Content-Type'] ?? 'document/html'));

        foreach (self::$contents as $type){
            if($contentType === $type){
                $this->echo($action, $type);
            }
        }
    }

    private function echo(mixed $action, string $contentType): void
    {
        $data = null;
        header('Content-Type :'.$contentType);

        if (($action instanceof Closure) || is_callable($action)) {
            $data = (string)$action();
        }

        if($action instanceof ControllerInterface){
            $data = $action->render();
        }

        echo $data;
    }
}