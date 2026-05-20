<?php
// config/db.php – Connexion PDO sécurisée à la base SenFood

define('DB_HOST', '127.0.0.1:3307');
define('DB_NAME', 'mon_eshop');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die('Connexion échouée : ' . $e->getMessage());
}