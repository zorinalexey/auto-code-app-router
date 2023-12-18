<?php

namespace AutoCode\AppRouter\Utils;

enum RequestMethodEnum: string
{
    case GET = 'GET';
    case POST = 'POST';
    case VIEW = 'VIEW';
    case PROPFIND = 'PROPFIND';
    case UNLOCK = 'UNLOCK';
    case LOCK = 'LOCK';
    case PURGE = 'PURGE';
    case UNLINK = 'UNLINK';
    case LINK = 'LINK';
    case OPTIONS = 'OPTIONS';
    case COPY = 'COPY';
    case DELETE = 'DELETE';
    case PATCH = 'PATCH';
    case PUT = 'PUT';
}
