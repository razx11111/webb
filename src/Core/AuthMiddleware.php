<?php

namespace App\Core;

class AuthMiddleware {
    /**
     * Protejează rutele: Verifică dacă utilizatorul (User sau Admin) este logat.
     * Dacă nu este, îl trimite la pagina de login sau îi dă eroare pe API.
     */
    public static function requireLogin() {
        // 1. Ne asigurăm că sesiunea este pornită
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Verificăm dacă există o sesiune activă (dacă s-a logat cineva)
        if (!isset($_SESSION['user_id'])) {
            
            // 3. Dacă e o cerere către API (AJAX din JS), nu putem face redirect.
            // Trebuie să dăm un răspuns JSON cu cod 401 (Unauthorized)
            if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['error' => 'Neautorizat. Te rugăm să te loghezi.']);
                exit();
            }

            // 4. Dacă e o cerere normală de pagină HTML, îl aruncăm afară (redirect)
            header("Location: /login");
            exit();
        }
    }
}
