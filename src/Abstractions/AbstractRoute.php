<?php

namespace AutoCode\AppRouter\Abstractions;

use AutoCode\AppRouter\Common\Request;
use AutoCode\AppRouter\Interfaces\SetRoutesInterface;
use AutoCode\AppRouter\Routes\Group;
use AutoCode\AppRouter\Utils\RequestMethodEnum;
use Closure;

abstract class AbstractRoute implements SetRoutesInterface
{
    private string $name = '';
    private string $url = '';
    private string $prefix = '';
    private array $middlewares = [];
    private array $validators = [];
    private array $ports = [

    ];
    private array $methods = [];
    private Closure|null $action = null;
    private Group|null $parent = null;
    private array $blackListIp = [];
    private array $whiteListIp = [];

    final public function getName(): string
    {
        $name = $this->name;

        if ($parent = $this->getParent()) {
            $name = $parent->getName() . $this->name;
        }

        return trim($name);
    }

    final public function getParent(): Group|null
    {
        return $this->parent;
    }

    final public function setParent(Group $parent): void
    {
        $this->parent = $parent;
    }

    final public function name(string $name): SetRoutesInterface
    {
        $this->name = trim($name);

        return $this;
    }

    final public function url(string $url): SetRoutesInterface
    {
        $this->url = trim($url, '/');

        return $this;
    }

    final public function prefix(string $prefix): SetRoutesInterface
    {
        $this->prefix = trim($prefix, '/');

        return $this;
    }

    final public function middleware(string|Middleware|array $middleware): SetRoutesInterface
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    final public function validator(string|Validator|array $validator): SetRoutesInterface
    {
        $this->validators[] = $validator;

        return $this;
    }

    final public function port(array|int $port): SetRoutesInterface
    {
        if (is_array($port)) {
            $this->ports = [...$port, ...$this->ports];
        }

        if (is_int($port) && !in_array($port, $this->ports, true)) {
            $this->ports[] = $port;
        }

        return $this;
    }

    final public function method(array|RequestMethodEnum $methods): SetRoutesInterface
    {
        if (is_array($methods)) {
            foreach ($methods as $method) {
                $this->method($method);
            }
        }

        if ($methods instanceof RequestMethodEnum) {
            $this->methods[] = $methods;
        }

        return $this;
    }

    final public function getAction(): Closure|null
    {
        return $this->action;
    }

    final public function action(Closure|null $action): SetRoutesInterface
    {
        $this->action = $action;

        return $this;
    }

    final public function getInfo(): array
    {
        $vars = [];
        $url = $this->getUrl();
        $request = Request::getInstance();
        $methods = $this->getMethods();

        if (preg_match_all('~{(?<vars>\w+)}~u', $url, $matchesParams) && preg_match_all('~{(?<vars>\w+)}~u', $request->queryString ?? '', $matchesParams)) {
            $vars = $matchesParams['vars'];
        }

        $requests = [];

        foreach ($methods as $method) {
            if (isset($request->{$method->value})) {
                $requests[] = $request->{$method->value};
            }
        }

        return [
            'methods' => $methods,
            'ports' => $this->getPorts(),
            'middlewares' => $this->getMiddlewares(),
            'validates' => $this->getValidators(),
            'url' => $url,
            'pattern' => '~' . preg_replace('~{(\w+)}~u', '(\w+)', $url) . '~u',
            'varNames' => $vars,
            'requests' => $requests,
            'request' => $request,
            'ip' => [
                'whiteList' => $this->getWhiteListIp(),
                'blackList' => $this->getBlackListIp(),
            ],
        ];
    }

    final public function getUrl(): string
    {
        $url = $this->url;

        if ($parent = $this->getParent()) {
            $url = $parent->getUrl() . '/' . $this->getPrefix() . '/' . $this->url;
        }

        return trim($url, '/');
    }

    final public function getPrefix(): string
    {
        $prefix = $this->prefix;

        if ($parent = $this->getParent()) {
            $prefix = $parent->getPrefix() . '/' . $this->prefix;
        }

        return trim($prefix, '/');
    }

    final public function getMethods(): array
    {
        if ($parent = $this->getParent()) {
            return [
                ...$parent->getMethods(),
                ...$this->methods
            ];
        }

        return $this->methods;
    }

    final public function getPorts(): array
    {
        $ports = $this->ports;

        if ($parent = $this->getParent()) {
            $ports = [
                ...$parent->getPorts(),
                ...$this->ports
            ];
        }

        return $ports;
    }

    final public function getMiddlewares(): array
    {
        $middlewares = $this->middlewares;

        if ($parent = $this->getParent()) {
            $middlewares = [
                ...$parent->getMiddlewares(),
                ...$this->middlewares
            ];
        }

        return $middlewares;
    }

    final public function getValidators(): array
    {
        $validators = $this->validators;

        if ($parent = $this->getParent()) {
            $validators = [
                ...$parent->getValidators(),
                ...$this->validators
            ];
        }

        return $validators;
    }

    final public function getWhiteListIp(): array
    {
        if ($parent = $this->getParent()) {
            return [
                ...$parent->getWhiteListIp(),
                ...$this->whiteListIp
            ];
        }

        return $this->whiteListIp;
    }

    final public function getBlackListIp(): array
    {
        if ($parent = $this->getParent()) {
            return [
                ...$parent->getBlackListIp(),
                ...$this->blackListIp
            ];
        }

        return $this->blackListIp;
    }

    final public function blackListIp(array $list): static
    {
        $this->blackListIp = $list;

        return $this;
    }

    final public function whiteListIp(array $list): static
    {
        $this->whiteListIp = $list;

        return $this;
    }
}