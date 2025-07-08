<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Restrict admin access to this page
restrict_admin_access();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Set page title
$page_title = "Schedule Appointment";

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'] ?? '';
$message = '';
$error = '';

// Get pet ID from URL if provided
$pet_id = isset($_GET['pet_id']) ? intval($_GET['pet_id']) : 0;

// If pet ID is provided, check if there are any pending appointments
if ($pet_id > 0) {
    // Check if there are any pending or upcoming appointments (not completed or cancelled)
    $pending_appt_sql = "SELECT COUNT(*) as count FROM appointments 
                        WHERE pet_id = ? 
                        AND status NOT IN ('completed', 'cancelled')";
    $pending_appt_stmt = $conn->prepare($pending_appt_sql);
    $pending_appt_stmt->bind_param("i", $pet_id);
    $pending_appt_stmt->execute();
    $pending_appt_result = $pending_appt_stmt->get_result();
    $pending_appt_count = $pending_appt_result->fetch_assoc()['count'];
    
    // If there are pending appointments, redirect to the pet profile
    if ($pending_appt_count > 0) {
        header("Location: view_pet.php?id=" . $pet_id);
        exit();
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $pet_id = $_POST['pet_id'] ?? 0;
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    $reason = $_POST['reason'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $vet_id = $_POST['vet_id'] ?? 0;
    
    // Validate required fields
    if (empty($pet_id) || empty($appointment_date) || empty($appointment_time) || empty($reason)) {
        $error = "Please fill in all required fields.";
    } else {
        // Check if the selected date is in the future
        $selected_date = strtotime($appointment_date);
        $today = strtotime(date('Y-m-d'));
        
        if ($selected_date < $today) {
            $error = "Please select a future date for the appointment.";
        } else {
            // Insert appointment into database
            $status = 'pending'; // Default status for new appointments
            
            $sql = "INSERT INTO appointments (pet_id, vet_id, appointment_date, appointment_time, reason, notes, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisssss", $pet_id, $vet_id, $appointment_date, $appointment_time, $reason, $notes, $status);
            
            if ($stmt->execute()) {
                $appointment_id = $conn->insert_id;
                $message = "Appointment scheduled successfully!";
                
                // Redirect to the appointment's page after a short delay
                header("refresh:2;url=view_appointment.php?id=" . $appointment_id);
            } else {
                $error = "Error scheduling appointment: " . $conn->error;
            }
        }
    }
}

// Get pets for selection
if ($user_type === 'owner') {
    $pets_sql = "SELECT * FROM pets WHERE owner_id = ? ORDER BY name ASC";
    $pets_stmt = $conn->prepare($pets_sql);
    $pets_stmt->bind_param("i", $user_id);
} else {
    // For vets, show all pets
    $pets_sql = "SELECT p.* FROM pets p JOIN appointments a ON p.id = a.pet_id WHERE a.vet_id = ? GROUP BY p.id ORDER BY p.name ASC";
    $pets_stmt = $conn->prepare($pets_sql);
    $pets_stmt->bind_param("i", $user_id);
}
$pets_stmt->execute();
$pets_result = $pets_stmt->get_result();

// Get veterinarians for selection
$vets_sql = "SELECT * FROM users WHERE user_type = 'veterinary' ORDER BY username ASC";
$vets_result = $conn->query($vets_sql);

// Include header
include 'includes/header.php';
?>

<div style="max-width: 48rem; margin: 0 auto; padding: 1rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.875rem; font-weight: bold;">Schedule Appointment</h1>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    
    <?php if (!empty($message)): ?>
        <div class="flash-message flash-success" style="margin-bottom: 1.5rem;">
            <p><?php echo $message; ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="flash-message flash-error" style="margin-bottom: 1.5rem;">
            <p><?php echo $error; ?></p>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <form method="post" action="add_appointment.php">
            <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 1.5rem;">
                <div>
                    <label for="pet_id" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                        Select Pet *
                    </label>
                    <select name="pet_id" id="pet_id" required class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                        <option value="">Select a Pet</option>
                        <?php while ($pet = $pets_result->fetch_assoc()): ?>
                            <option value="<?php echo $pet['id']; ?>" <?php echo ($pet_id == $pet['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($pet['name']); ?> (<?php echo htmlspecialchars($pet['species']); ?> - <?php echo htmlspecialchars($pet['breed']); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <?php if ($pets_result->num_rows === 0): ?>
                        <p style="font-size: 0.875rem; color: #ef4444; margin-top: 0.5rem;">
                            <?php echo $user_type === 'owner' ? 'You need to add a pet first.' : 'No pets found.'; ?>
                            <?php if ($user_type === 'owner'): ?>
                                <a href="add_pet.php" style="color: #2563eb; text-decoration: underline;">Add a pet</a>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                    <div>
                        <label for="appointment_date" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                            Appointment Date *
                        </label>
                        <input type="date" name="appointment_date" id="appointment_date" required class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div>
                        <label for="appointment_time" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                            Appointment Time *
                        </label>
                        <input type="time" name="appointment_time" id="appointment_time" required class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    </div>
                </div>
                
                <div>
                    <label for="reason" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                        Reason for Visit *
                    </label>
                    <select name="reason" id="reason" required class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                        <option value="">Select a Reason</option>
                        <option value="Wellness Check">Wellness Check</option>
                        <option value="Vaccination">Vaccination</option>
                        <option value="Illness">Illness</option>
                        <option value="Injury">Injury</option>
                        <option value="Surgery">Surgery</option>
                        <option value="Dental">Dental Care</option>
                        <option value="Grooming">Grooming</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <?php if ($user_type === 'owner'): ?>
                    <div>
                        <label for="vet_id" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                            Select Veterinarian (Optional)
                        </label>
                        <select name="vet_id" id="vet_id" class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                            <option value="">Any Available Veterinarian</option>
                            <?php while ($vet = $vets_result->fetch_assoc()): ?>
                                <option value="<?php echo $vet['id']; ?>">
                                    Dr. <?php echo htmlspecialchars($vet['username']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="vet_id" value="<?php echo $user_id; ?>">
                <?php endif; ?>
                
                <div>
                    <label for="notes" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                        Additional Notes (Optional)
                    </label>
                    <textarea name="notes" id="notes" rows="4" class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;"></textarea>
                </div>
                
                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary">Schedule Appointment</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
