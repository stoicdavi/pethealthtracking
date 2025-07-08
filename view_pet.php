<?php
session_start();
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if pet ID is provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$pet_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch pet details
$sql = "SELECT * FROM pets WHERE id = ? AND (owner_id = ? OR 
        (SELECT user_type FROM users WHERE id = ?) = 'veterinary')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $pet_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Pet not found or user doesn't have permission
    header("Location: dashboard.php");
    exit();
}

$pet = $result->fetch_assoc();

// Fetch health records
$health_sql = "SELECT * FROM health_records WHERE pet_id = ? ORDER BY record_date DESC";
$health_stmt = $conn->prepare($health_sql);
$health_stmt->bind_param("i", $pet_id);
$health_stmt->execute();
$health_records = $health_stmt->get_result();

// Fetch upcoming appointments
$appt_sql = "SELECT * FROM appointments WHERE pet_id = ? AND appointment_date >= CURDATE() ORDER BY appointment_date ASC";
$appt_stmt = $conn->prepare($appt_sql);
$appt_stmt->bind_param("i", $pet_id);
$appt_stmt->execute();
$appointments = $appt_stmt->get_result();

// Check if there are any pending or upcoming appointments (not completed or cancelled)
$pending_appt_sql = "SELECT COUNT(*) as count FROM appointments 
                    WHERE pet_id = ? 
                    AND status NOT IN ('completed', 'cancelled')";
$pending_appt_stmt = $conn->prepare($pending_appt_sql);
$pending_appt_stmt->bind_param("i", $pet_id);
$pending_appt_stmt->execute();
$pending_appt_result = $pending_appt_stmt->get_result();
$pending_appt_count = $pending_appt_result->fetch_assoc()['count'];

// Check if there are any completed appointments for this pet
$completed_appt_sql = "SELECT COUNT(*) as count FROM appointments WHERE pet_id = ? AND status = 'completed'";
$completed_appt_stmt = $conn->prepare($completed_appt_sql);
$completed_appt_stmt->bind_param("i", $pet_id);
$completed_appt_stmt->execute();
$completed_appt_result = $completed_appt_stmt->get_result();
$completed_appt_count = $completed_appt_result->fetch_assoc()['count'];

// Check if the current user is a veterinarian
$is_vet = ($_SESSION['user_type'] === 'veterinary');

// Set page title
$page_title = "Pet Details - " . $pet['name'];

// Include header
include 'includes/header.php';
?>

<div class="mb-6">
    <div class="flex justify-between items-center mb-6">
        <h1 style="font-size: 1.875rem; font-weight: bold; color: #1e40af;">Pet Details</h1>
        <div>
            <a href="dashboard.php" class="btn btn-primary mr-2">
                Back to Dashboard
            </a>
            <a href="edit_pet.php?id=<?php echo $pet_id; ?>" class="btn btn-success">
                Edit Pet
            </a>
        </div>
    </div>

    <div class="card mb-6">
        <div class="flex flex-col md:flex-row">
            <div style="width: 100%; margin-bottom: 1rem;">
                <?php if (!empty($pet['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($pet['image_path']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>" style="width: 100%; border-radius: 0.5rem;">
                <?php else: ?>
                    <div style="width: 100%; height: 16rem; background-color: #e5e7eb; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                        <span style="color: #6b7280;">No Image Available</span>
                    </div>
                <?php endif; ?>
            </div>
            <div style="width: 100%; padding-left: 0;">
                <h2 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem;"><?php echo htmlspecialchars($pet['name']); ?></h2>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                    <div>
                        <p style="color: #4b5563;">Species</p>
                        <p style="font-weight: 600;"><?php echo htmlspecialchars($pet['species']); ?></p>
                    </div>
                    <div>
                        <p style="color: #4b5563;">Breed</p>
                        <p style="font-weight: 600;"><?php echo htmlspecialchars($pet['breed']); ?></p>
                    </div>
                    <div>
                        <p style="color: #4b5563;">Date of Birth</p>
                        <p style="font-weight: 600;"><?php echo date('M d, Y', strtotime($pet['date_of_birth'])); ?></p>
                    </div>
                    <div>
                        <p style="color: #4b5563;">Gender</p>
                        <p style="font-weight: 600;"><?php echo ucfirst(htmlspecialchars($pet['gender'])); ?></p>
                    </div>
                    <div>
                        <p style="color: #4b5563;">Weight</p>
                        <p style="font-weight: 600;"><?php echo htmlspecialchars($pet['weight']); ?> kg</p>
                    </div>
                </div>
                <div style="margin-top: 1rem;">
                    <p style="color: #4b5563;">Special Notes</p>
                    <p style="font-weight: 600;"><?php echo !empty($pet['notes']) ? nl2br(htmlspecialchars($pet['notes'])) : 'No special notes'; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Health Records Section -->
    <div class="card mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 style="font-size: 1.25rem; font-weight: bold;">Health Records</h2>
            <?php 
            // Only show Add Health Record button if:
            // 1. User is a veterinarian AND
            // 2. There is at least one completed appointment for this pet
            if ($is_vet && $completed_appt_count > 0): 
            ?>
                <a href="add_health_record.php?pet_id=<?php echo $pet_id; ?>" class="btn btn-primary" style="font-size: 0.875rem; padding: 0.25rem 0.75rem;">
                    Add Health Record
                </a>
            <?php elseif ($is_vet && $completed_appt_count == 0): ?>
                <span class="btn btn-secondary" style="font-size: 0.875rem; padding: 0.25rem 0.75rem; cursor: not-allowed; opacity: 0.7;" 
                      title="You can add health records after completing an appointment">
                    Add Health Record
                </span>
            <?php endif; ?>
        </div>
        <?php if ($health_records->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($record = $health_records->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($record['record_date'])); ?></td>
                                <td><?php echo htmlspecialchars($record['record_type']); ?></td>
                                <td><?php echo htmlspecialchars($record['description']); ?></td>
                                <td>
                                    <a href="view_health_record.php?id=<?php echo $record['id']; ?>" style="color: #2563eb; text-decoration: none;">View</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="color: #6b7280;">No health records available.</p>
        <?php endif; ?>
    </div>

    <!-- Reminders Section -->
    <div class="card mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 style="font-size: 1.25rem; font-weight: bold;">Vaccination & Health Reminders</h2>
            <a href="add_reminder.php?pet_id=<?php echo $pet_id; ?>" class="btn btn-primary" style="font-size: 0.875rem; padding: 0.25rem 0.75rem;">
                Add Reminder
            </a>
        </div>
        <?php 
        // Fetch reminders
        $reminder_sql = "SELECT * FROM reminders WHERE pet_id = ? ORDER BY reminder_date ASC";
        $reminder_stmt = $conn->prepare($reminder_sql);
        $reminder_stmt->bind_param("i", $pet_id);
        $reminder_stmt->execute();
        $reminders = $reminder_stmt->get_result();
        
        if ($reminders->num_rows > 0): 
        ?>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Due Date</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($reminder = $reminders->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($reminder['reminder_date'])); ?></td>
                                <td><?php echo htmlspecialchars($reminder['title']); ?></td>
                                <td><?php echo htmlspecialchars($reminder['description']); ?></td>
                                <td>
                                    <span class="badge <?php echo $reminder['is_completed'] ? 'badge-confirmed' : 'badge-pending'; ?>">
                                        <?php echo $reminder['is_completed'] ? 'Completed' : 'Pending'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="color: #6b7280;">No reminders available.</p>
        <?php endif; ?>
    </div>

    <!-- Upcoming Appointments Section -->
    <div class="card">
        <div class="flex justify-between items-center mb-4">
            <h2 style="font-size: 1.25rem; font-weight: bold;">Upcoming Appointments</h2>
            <?php if ($pending_appt_count == 0): ?>
                <a href="add_appointment.php?pet_id=<?php echo $pet_id; ?>" class="btn btn-primary" style="font-size: 0.875rem; padding: 0.25rem 0.75rem;">
                    Schedule Appointment
                </a>
            <?php else: ?>
                <span class="btn btn-secondary" style="font-size: 0.875rem; padding: 0.25rem 0.75rem; cursor: not-allowed; opacity: 0.7;" 
                      title="You can schedule a new appointment after the current appointment is completed or cancelled">
                    Schedule Appointment
                </span>
            <?php endif; ?>
        </div>
        <?php if ($appointments->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($appt = $appointments->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo date('M d, Y', strtotime($appt['appointment_date'])); ?> at 
                                    <?php echo date('h:i A', strtotime($appt['appointment_time'])); ?>
                                </td>
                                <td><?php echo htmlspecialchars($appt['reason']); ?></td>
                                <td>
                                    <span class="badge <?php echo $appt['status'] === 'confirmed' ? 'badge-confirmed' : 
                                        ($appt['status'] === 'pending' ? 'badge-pending' : 'badge-cancelled'); ?>">
                                        <?php echo ucfirst($appt['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view_appointment.php?id=<?php echo $appt['id']; ?>" style="color: #2563eb; text-decoration: none; margin-right: 0.5rem;">View</a>
                                    <?php if ($appt['status'] !== 'completed'): ?>
                                        <a href="edit_appointment.php?id=<?php echo $appt['id']; ?>" style="color: #10b981; text-decoration: none;">Edit</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="color: #6b7280;">No upcoming appointments.</p>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>

<style>
.btn-secondary {
    background-color: #9ca3af;
    color: white;
}

.btn-secondary:hover {
    background-color: #9ca3af;
}
</style>
