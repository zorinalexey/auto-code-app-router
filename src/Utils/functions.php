<?php

if (!function_exists('getallheaders'))
{
    function getallheaders()
    {
        $headers = [];

        foreach ($_SERVER as $name => $value)
        {
            if (str_starts_with(mb_strtoupper($name), 'HTTP_'))
            {
                $key = str_replace('HTTP_', '', $name);
                $headers[$key] = $value;
            }
        }

        return $headers;
    }
}