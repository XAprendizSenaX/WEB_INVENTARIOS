<?php 

$host = 'db.jryuieelkfclwwoatnlk.supabase.co';
$db = 'postgres';
$user = 'postgres';
$password = 'NCSdb2025@_';
$port = '5432';
$charset = 'utf8mb4';

$dsn = "pgsql:host=$host;port=$port;dbname=$db";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO ($dsn, $user, $password, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int) $e->getCode());
}

?>