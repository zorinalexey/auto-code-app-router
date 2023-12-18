<?php

namespace AutoCode\AppRouter\Utils;

use Closure;
use CloudCastle\Xml\Generator\XmlService;
use DateTime;
use Opis\Closure\SerializableClosure;

final readonly class ResponseXml
{
    private string $xml;
    private string $rootElementName;
    private bool $fileAutoEncode;
    private string $dateFormat;

    public function __construct(mixed $data)
    {
        $this->dateFormat = config('app', 'response_xml_date_format', 'Y-m-d H:i:s');
        $this->rootElementName = config('app', 'response_xml_root_element_name', 'response');
        $this->fileAutoEncode = config('app', 'response_file_auto_encode', true);
        $this->xml = $this->createSchema($data);
    }

    private function createSchema(mixed $data): string
    {
        $rootElementName = $this->rootElementName;
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

        if (is_string($value)) {
            $this->setTextBlock($value, $xml, $key, $attr);
        } elseif (is_bool($value) || is_numeric($value)) {
            $xml->addElement($key, (string)$value, $attr);
        } elseif ($value === null) {
            $xml->addElement($key, $value, $attr, createIfTextNull: true);
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
        if ($this->fileAutoEncode && is_file($value)) {
            $attr['type'] = 'file';
            $attr['encode'] = 'base64';
            $value = base64_encode(file_get_contents($value));
        }

        if (strtotime($value)) {
            $format = $this->dateFormat;
            $date = new DateTime($value);
            $attr['type'] = 'date';
            $attr['class'] = DateTime::class;
            $attr['format'] = $format;
            $attr['timezone'] = $date->getTimezone()->getName();
            $attr['offset'] = $date->getOffset();
            $xml->addElement('location', null, $date->getTimezone()->getLocation());

            $value = $date->format($format);
        }

        $xml->addElement($key, $value, $attr, createIfTextNull: true);
    }

    private function setSuperBlock(mixed $value, XmlService $xml, int|string $key, array $attr): void
    {
        if ($attr['type'] === 'object') {
            $attr['class'] = $value::class;
        }

        if ($value instanceof DateTime) {
            $format = $this->dateFormat;
            $attr['type'] = 'date';
            $attr['format'] = $format;
            $attr['timezone'] = $value->getTimezone()->getName();
            $attr['offset'] = $value->getOffset();
            $xml->addElement('location', null, $value->getTimezone()->getLocation());
            $value = $value->format($format);
            $xml->addElement($key, $value, $attr, createIfTextNull: true);
        } else {
            $xml->startElement($key, null, $attr);

            foreach ($value as $k => $val) {
                $this->createStructure($k, $val, $xml);
            }

            $xml->closeElement();
        }

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