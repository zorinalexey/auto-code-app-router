<?php

namespace AutoCode\AppRouter\Interfaces;

use AutoCode\AppRouter\Abstractions\Middleware;
use AutoCode\AppRouter\Abstractions\Validator;
use Closure;

interface SetRoutesInterface
{
    public function action(Closure|null $action): self;

    public function method(array $methods): self;

    public function port(array|int $port): self;

    public function validator(string|Validator|array $validator): self;

    public function middleware(string|Middleware|array $middleware): self;

    public function prefix(string $prefix): self;

    public function name(string $name): self;

    public function url(string $url): self;
}