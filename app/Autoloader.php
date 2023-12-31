<?php

namespace App;

class Autoloader
{
    public static function register($appRoot): void {
        spl_autoload_register(function ($class) use($appRoot): bool|string {


            $file = str_replace("\\", DIRECTORY_SEPARATOR, $class).".php";

            $file = preg_replace('#^App#', $appRoot, $file );

            if (file_exists($file)) {
                require $file;
                return true;
            }

            return false;

        });
    }



}