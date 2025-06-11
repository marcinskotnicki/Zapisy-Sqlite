<?php

$dbFile = __DIR__ . '/data/database.sqlite';

if (!is_dir(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data');
}

if (file_exists($dbFile)) {
    echo "Database already exists. Delete it to reinstall.";
    exit;
}

$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Example: Create a 'games' table
$pdo->exec("
	CREATE TABLE event (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL
	);
	
	CREATE TABLE event_day (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		event_id INTEGER,
        title TEXT,
		FOREIGN KEY (event_id) REFERENCES event(id)
	);

    CREATE TABLE game_table (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
		day_id INTEGER,
		FOREIGN KEY (day_id) REFERENCES event_day(id)
    );

    CREATE TABLE games (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
		table_id INTEGER
        title TEXT NOT NULL,
		weight REAL,
		rating REAL,
		minplayers INTEGER,
		maxplayers INTEGER,
		playtime INTEGER,
		explanation INTEGER,
		link TEXT,
		image TEXT,
        proposer TEXT NOT NULL,
		proposer_email TEXT,
		proposer_ip TEXT,
        start_time TEXT,
        FOREIGN KEY (table_id) REFERENCES game_table(id)
    );

    CREATE TABLE signups (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        game_id INTEGER,
        name TEXT NOT NULL,
        email TEXT,
		rules INTEGER,
		comment TEXT,
		user_id INTEGER,
		reserve INTEGER,
        FOREIGN KEY (game_id) REFERENCES games(id)
    );

    CREATE TABLE conversation (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        game_id INTEGER,
        name TEXT NOT NULL,
		comment TEXT,
		user_id INTEGER,
        FOREIGN KEY (game_id) REFERENCES games(id)
    );
	
	CREATE TABLE admins (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		username TEXT UNIQUE NOT NULL,
		password TEXT NOT NULL -- hashed with password_hash()
	);
");

echo "Installation complete. Database created.";