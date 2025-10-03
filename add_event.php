<?php
session_start();
require_once "db.php"; // your PDO $pdo connection

// 1) Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize messages
$error = '';
$success = '';

// 5) Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_title = trim($_POST['event_title'] ?? '');
    $days = $_POST['days'] ?? [];

    if (!$event_title) {
        $error = "Event title is required.";
    } elseif (empty($days)) {
        $error = "You must specify at least one day.";
    } else {
        try {
            $pdo->beginTransaction();

            // Insert into event
            $stmt = $pdo->prepare("INSERT INTO event (title) VALUES (?)");
            $stmt->execute([$event_title]);
            $event_id = $pdo->lastInsertId();

            // Insert days
            $stmt_day = $pdo->prepare("
				INSERT INTO event_day (event_id, title, date, start_time, end_time)
				VALUES (?, ?, ?, ?, ?)
			");
			foreach ($days as $day) {
				$day_title = trim($day['title'] ?? "Day"); // optional title
				$day_date  = trim($day['date'] ?? '');
				$day_start = trim($day['start'] ?? '');
				$day_end   = trim($day['end'] ?? '');
				
				if ($day_date && $day_start && $day_end) {
					$stmt_day->execute([$event_id, $day_title, $day_date, $day_start, $day_end]);
				}
			}

            $pdo->commit();
            $success = "Event and its days have been successfully added!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add New Event</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: auto; }
        label { display: block; margin-top: 10px; }
        input[type=text], input[type=date], input[type=time], input[type=number] { padding: 5px; width: 200px; }
        .day-fields { margin-bottom: 15px; border: 1px solid #ccc; padding: 10px; }
    </style>
</head>
<body>
    <h1>Add New Event</h1>
    <?php if($error) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if($success) echo "<p style='color:green;'>$success</p>"; ?>

    <form method="post">
        <label>Event Title:
            <input type="text" name="event_title" required>
        </label>

        <label>Number of Days:
            <input type="number" id="num_days" min="1" value="1">
            <button type="button" onclick="generateDays()">Set Days</button>
        </label>

        <div id="days_container">
			<div class="day-fields">
				<label>Day 1 Title: <input type="text" name="days[0][title]" placeholder="Optional"></label>
				<label>Day 1 Date: <input type="date" name="days[0][date]" required></label>
				<label>Start Time: <input type="time" name="days[0][start]" required></label>
				<label>End Time: <input type="time" name="days[0][end]" required></label>
			</div>
		</div>

        <br>
        <button type="submit">Add Event</button>
    </form>

    <script>
        function generateDays() {
            const container = document.getElementById('days_container');
            const numDays = parseInt(document.getElementById('num_days').value);
            container.innerHTML = '';

            for (let i = 0; i < numDays; i++) {
                const div = document.createElement('div');
                div.className = 'day-fields';
                div.innerHTML = `
					<label>Day ${i+1} Title: <input type="text" name="days[${i}][title]" placeholder="Optional"></label>
					<label>Day ${i+1} Date: <input type="date" name="days[${i}][date]" required></label>
					<label>Start Time: <input type="time" name="days[${i}][start]" required></label>
					<label>End Time: <input type="time" name="days[${i}][end]" required></label>
				`;
                container.appendChild(div);
            }
        }
    </script>
</body>
</html>