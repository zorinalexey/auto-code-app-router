<?php

namespace AutoCode\AppRouter\Utils;

use Closure;
use CloudCastle\Xml\Generator\XmlService;
use DateTime;
use Opis\Closure\SerializableClosure;

final class ResponseXml
{
    private string $xml;

    public function __construct(mixed $data)
    {
        $this->xml = $this->createSchema($data);
    }

    private function createSchema(mixed $data): string
    {
        $rootElementName = config('app', 'xml_response_root_element_name', 'response');
        $attr['type'] = gettype($data);

        if ($attr['type'] === 'object') {
            $attr['class'] = $data::class;
        }

        $xml = new XmlService();

        if (is_array($data) || is_object($data)) {
            $xml->startElement($rootElementName, null, $attr);

            foreach ($data as $key => $value) {
                $this->createStructure($key, $value, $xml);
            }
        } else {
            $xml->startElement($rootElementName, $data, $attr);
        }

        $xml->closeElement();

        return $xml->get();
    }


    private function createStructure(int|string $key, mixed $value, XmlService $xml): void
    {
        $attr['type'] = gettype($value);

        if (is_numeric($key)) {
            $key = "_{$key}";
        }

        if(is_file($value) && config('app', 'response_file_auto_encode', true)){
            $value = base64_encode(file_get_contents($value));
        }

        if (is_string($value)) {
            $this->setTextBlock($value, $xml, $key, $attr);
        } elseif (is_bool($value) || is_numeric($value)) {
            $xml->addElement($key, (string)$value, $attr);
        } elseif ($value === null) {
            $value = 'NULL';
            $xml->addElement($key, $value, $attr);
        } elseif ((is_array($value) || is_object($value)) && !($value instanceof Closure)) {
            $this->setSuperBlock($value, $xml, $key, $attr);
        } elseif (($value instanceof Closure) || is_callable($value)) {
            $this->setSerializableBlock($value, $xml, $key, $attr);
        } else {
            $xml->addElement($key, null, [...$attr, 'error' => 'data type not supported']);
        }
    }

    private function setTextBlock(string $value, XmlService $xml, string|int $key, array $attr): void
    {
        if (strtotime($value)) {
            $format = 'Y-m-d H:i:s';
            $attr['type'] = 'date';
            $attr['class'] = DateTime::class;
            $attr['format'] = $format;
            $value = (new DateTime($value))->format($format);
        }

        $xml->addElement($key, $value, $attr, createIfTextNull: true);
    }

    private function setSuperBlock(mixed $value, XmlService $xml, int|string $key, array $attr): void
    {
        if ($attr['type'] === 'object') {
            $attr['class'] = $value::class;
        }

        $xml->startElement($key, null, $attr);

        foreach ($value as $k => $val) {
            $this->createStructure($k, $val, $xml);
        }

        $xml->closeElement();
    }

    private function setSerializableBlock(callable|Closure $value, XmlService $xml, int|string $key, array $attr): void
    {
        $attr['class'] = $value::class;
        $attr['serialized_by'] = SerializableClosure::class;
        $xml->addElement($key, serialize(new SerializableClosure($value)), $attr);
    }

    public function __toString(): string
    {
        return $this->xml;
    }
}