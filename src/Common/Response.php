<?php

namespace AutoCode\AppRouter\Common;

use AutoCode\AppRouter\Abstractions\AbstractRoute;
use AutoCode\AppRouter\Interfaces\ControllerInterface;
use Closure;
use CloudCastle\Xml\Generator\XmlService;
use DateTime;
use Sabre\DAV\Exception;

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

        return json_encode( $data);
    }

    private function toXml(mixed $data): string
    {
        $rootElementName = 'response';
        $attr['type'] = gettype($data);

        if($attr['type'] === 'object'){
            $attr['class'] = $data::class;
        }

        $xml = new XmlService();

        if (is_array($data) || is_object($data)) {
            $xml->startElement($rootElementName, null, $attr);

            foreach ($data as $key => $value) {
                $this->addElement($key, $value, $xml);
            }
        }else{
            $xml->startElement($rootElementName, $data, $attr);
        }

        $xml->closeElement();

        return $xml->get();
    }

    private function addElement(int|string $key, mixed $value, XmlService $xml): void
    {
        $attr['type'] = gettype($value);
        $attr['error'] = false;

        if (is_string($value) ) {
            $this->setTextXmlBlock($value, $xml, $key, $attr);
        }elseif(is_bool($value) || is_numeric($value)){
            $xml->addElement($key, (string)$value, $attr);
        }elseif ($value === null){
            $value = 'NULL';
            $xml->addElement($key, $value, $attr);
        }elseif(!is_resource($value) && !is_callable($value)) {
            $this->setSuperXmlBlock($value, $xml, $key, $attr);
        }else{
            $xml->addElement($key, null, [...$attr, 'message' => 'Не поддерживаемый тип данных', 'error' => true]);
        }
    }

    private function setTextXmlBlock(string $value, XmlService $xml, string|int $key, array $attr):void
    {
        if(strtotime($value)){
            $format = 'Y-m-d H:i:s';
            $attr['type'] = 'date';
            $attr['class'] = DateTime::class;
            $attr['format'] = $format;
            $value = (new DateTime($value))->format($format);
        }

        $xml->addElement($key, $value, $attr, createIfTextNull: true);
    }

    private function setSuperXmlBlock(mixed $value, XmlService $xml, int|string $key, array $attr):void
    {
        if($attr['type'] === 'object'){
            $attr['class'] = $value::class;
        }

        $xml->startElement($key, null, $attr);

        foreach ($value as $k => $val) {
            $this->addElement($k, $val, $xml);
        }

        $xml->closeElement();
    }
}