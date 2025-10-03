<?php

$dbFile = __DIR__ . '/data/database.sqlite';

if (!is_dir(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data');
}

if (file_exists($dbFile)) {
    echo "Database already exists. Delete it to reinstall.";
    exit;
}

try {
	$pdo = new PDO('sqlite:' . $dbFile);
	$pdo->exec("PRAGMA foreign_keys = ON;");
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$pdo->exec("
		CREATE TABLE event (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			title TEXT NOT NULL
		);
		
		CREATE TABLE event_day (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			event_id INTEGER,
			title TEXT,
			date TEXT NOT NULL,
			start_time TEXT NOT NULL,
			end_time TEXT NOT NULL,
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
			table_id INTEGER,
			status INTEGER DEFAULT 2, -- 2=active, 1=deleted visible, 0=deleted invisible
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
			email TEXT NOT NULL,
			password TEXT NOT NULL -- hashed with password_hash()
		);
		
		CREATE TABLE settings (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			key TEXT KEY,
			value TEXT NOT NULL
		);
		
		CREATE TABLE pending_action (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			target_table TEXT NOT NULL,    -- 'games' or 'signups'
			target_id INTEGER NOT NULL,
			action_type TEXT NOT NULL,     -- 'edit'|'delete'
			proposer_email TEXT,           -- email to notify (if present)
			token TEXT NOT NULL,           -- one-time token for confirmation
			token_expires INTEGER NOT NULL, -- unix timestamp
			payload TEXT,                  -- JSON of proposed changes (for edit)
			created_at INTEGER DEFAULT (strftime('%s','now'))
		);
		
		CREATE TABLE email_log (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			to_email TEXT,
			subject TEXT,
			body TEXT,
			sent_at INTEGER DEFAULT (strftime('%s','now')),
			status TEXT
		)
	");

	$defaults = include __DIR__ . "/settings.php";

	 // Insert defaults if missing
	 $stmt = $pdo->prepare("INSERT OR IGNORE INTO settings (key, value) VALUES (:key, :value)");
	 foreach ($defaults as $key => $value) {
		$stmt->execute([':key' => $key, ':value' => $value]);
	}
	
	 // Create default admin if none exists
    $check = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    if ($check == 0) {
        $stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([
            $defaults['default_admin_user'],
            $defaults['default_admin_email'],
            password_hash($defaults['default_admin_pass'], PASSWORD_DEFAULT)
        ]);
        echo "✅ Default admin created (username: {$defaults['default_admin_user']}, password: {$defaults['default_admin_pass']})<br>";
    }
	
	$pdo->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('system_version', '1.0');");
	
}catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
echo "Installation complete. Database created.";