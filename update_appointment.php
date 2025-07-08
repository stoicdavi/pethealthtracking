<?php
session_start();
require_once 'includes/db_connect.php';

// Check if user is logged in and is a veterinarian
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'veterinary') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Set page title
$page_title = "Update Appointment";

// Check if appointment ID is provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$appointment_id = $_GET['id'];

// Get appointment details
$query = "SELECT a.*, p.name as pet_name, p.species, p.breed, u.username as owner_name 
          FROM appointments a 
          JOIN pets p ON a.pet_id = p.id 
          JOIN users u ON p.owner_id = u.id 
          WHERE a.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: dashboard.php");
    exit();
}

$appointment = $result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (empty($status)) {
        $error = "Please select a status.";
    } else {
        // Update appointment status
        $update_sql = "UPDATE appointments SET status = ?, notes = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi", $status, $notes, $appointment_id);
        
        if ($update_stmt->execute()) {
            $success = "Appointment status updated successfully!";
            
            // Refresh appointment data
            $stmt->execute();
            $result = $stmt->get_result();
            $appointment = $result->fetch_assoc();
        } else {
            $error = "Error updating appointment: " . $conn->error;
        }
    }
}

// Include header
include 'includes/header.php';
?>

<div style="max-width: 48rem; margin: 0 auto; padding: 1rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.875rem; font-weight: bold;">Update Appointment Status</h1>
        <a href="my_appointments.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to Appointments
        </a>
    </div>
    
    <?php if (!empty($success)): ?>
        <div class="flash-message flash-success" style="margin-bottom: 1.5rem;">
            <p><?php echo $success; ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="flash-message flash-error" style="margin-bottom: 1.5rem;">
            <p><?php echo $error; ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Appointment Details -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">
            Appointment Details
        </h2>
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
            <div>
                <p style="font-weight: bold; color: #6b7280;">Pet</p>
                <p><?php echo htmlspecialchars($appointment['pet_name']); ?> (<?php echo htmlspecialchars($appointment['species']); ?> - <?php echo htmlspecialchars($appointment['breed']); ?>)</p>
            </div>
            
            <div>
                <p style="font-weight: bold; color: #6b7280;">Owner</p>
                <p><?php echo htmlspecialchars($appointment['owner_name']); ?></p>
            </div>
            
            <div>
                <p style="font-weight: bold; color: #6b7280;">Date</p>
                <p><?php echo date('F d, Y', strtotime($appointment['appointment_date'])); ?></p>
            </div>
            
            <?php if (!empty($appointment['appointment_time'])): ?>
                <div>
                    <p style="font-weight: bold; color: #6b7280;">Time</p>
                    <p><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></p>
                </div>
            <?php endif; ?>
            
            <div>
                <p style="font-weight: bold; color: #6b7280;">Reason</p>
                <p><?php echo htmlspecialchars($appointment['reason']); ?></p>
            </div>
            
            <div>
                <p style="font-weight: bold; color: #6b7280;">Current Status</p>
                <p>
                    <span class="badge <?php 
                        echo $appointment['status'] === 'confirmed' ? 'badge-confirmed' : 
                            ($appointment['status'] === 'pending' ? 'badge-pending' : 
                            ($appointment['status'] === 'completed' ? 'badge-completed' : 'badge-cancelled')); 
                    ?>">
                        <?php echo ucfirst($appointment['status']); ?>
                    </span>
                </p>
            </div>
        </div>
        
        <?php if (!empty($appointment['notes'])): ?>
            <div>
                <p style="font-weight: bold; color: #6b7280;">Notes</p>
                <p><?php echo nl2br(htmlspecialchars($appointment['notes'])); ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Update Status Form -->
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">
            Update Status
        </h2>
        
        <form method="post" action="update_appointment.php?id=<?php echo $appointment_id; ?>">
            <div style="margin-bottom: 1rem;">
                <label for="status" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                    New Status *
                </label>
                <select name="status" id="status" required style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    <option value="">Select Status</option>
                    <option value="pending" <?php echo $appointment['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo $appointment['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="confirmed" <?php echo $appointment['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="completed" <?php echo $appointment['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $appointment['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label for="notes" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                    Notes (Optional)
                </label>
                <textarea name="notes" id="notes" rows="4" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;"><?php echo htmlspecialchars($appointment['notes'] ?? ''); ?></textarea>
                <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                    Add any notes about this appointment or status change.
                </p>
            </div>
            
            <div style="display: flex; justify-content: flex-end;">
                <a href="my_appointments.php" class="btn btn-secondary" style="margin-right: 0.5rem;">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    Update Status
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
