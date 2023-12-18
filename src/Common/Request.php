<?php

namespace AutoCode\AppRouter\Common;

final class Request
{

    private static self|null $instance = null;
    public readonly array $session;
    public readonly array $cookie;
    public readonly array $post;
    public readonly array $get;
    public readonly array $files;
    public readonly array $server;
    public readonly array $request;
    public readonly array $headers;
    public readonly mixed $input;
    public readonly object $params;

    private function __construct()
    {

        $this->session = $_SESSION ?? [];
        $this->cookie = $_COOKIE ?? [];
        $this->post = $_POST ?? [];
        $this->get = $_GET ?? [];
        $this->files = $_FILES ?? [];
        $this->server = $_SERVER ?? [];
        $this->request = $_REQUEST ?? [];
        $this->headers = $this->setHeaders();
        $this->input = $this->setInput();
        $this->params = $this->setParams();
    }

    private function setHeaders(): array
    {
        $headers = [];

        foreach ($this->server as $name => $value) {
            $name = mb_strtoupper($name);

            if (preg_match('~^(HTTP_(?<key>\w+))$~ui', $name, $matches)) {
                $headers[$this->getHeaderName($matches)] = $value;
            }
        }

        return $headers;
    }

    private function getHeaderName(array $matches): string
    {
        $name = '';
        $chunks = explode('_', $matches['key']);

        foreach ($chunks as $chunk) {
            $name .= ucfirst(mb_strtolower($chunk)) . '-';
        }

        return trim($name, '-');
    }

    private function setInput(): mixed
    {
        return file_get_contents("php://input");
    }

    public function setParams(): object
    {
        $params = [
            'request_patch' => trim($this->server['PATH_INFO'] ?? '', '/'),
            'request_method' => mb_strtoupper(trim($this->server['REQUEST_METHOD'] ?? '')),
            'request_port' => (int)($this->server['SERVER_PORT'] ?? 0),
        ];

        return (object)$params;
    }

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __clone()
    {

    }
}