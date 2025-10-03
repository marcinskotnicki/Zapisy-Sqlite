<?php
// This migration adds a "status" column to games and start/end times to event_day
// Only run if the columns do not already exist

try {
    // 1) Add 'status' column to 'games' table
    $columns = $pdo->query("PRAGMA table_info(games)")->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!in_array('status', $columns)) {
        $pdo->exec("ALTER TABLE games ADD COLUMN status INTEGER DEFAULT 2");
        echo "Added 'status' column to 'games'.<br>";
    } else {
        echo "'status' column already exists in 'games'.<br>";
    }

    // 2) Add 'start_time' and 'end_time' columns to 'event_day'
    $columnsDay = $pdo->query("PRAGMA table_info(event_day)")->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!in_array('start_time', $columnsDay)) {
        $pdo->exec("ALTER TABLE event_day ADD COLUMN start_time TEXT NOT NULL DEFAULT '12:00'");
        echo "Added 'start_time' column to 'event_day'.<br>";
    } else {
        echo "'start_time' column already exists in 'event_day'.<br>";
    }

    if (!in_array('end_time', $columnsDay)) {
        $pdo->exec("ALTER TABLE event_day ADD COLUMN end_time TEXT NOT NULL DEFAULT '18:00'");
        echo "Added 'end_time' column to 'event_day'.<br>";
    } else {
        echo "'end_time' column already exists in 'event_day'.<br>";
    }

} catch (PDOException $e) {
    die("Migration error: " . $e->getMessage());
}