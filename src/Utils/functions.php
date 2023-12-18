<?php

if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if (preg_match('~HTTP_~ui', $name)) {
                $key = str_replace('HTTP_', '', $name);
                $headers[$key] = $value;
            }
        }

        return $headers;
    }
}