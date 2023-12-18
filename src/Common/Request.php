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
        $this->get  = $_GET ?? [];
        $this->files = $_FILES ?? [];
        $this->server = $_SERVER ?? [];
        $this->request = $_REQUEST ?? [];
        $this->headers = headers_list() ?? [];
        $this->input = $this->setInput();
        $this->params = $this->setParams();
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

    private function setInput():mixed
    {
        return file_get_contents("php://input");
    }

    public function setParams(): object
    {
        $params = [
            'request_patch' => trim($this->server['PATH_INFO'] ?? '', '/'),
            'request_method' => trim(mb_strtoupper($this->server['REQUEST_METHOD'] ?? '')),
            'request_port' => (int)$this->server['SERVER_PORT'] ?? 0,
        ];

        return (object)$params;
    }
}