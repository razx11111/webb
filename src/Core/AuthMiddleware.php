<?php

namespace App\Core;

/**
 * Handles basic authentication checks for routes.
 */
class AuthMiddleware {

    /**
     * Protects a route by checking if a user (User or Admin) is logged in.
     * If not, it redirects to the login page or returns a JSON error for API requests.
     */
    public static function requireLogin() {
        // First, ensure the session is active.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if a user ID exists in the session.
        if (!isset($_SESSION['user_id'])) {
            
            // Check if this is an API request
            $isApiRequest = strpos($_SERVER['REQUEST_URI'], '/api/') === 0;

            if ($isApiRequest) {
                // For API requests, we can't redirect. Send a JSON error with a 401 Unauthorized status.
                header('Content-Type: application/json');
                http_response_code(401); // 401 Unauthorized: The client must authenticate themselves to get the requested response.
                echo json_encode(['error' => 'Unauthorized. Please log in.']);
            } else {
                // For normal page requests, redirect the user to the login page.
                header("Location: /login");
            }
            
            // Stop script execution after sending the response.
            exit();
        }
    }
}
