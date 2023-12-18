<?php

namespace AutoCode\AppRouter\Routes;

use AutoCode\AppRouter\Abstractions\AbstractRoute;
use AutoCode\AppRouter\Utils\RequestMethodEnum;

final class Get extends AbstractRoute
{
    protected RequestMethodEnum $method = RequestMethodEnum::GET;
}