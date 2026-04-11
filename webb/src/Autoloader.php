<?php

namespace App;

class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            // Prefixul de namespace specific proiectului
            $prefix = 'App\\';

            // Directorul de bază pentru prefixul de namespace
            $base_dir = __DIR__ . '/';

            // Verificăm dacă clasa folosește prefixul de namespace
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                // Dacă nu, mergem la următorul autoloader înregistrat
                return;
            }

            // Obținem numele clasei relativ
            $relative_class = substr($class, $len);

            // Înlocuim prefixul de namespace cu directorul de bază, înlocuim
            // separatorii de namespace cu separatorii de directoare, adăugăm .php
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

            // Dacă fișierul există, îl includem
            if (file_exists($file)) {
                require $file;
            }
        });
    }
}
