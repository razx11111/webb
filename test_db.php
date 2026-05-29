<?php
require_once __DIR__ . '/src/Core/Autoloader.php';
\App\Core\Autoloader::register();
if (file_exists(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
}

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT to_regclass('public.users')");
    $result = $stmt->fetchColumn();
    if ($result) {
        echo "Users table exists.\n";
        $stmt2 = $db->query("SELECT COUNT(*) FROM users");
        echo "Users count: " . $stmt2->fetchColumn() . "\n";
        $stmt3 = $db->query("SELECT COUNT(*) FROM admins");
        echo "Admins count: " . $stmt3->fetchColumn() . "\n";
    } else {
        echo "Users table DOES NOT EXIST.\n";
    }
} catch (\Exception $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
