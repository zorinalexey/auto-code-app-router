<?php

namespace AutoCode\AppRouter\Common;

use AutoCode\AppRouter\Abstractions\AbstractRoute;
use Closure;

final class Response
{

    private readonly AbstractRoute $route;

    private static array $contents = [
        'document/html' => 'toString',
        'application/xml' => 'toXml',
        'application/json' => 'toJson',
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

        foreach (self::$contents as $type => $method){
            header('Content-Type :'.$type);
            if($contentType === $type){
                $this->$method($action);
            }
        }
    }

    private function toString(mixed $action):void
    {
        if (($action instanceof Closure) || is_callable($action)) {
            echo $action();
        }
    }

    private function toJson(mixed $action):void
    {
        $data = $action();

        echo json_encode($data, JSON_THROW_ON_ERROR);
    }
}