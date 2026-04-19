<?php
try {
    $database = new PDO('mysql:host=localhost;dbname=pfa_esen_2026;charset=utf8', 'root', '');
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
?>