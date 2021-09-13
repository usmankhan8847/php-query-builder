<?php

namespace App;

class Config
{
    private static function configData()
    {
        return [
            'host' => "localhost",
            'user' => "root",
            'password' => "",
            'database' => "demo",
        ];
    }

    public static function get($key)
    {
        return self::configData()[$key];
    }
}
