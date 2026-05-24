<?php

namespace App\Core;

class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            // Namespace prefix specific to the project
            $prefix = 'App\\';

            // Base directory for the prefix
            $base_dir = dirname(__DIR__) . '/';

            // We check if the class uses the namespace prefix
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                // If not, we go to the next autoloader
                return;
            }

            // Get the relative class name
            $relative_class = substr($class, $len);

            // Replace the namespace prefix with the base directory, replace
            // namespace separators with directory separators, add .php
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

            // If the file exists, include it
            if (file_exists($file)) {
                require $file;
            }
        });
    }
}
