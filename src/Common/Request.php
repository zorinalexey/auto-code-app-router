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
    public readonly string $input;

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
        $this->input = file_get_contents("php://input");
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