<?php

namespace AutoCode\AppRouter\Common;

use AutoCode\AppRouter\Abstractions\AbstractRoute;
use AutoCode\AppRouter\Interfaces\ControllerInterface;
use AutoCode\AppRouter\Utils\ResponseXml;
use Closure;

final class Response
{

    private static array $contents = [
        'document/html' => 'toHtml',
        'application/xml' => 'toXml',
        'application/json' => 'toJson',
    ];
    private readonly AbstractRoute $route;

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

        foreach (self::$contents as $type => $method) {
            if ($contentType === $type) {
                $this->echo($action, $type, $method);
            }
        }
    }

    private function echo(mixed $action, string $contentType, string $method): void
    {
        $data = null;
        header('Content-Type: ' . $contentType);

        if (($action instanceof Closure) || is_callable($action)) {
            $data = $action();
        }

        if ($action instanceof ControllerInterface) {
            $data = $action->render();
        }

        echo $this->$method($data);
    }

    private function toHtml(mixed $data): string|null
    {
        if (is_string($data)) {
            return $data;
        }

        return null;
    }

    private function toJson(mixed $data): string
    {
        if (is_string($data)) {
            return $data;
        }

        return json_encode($data);
    }

    private function toXml(mixed $data): string
    {
        return new ResponseXml($data);
    }

}