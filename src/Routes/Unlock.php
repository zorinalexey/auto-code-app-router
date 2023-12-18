<?php

namespace AutoCode\AppRouter\Routes;

use AutoCode\AppRouter\Abstractions\AbstractRoute;
use AutoCode\AppRouter\Utils\RequestMethodEnum;

final class Unlock extends AbstractRoute
{
    protected RequestMethodEnum $method = RequestMethodEnum::UNLOCK;
}