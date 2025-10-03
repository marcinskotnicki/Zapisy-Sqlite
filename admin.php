<?php
session_start();
require_once "db.php"; // your database connection
require_once "functions.php"; // helper functions like isAdminLoggedIn()

// redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// fetch current settings
$stmt = $pdo->query("SELECT * FROM settings ORDER BY id ASC");
$settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    foreach ($_POST['settings'] as $key => $value) {
        $update = $pdo->prepare("UPDATE settings SET value = ? WHERE key = ?");
        $update->execute([$value, $key]);
    }
    $msg = "Settings updated successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: auto; }
        .msg { background: #dfd; padding: 10px; margin: 10px 0; }
        form { margin-bottom: 20px; }
        label { display: block; margin-top: 10px; }
        input[type=text], input[type=email], input[type=password] { width: 100%; padding: 6px; }
    </style>
</head>
<body>
    <h1>Admin Panel</h1>

    <?php if (!empty($msg)): ?>
        <div class="msg"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <h2>Site Settings</h2>
    <form method="post">
        <?php foreach ($settings as $setting): ?>
            <label>
                <?= htmlspecialchars($setting['key']) ?><br>
                <input type="text" name="settings[<?= htmlspecialchars($setting['key']) ?>]" 
                       value="<?= htmlspecialchars($setting['value']) ?>">
            </label>
        <?php endforeach; ?>
        <br>
        <button type="submit" name="update_settings">Save Settings</button>
    </form>

    <h2>Events</h2>
    <p><a href="add_event.php">➕ Add New Event</a></p>

    <p><a href="logout.php">🚪 Log out</a></p>
</body>
</html>