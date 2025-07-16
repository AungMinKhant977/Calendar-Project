<?php

include "connection.php";

$successMsg = ''; 
$errorMsg = '';   
$eventsFromDB = []; 

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['action'] ?? '') === "add") {
    $course = trim($_POST["course_name"] ?? "");
    $instructor = trim($_POST["instructor_name"] ?? "");
    $start = $_POST["start_date"] ?? '';
    $end = $_POST["end_date"] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';

    if ($course && $instructor && $start && $end && $startTime && $endTime) {
        $stmt = $conn->prepare(
            "INSERT INTO appointments (course_name, instructor_name, start_date, end_date, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssss", $course, $instructor, $start, $end, $startTime, $endTime);

        if ($stmt->execute()) {
            header("Location: " . $_SERVER["PHP_SELF"] . "?success=1"); 
        } else {
            header("Location: " . $_SERVER["PHP_SELF"] . "?error=1");  
        }
        $stmt->close(); 
        exit; 
    } else {
        header("Location: " . $_SERVER["PHP_SELF"] . "?error=1");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? '') == 'edit') {
    $id = $_POST["event_id"] ?? null;
    $course = trim($_POST['course_name'] ?? '');
    $instructor = trim($_POST['instructor_name'] ?? '');
    $start = $_POST['start_date'] ?? '';
    $end = $_POST['end_date'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';

    if ($id && $course && $instructor && $start && $end && $startTime && $endTime) {
        $stmt = $conn->prepare(
            "UPDATE appointments SET course_name = ?, instructor_name = ?, start_date = ?, end_date = ?, start_time = ?, end_time = ? WHERE id = ?"
        );
        $stmt->bind_param("ssssssi", $course, $instructor, $start, $end, $startTime, $endTime, $id);

        if ($stmt->execute()) {
            header("Location: " . $_SERVER["PHP_SELF"] . "?success=2");
        } else {
            header("Location: " . $_SERVER["PHP_SELF"] . "?error=2");
        }
        $stmt->close();
        exit;
    } else {
        header("Location: " . $_SERVER["PHP_SELF"] . "?error=2");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? '') === "delete") {
    $id = $_POST['event_id'] ?? null;

    if ($id) {
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ? ");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header("Location: " . $_SERVER["PHP_SELF"] . "?success=3");
        } else {
            header("Location: " . $_SERVER["PHP_SELF"] . "?error=3");
        }
        $stmt->close();
        exit;
    } else {
        header("Location: " . $_SERVER["PHP_SELF"] . "?error=3");
        exit;
    }
}

if (isset($_GET["success"])) {
    switch ($_GET["success"]) {
        case '1':
            $successMsg = "✅ Appointment added successfully";
            break;
        case '2':
            $successMsg = "✅ Appointment updated successfully";
            break;
        case '3':
            $successMsg = "🗑️ Appointment deleted successfully";
            break;
        default:
            $successMsg = '';
            break;
    }
}

if (isset($_GET["error"])) {
    $errorMsg = '❗ Error occurred. Please check your input.';
}


$result = $conn->query("SELECT * FROM appointments ORDER BY start_date, start_time"); 

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $start = new DateTime($row["start_date"]);
        $end = new DateTime($row['end_date']);


        while ($start <= $end) {
            $eventsFromDB[] = [
                'id' => $row['id'],
                'title' => "{$row['course_name']} - {$row['instructor_name']}",
                'date' => $start->format('Y-m-d'),          
                'start' => $row["start_date"],              
                'end' => $row["end_date"],                  
                'start_time' => substr($row["start_time"], 0, 5), 
                'end_time' => substr($row["end_time"], 0, 5)     
            ];
            $start->modify('+1 day'); 
        }
    }
}

$conn->close(); 
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Course Calendar</title>

    <meta name="description" content="My Own Calendar Project">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style.css" />
</head>

<body>

    <header>
        <h1>📅 Course Calendar <br> My Calendar Project</h1>
    </header>

    <div id="messageContainer">
        <?php if (!empty($successMsg)) { echo '<p class="alert success">' . $successMsg . '</p>'; } ?>
        <?php if (!empty($errorMsg)) { echo '<p class="alert error">' . $errorMsg . '</p>'; } ?>
    </div>

    <div class="clock-container">
        <div id="clock"></div>
    </div>

    <div class="calendar">
        <div class="nav-btn-container">
            <button class="nav-btn prev-month">⏮️</button> <h2 id="monthYear" style="margin:0 "></h2>
            <button class="nav-btn next-month">⏭️</button> </div>

        <div class="calendar-grid" id="calendar"></div>
    </div>

    <div class="modal" id="eventModal">
        <div class="modal-content">
            <span class="close-button">&times;</span> <div id="eventSelectorWrapper">
                <label for="eventSelector">
                    <strong>Select Event:</strong>
                </label>
                <select id="eventSelector">
                    <option value="" disabled selected>Choose event...</option>
                </select>
            </div>

            <form action="" method="POST" id="eventForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="event_id" id="eventId">

                <label for="courseName">Course Title:</label>
                <input type="text" name="course_name" id="courseName" required>

                <label for="instructorName">Instructor Name:</label>
                <input type="text" name="instructor_name" id="instructorName" required>

                <label for="startDate">Start Date:</label>
                <input type="date" name="start_date" id="startDate" required>

                <label for="endDate">End Date:</label>
                <input type="date" name="end_date" id="endDate" required>

                <label for="startTime">Start Time:</label>
                <input type="time" name="start_time" id="startTime" required>

                <label for="endTime">End Time:</label>
                <input type="time" name="end_time" id="endTime" required>

                <button type="submit">Save</button>
            </form>

            <form action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this appointment?')" style="margin-top: 10px;">
                <input type="hidden" name="action" value="delete" />
                <input type="hidden" name="event_id" id="deleteEventId">
                <button type="submit" class="submit-btn delete-btn">🚮 Delete </button>
            </form>

            <button type="button" class="submit-btn cancel-btn">❌ Cancel</button>

        </div>
    </div>

    <script>

        const events = <?= json_encode($eventsFromDB, JSON_UNESCAPED_UNICODE); ?>;
        console.log("Events data received from PHP:", events); 
    </script>

    <script src="calendar.js"></script>
</body>

</html>