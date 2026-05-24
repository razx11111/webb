<?php

namespace App\Core;

class Router
{
    private $routes = []; // Internal registry mapping URLs to specific Controller actions

    public function get($path, $controller, $action)
    {
        // Storing the route under the 'GET' category for later lookup
        $this->routes['GET'][$path] = [
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function post($path, $controller, $action)
    {
        // Storing the route under the 'POST' category
        $this->routes['POST'][$path] = [
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function run()
    {
        // $_SERVER is a global array; 'REQUEST_METHOD' tells us if it's GET, POST, etc.
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Extracting only the path (e.g., /earthquakes) and ignoring query strings (?id=1)
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Checking if the requested method and path exist in our $routes registry
        if (isset($this->routes[$method][$uri])) {
            $controllerName = $this->routes[$method][$uri]['controller'];
            $actionName = $this->routes[$method][$uri]['action'];

            // Building the full string name of the class including its Namespace
            $fullControllerPath = "\\App\\Controllers\\" . $controllerName;

            // class_exists triggers the Autoloader to see if the file actually exists
            if (class_exists($fullControllerPath)) {
                //creating an object using a string variable name
                $controller = new $fullControllerPath();
                
                // Checking if the specific method exists inside that Controller object
                if (method_exists($controller, $actionName)) {
                    // Variable Method Call: executes the method named in the $actionName string
                    $controller->$actionName();
                    return;
                }
            }
        }

        // If no match was found, trigger the failure sequence
        $this->abort();
    }

    private function abort($code = 404)
    {
        // Telling the browser to interpret this response as a specific error status
        http_response_code($code);
        echo "Page not found (404).";
        die(); // Halts all script execution immediately
    }
}
