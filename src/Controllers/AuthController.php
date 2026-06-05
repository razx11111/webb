<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Admin;

class AuthController {
    
    // START SESSIONS in the constructor to ensure they are active
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // --- UI Methods (Return HTML) ---

    public function showLogin() {
        require_once __DIR__ . '/../../templates/pages/login.php';
    }

    public function showRegister() {
        require_once __DIR__ . '/../../templates/pages/register.php';
    }

    // --- Action Methods (Handle form submissions) ---

    public function register() {
        // This method is for when a user wants to create a new account
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // Get the data from the form
        // sanitize the input to prevent security issues
        $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $password = filter_input(INPUT_POST, 'password');
        

        // --- VALIDATION ---
        // user filled all the fields
        if (empty($username) || empty($email) || empty($password)) {
            // If any of the fields are empty, redirect the user back to the register page with an error
            header("Location: /register?error=" . urlencode('All fields are required.'));
            exit();
        }

        // I also need to make sure that the email is in the correct format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: /register?error=" . urlencode('Invalid email format.'));
            exit();
        }

        // Create a new User model to interact with the database
        $user_model = new User();

        // --- CHECK IF USER ALREADY EXISTS ---
        if ($user_model->findByEmail($email)) {
            // If the email is already registered, I show an error
            header("Location: /register?error=" . urlencode('This email is already registered.'));
            exit();
        }
        if ($user_model->findByUsername($username)) {
            // If the username is already taken, I show an error
            header("Location: /register?error=" . urlencode('This username is already taken.'));
            exit();
        }

        // --- HASH THE PASSWORD ---
        // password_hash() to securely hash the password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // --- CREATE THE USER ---
        $success = $user_model->create([
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash
        ]);

        // --- REDIRECT ---
        // redirect the user to the login page if the user was created successfully
        if ($success) {
            header("Location: /login?success=1");
            exit();
        } else {
            // If there was an error, I show a generic error message
            header("Location: /register?error=" . urlencode('An unexpected error occurred. Please try again.'));
            exit();
        }
    }

    public function login() {
        // This is the login method. It's called when the user tries to log in.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // Get the data from the login form
        $identifier = trim(filter_input(INPUT_POST, 'identifier', FILTER_SANITIZE_SPECIAL_CHARS));
        $password = $_POST['password'] ?? '';
        
        // var_dump($identifier, $password);

        // I need to make sure the user entered both the identifier and the password
        if (empty($identifier) || empty($password)) {
            // If one of them is missing, I redirect the user back to the login page with an error
            header("Location: /login?error=" . urlencode('Identifier and password are required.'));
            exit();
        }

        // --- TRY TO LOG IN AS ADMIN ---
        // Admins log in with their phone number.
        $admin_model = new Admin();
        $admin = $admin_model->findByPhone($identifier);

        // Check if phone number and the password is correct
        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Save the info in the session
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['role'] = 'admin';
            $_SESSION['name'] = $admin['nume'] . ' ' . $admin['prenume'];

            // Redirect the admin to the homepage
            header("Location: /");
            exit();
        }

        // --- TRY TO LOG IN AS USER ---
        // If the user is not an admin, check if they are a regular user.
        // Users log in with their email.
        $userModel = new User();
        $user = $userModel->findByEmail($identifier);

        // If email and password are correct
        if ($user && password_verify($password, $user['password_hash'])) {
            // save the user's info in the session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = 'user';
            $_SESSION['name'] = $user['username'];

            // redirect the user to the homepage
            header("Location: /");
            exit();
        }

        // --- FAILED LOGIN ---
        // If the user is neither an admin nor a user, or if the password was wrong,
        // 78,.redirect them back to the login page with an error message.
        header("Location: /login?error=" . urlencode('Invalid credentials.'));
        exit();
    }

    public function logout() {
        session_destroy();
        header("Location: /");
        exit();
    }

    /**
     * Redirects to a given URL with an error message.
     *
     * @param string $url The URL to redirect to.
     * @param string $message The error message to display.
     */
    private function redirectWithError(string $url, string $message) {
        header("Location: {$url}?error=" . urlencode($message));
        exit();
    }
}
