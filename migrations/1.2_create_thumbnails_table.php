<?php
// Migration to create 'thumbnails' table and insert a default thumbnail

try {
    // Check if table already exists
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='thumbnails'")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('thumbnails', $tables)) {
        echo "'thumbnails' table already exists.<br>";
    } else {
        $pdo->exec("
            CREATE TABLE thumbnails (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                thumbnail_url TEXT NOT NULL,
                thumbnail_name TEXT NOT NULL
            )
        ");
        echo "Created 'thumbnails' table.<br>";

        // Insert default thumbnail
        $stmt = $pdo->prepare("INSERT INTO thumbnails (thumbnail_url, thumbnail_name) VALUES (?, ?)");
        $stmt->execute(['/thumbnails/default.jpg', 'default']);
        echo "Inserted default thumbnail.<br>";
    }
} catch (PDOException $e) {
    die("Migration error: " . $e->getMessage());
}