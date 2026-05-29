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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($email) || empty($password)) {
            die("All fields are required.");
        }

        $userModel = new User();

        // Check for uniqueness
        if ($userModel->findByEmail($email)) {
            die("This email is already taken.");
        }
        if ($userModel->findByUsername($username)) {
            die("This username is already taken.");
        }

        // HASH THE PASSWORD! (Never store plain text)
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Save to DB
        $userModel->create([
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash
        ]);

        // Redirect to login
        header("Location: /login?success=1");
        exit();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $identifier = trim($_POST['identifier'] ?? ''); // can be email (user) or phone (admin)
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user'; // We add a dropdown or radio to select if they log in as user or admin

        if (empty($identifier) || empty($password)) {
            die("Incomplete fields.");
        }

        if ($role === 'admin') {
            $adminModel = new Admin();
            $admin = $adminModel->findByPhone($identifier);

            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Login successful for Admin
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['role'] = 'admin';
                $_SESSION['name'] = $admin['nume'] . ' ' . $admin['prenume'];
                
                header("Location: /");
                exit();
            }
        } else {
            $userModel = new User();
            $user = $userModel->findByEmail($identifier);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Login successful for User
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = 'user';
                $_SESSION['name'] = $user['username'];
                
                header("Location: /");
                exit();
            }
        }

        die("Incorrect credentials!");
    }

    public function logout() {
        session_destroy();
        header("Location: /");
        exit();
    }
}
