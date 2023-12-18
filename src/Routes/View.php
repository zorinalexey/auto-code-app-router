<?php

namespace AutoCode\AppRouter\Routes;

use AutoCode\AppRouter\Abstractions\AbstractRoute;
use AutoCode\AppRouter\Utils\RequestMethodEnum;

final class View extends AbstractRoute
{
    protected RequestMethodEnum $method = RequestMethodEnum::VIEW;
}