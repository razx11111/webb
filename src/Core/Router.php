<?php

namespace App\Core;

/**
 * A basic router that maps URLs to controller actions.
 * It handles GET and POST requests and dispatches them to the appropriate controller.
 */
class Router
{
    private $routes = []; // Internal registry mapping URLs to specific Controller actions

    /**
     * Registers a GET route.
     * @param string $path The URL path to match.
     * @param string $controller The name of the controller class.
     * @param string $action The name of the method to call in the controller.
     */
    public function get($path, $controller, $action)
    {
        // Storing the route under the 'GET' category for later lookup
        $this->routes['GET'][$path] = [
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Registers a POST route.
     * @param string $path The URL path to match.
     * @param string $controller The name of the controller class.
     * @param string $action The name of the method to call in the controller.
     */
    public function post($path, $controller, $action)
    {
        // Storing the route under the 'POST' category
        $this->routes['POST'][$path] = [
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Executes the router.
     * It matches the current request's method and URI to a registered route and calls the corresponding controller action.
     * If no route is found, it triggers a 404 error.
     */
    public function run()
    {
        // Get the request method (GET, POST, etc.) and the requested URI.
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Check if a route exists for the current method and URI.
        if (isset($this->routes[$method][$uri])) {
            $route = $this->routes[$method][$uri];
            $controllerName = $route['controller'];
            $actionName = $route['action'];

            // Construct the full class name, including the namespace (e.g., "App\Controllers\HomeController").
            $fullControllerPath = "App\\Controllers\\" . $controllerName;

            // `class_exists` will trigger the Autoloader to find and include the controller file.
            if (class_exists($fullControllerPath)) {
                
                // Create a new instance of the controller.
                $controller = new $fullControllerPath();
                
                // Check if the action (method) exists within the controller.
                if (method_exists($controller, $actionName)) {
                    // Call the method on the controller to dispatch the request.
                    $controller->$actionName();
                    return; // Stop the router from continuing.
                }
            }
        }

        // If no route was matched, trigger a 404 error.
        $this->abort();
    }

    /**
     * Handles routing failures by showing a generic error page.
     * @param int $code The HTTP status code to send. Defaults to 404.
     */
    private function abort($code = 404)
    {
        // Set the HTTP response code (e.g., 404 Not Found).
        http_response_code($code);

        // Prepare variables for the error page.
        $errorCode = $code;
        if ($code == 404) {
            $errorMessage = 'The page you are looking for could not be found.';
        } else {
            $errorMessage = 'An unexpected error occurred.';
        }
        
        // Load the error template for a cleaner error response.
        require_once dirname(__DIR__) . '/../templates/pages/error.php';

        // Exit to ensure no further script execution.
        exit();
    }
}
