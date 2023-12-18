<?php

namespace AutoCode\AppRouter\Common;

use AutoCode\AppRouter\Abstractions\AbstractRoute;
use AutoCode\AppRouter\Interfaces\ControllerInterface;
use Closure;
use CloudCastle\Xml\Generator\XmlService;

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

    private function toHtml(mixed $data): string
    {
        if (is_string($data)) {
            return $data;
        }
    }

    private function toJson(mixed $data): string
    {
        if (is_string($data)) {
            return $data;
        } else {
            return json_encode($data, JSON_THROW_ON_ERROR);
        }
    }

    private function toXml(mixed $data): string
    {
        $xml = new XmlService();
        $xml->startElement('root');

        if (is_array($data) or is_object($data)) {
            foreach ($data as $key => $value) {
                $this->addElement($key, $value, $xml);
            }
        }

        $xml->closeElement();

        return $xml->get();
    }

    private function addElement(int|string $key, mixed $value, XmlService $xml): void
    {
        if (is_string($value) || is_int($value) || is_bool($value) || $value === null) {
            $xml->addElement($key, (string)$value);
        } else {
            $xml->startElement($key);
            foreach ($value as $k => $val) {
                $this->addElement($k, $val, $xml);
            }
            $xml->closeElement();
        }
    }
}