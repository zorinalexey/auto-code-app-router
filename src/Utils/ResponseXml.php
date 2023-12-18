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
        $rootElementName = 'response';
        $attr['type'] = gettype($data);

        if ($attr['type'] === 'object') {
            $attr['class'] = $data::class;
        }

        $xml = new XmlService();

        if (is_array($data) || is_object($data)) {
            $xml->startElement($rootElementName, null, $attr);

            foreach ($data as $key => $value) {
                $this->addElement($key, $value, $xml);
            }
        } else {
            $xml->startElement($rootElementName, $data, $attr);
        }

        $xml->closeElement();

        return $xml->get();
    }


    private function addElement(int|string $key, mixed $value, XmlService $xml): void
    {
        $attr['type'] = gettype($value);

        if (is_numeric($key)) {
            $key = "_{$key}";
        }

        if (is_string($value)) {
            $this->setTextXmlBlock($value, $xml, $key, $attr);
        } elseif (is_bool($value) || is_numeric($value)) {
            $xml->addElement($key, (string)$value, $attr);
        } elseif ($value === null) {
            $value = 'NULL';
            $xml->addElement($key, $value, $attr);
        } elseif ((is_array($value) || is_object($value)) && !($value instanceof Closure)) {
            $this->setSuperXmlBlock($value, $xml, $key, $attr);
        } elseif (($value instanceof Closure) || is_callable($value)) {
            $this->setSerializableXmlBlock($value, $xml, $key, $attr);
        } else {
            $xml->addElement($key, null, [...$attr, 'error' => 'data type not supported']);
        }
    }

    private function setTextXmlBlock(string $value, XmlService $xml, string|int $key, array $attr): void
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

    private function setSuperXmlBlock(mixed $value, XmlService $xml, int|string $key, array $attr): void
    {
        if ($attr['type'] === 'object') {
            $attr['class'] = $value::class;
        }

        $xml->startElement($key, null, $attr);

        foreach ($value as $k => $val) {
            $this->addElement($k, $val, $xml);
        }

        $xml->closeElement();
    }

    private function setSerializableXmlBlock(callable|Closure $value, XmlService $xml, int|string $key, array $attr): void
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