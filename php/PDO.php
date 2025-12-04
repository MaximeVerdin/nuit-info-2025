<?php
function getPDO() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO('pgsql:host=localhost;dbname=qcm_db', 'postgres', 'password');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $pdo = false;
        }
    }
    return $pdo;
}
?>
