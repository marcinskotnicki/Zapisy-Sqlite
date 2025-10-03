<?php
session_start();
require_once "db.php"; // PDO $pdo connection

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Config
$repo = "marcinskotnicki/Zapisy-Sqlite";
$branch = "main";
$zipUrl = "https://github.com/$repo/archive/refs/heads/$branch.zip";
$tmpZip = __DIR__ . '/update_tmp.zip';
$extractPath = __DIR__ . '/update_tmp/';
$backupDir = __DIR__ . '/backup/backup_' . date('Ymd_His');

function rrmdir($dir) {
    if (!is_dir($dir)) return;
    $items = array_diff(scandir($dir), ['.','..']);
    foreach ($items as $item) {
        $path = "$dir/$item";
        is_dir($path) ? rrmdir($path) : unlink($path);
    }
    rmdir($dir);
}

// Step 1: Backup current installation
mkdir($backupDir);
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);
foreach ($iterator as $file) {
    $path = $file->getPathname();

    // Skip backup folder and data folder
    if (str_starts_with($path, $backupDir) || strpos($path, '/data/') !== false || strpos($path, '/backup/') !== false || strpos($path, '/backup_') !== false) {
        continue;
    }

    // Determine destination path
    $dest = $backupDir . '/' . $iterator->getSubPathName();

    if ($file->isDir()) {
        if (!is_dir($dest)) mkdir($dest, 0777, true);
    } else {
        copy($path, $dest);
    }
}
echo "Backup created at $backupDir<br>";

// Step 2: Download ZIP
echo "Downloading latest version...<br>";
file_put_contents($tmpZip, fopen($zipUrl, 'r'));

// Step 3: Extract ZIP
$zip = new ZipArchive;
if ($zip->open($tmpZip) === TRUE) {
    $zip->extractTo($extractPath);
    $zip->close();
    echo "Extracted files.<br>";
} else {
    die("Failed to open ZIP file.");
}

// Step 4: Copy files over
$sourceDir = $extractPath . 'Zapisy-Sqlite-main/';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
foreach ($it as $file) {
    $relPath = substr($file, strlen($sourceDir));
    $dest = __DIR__ . '/' . $relPath;
    if ($file->isDir()) {
        if (!is_dir($dest)) mkdir($dest, 0777, true);
    } else {
        copy($file, $dest);
    }
}
echo "Files updated.<br>";

// Step 5: Cleanup temp files
unlink($tmpZip);
rrmdir($extractPath);
echo "Temporary files cleaned up.<br>";

// Step 6: Database migrations
$currentVersion = $pdo->query("SELECT value FROM settings WHERE key='system_version'")->fetchColumn() ?: '1.0';
$migrationsDir = __DIR__ . '/migrations/';
echo "current database version: ".$currentVersion."<br>";
$newVersion=$currentVersion;
$migrations = glob($migrationsDir . '*.php');
sort($migrations, SORT_NATURAL);

foreach ($migrations as $migration) {
    preg_match('/(\d+\.\d+)/', basename($migration), $matches);
    $version = $matches[1];
    if (version_compare($version, $currentVersion, '>')) {
        echo "Applying migration $version ...<br>";
        include $migration;
        $pdo->prepare("UPDATE settings SET value=? WHERE key='system_version'")->execute([$version]);
        echo "Migration $version applied.<br>";
		$newVersion=$version;
    }
}

echo "<b>Update complete! Current version: $currentVersion</b>";