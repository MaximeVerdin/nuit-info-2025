<?php
require_once 'PDO.php';

$pdo = getPDO();
if ($pdo) {
    echo "Database connection successful!\n";
    // Test query to check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database: " . implode(', ', $tables) . "\n";
} else {
    echo "Database connection failed.\n";
}
?>
