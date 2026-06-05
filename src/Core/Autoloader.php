<?php

namespace App\Core;

/**
 * Implements a PSR-4 autoloader for the project.
 * This class automatically includes class files when they are first used,
 */
class Autoloader
{
    /**
     * Registers the autoloader function. This should be called once, at the start of the application.
     */
    public static function register()
    {
        spl_autoload_register(function ($class) {
            // Project-specific namespace prefix. We only want to autoload our own classes.
            $prefix = 'App\\';

            // Base directory for the project's source files. `__DIR__` is the current directory (`/src/Core`).
            // `dirname(__DIR__)` goes one level up to `/src`.
            $base_dir = dirname(__DIR__) . '/';

            // Does the class use our project's namespace prefix?
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                // No, it's a class from another library
                return;
            }

            // Get the part of the class name *after* the prefix.
            // e.g., for `App\Controllers\HomeController`, this will be `Controllers\HomeController`.
            $relative_class = substr($class, $len);

            // Replace namespace separators `\` with directory separators `/` and add the .php extension.
            // This maps the namespace to a file path.
            // e.g., `Controllers\HomeController` becomes `src/Controllers/HomeController.php`.
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

            // If the mapped file exists, include it.
            if (file_exists($file)) {
                require $file;
            }
        });
    }
}
