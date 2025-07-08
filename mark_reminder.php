<?php
session_start();
require_once 'includes/db_connect.php';

// Check if user is logged in and is a pet owner
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'owner') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if reminder ID and action are provided
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header("Location: dashboard.php");
    exit();
}

$reminder_id = intval($_GET['id']);
$action = $_GET['action'];

// Validate action
if ($action != 'complete') {
    header("Location: dashboard.php");
    exit();
}

// Check if reminder exists and belongs to one of the user's pets
$check_query = "SELECT r.* FROM reminders r 
               JOIN pets p ON r.pet_id = p.id 
               WHERE r.id = $reminder_id AND p.owner_id = $user_id";
$check_result = $conn->query($check_query);

if ($check_result->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}

// Mark reminder as completed
$update_query = "UPDATE reminders SET is_completed = 1 WHERE id = $reminder_id";

if ($conn->query($update_query) === TRUE) {
    // Redirect back to dashboard
    header("Location: dashboard.php");
} else {
    echo "Error updating record: " . $conn->error;
}

$conn->close();
?>
