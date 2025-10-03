<?php
function getPDO() {
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO('sqlite:' . __DIR__ . '/data/database.sqlite');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo;
}