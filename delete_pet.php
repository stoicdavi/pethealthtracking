<?php
session_start();
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if form was submitted and pet_id is provided
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['pet_id'])) {
    header("Location: dashboard.php");
    exit();
}

$pet_id = $_POST['pet_id'];
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Check if the user has permission to delete this pet
$check_sql = "SELECT * FROM pets WHERE id = ? AND owner_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $pet_id, $user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    // Pet not found or user doesn't have permission
    $_SESSION['error_message'] = "You don't have permission to delete this pet.";
    header("Location: dashboard.php");
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Delete related records first to maintain referential integrity
    
    // Delete vaccination records
    $vac_sql = "DELETE FROM vaccinations WHERE pet_id = ?";
    $vac_stmt = $conn->prepare($vac_sql);
    $vac_stmt->bind_param("i", $pet_id);
    $vac_stmt->execute();
    
    // Delete health records
    $health_sql = "DELETE FROM health_records WHERE pet_id = ?";
    $health_stmt = $conn->prepare($health_sql);
    $health_stmt->bind_param("i", $pet_id);
    $health_stmt->execute();
    
    // Delete appointments
    $appt_sql = "DELETE FROM appointments WHERE pet_id = ?";
    $appt_stmt = $conn->prepare($appt_sql);
    $appt_stmt->bind_param("i", $pet_id);
    $appt_stmt->execute();
    
    // Delete reminders
    $reminder_sql = "DELETE FROM reminders WHERE pet_id = ?";
    $reminder_stmt = $conn->prepare($reminder_sql);
    $reminder_stmt->bind_param("i", $pet_id);
    $reminder_stmt->execute();
    
    // Finally, delete the pet
    $pet_sql = "DELETE FROM pets WHERE id = ?";
    $pet_stmt = $conn->prepare($pet_sql);
    $pet_stmt->bind_param("i", $pet_id);
    $pet_stmt->execute();
    
    // Commit the transaction
    $conn->commit();
    
    $_SESSION['success_message'] = "Pet and all associated records have been deleted successfully.";
    header("Location: dashboard.php");
    exit();
    
} catch (Exception $e) {
    // Roll back the transaction if something failed
    $conn->rollback();
    $_SESSION['error_message'] = "Error deleting pet: " . $e->getMessage();
    header("Location: dashboard.php");
    exit();
}
?>
