<?php
session_start();
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'] ?? '';
$error = '';
$success = '';

// Check if user is a veterinarian
if ($user_type !== 'veterinary') {
    header("Location: dashboard.php");
    exit();
}

// Set page title
$page_title = "Add Health Record";

// Check if pet ID is provided
if (!isset($_GET['pet_id'])) {
    header("Location: dashboard.php");
    exit();
}

$pet_id = $_GET['pet_id'];

// Check if there are any completed appointments for this pet
$completed_appt_sql = "SELECT COUNT(*) as count FROM appointments WHERE pet_id = ? AND status = 'completed'";
$completed_appt_stmt = $conn->prepare($completed_appt_sql);
$completed_appt_stmt->bind_param("i", $pet_id);
$completed_appt_stmt->execute();
$completed_appt_result = $completed_appt_stmt->get_result();
$completed_appt_count = $completed_appt_result->fetch_assoc()['count'];

// If no completed appointments, redirect to dashboard
if ($completed_appt_count == 0) {
    header("Location: dashboard.php");
    exit();
}
    header("Location: dashboard.php");
    exit();
}

$pet_id = intval($_GET['pet_id']);

// Check if user has permission to add health record for this pet
if ($user_type === 'owner') {
    // Pet owners can only add health records for their own pets
    $check_sql = "SELECT * FROM pets WHERE id = ? AND owner_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $pet_id, $user_id);
} else {
    // Veterinarians can add health records for any pet
    $check_sql = "SELECT p.*, u.username as owner_name 
                 FROM pets p 
                 JOIN users u ON p.owner_id = u.id 
                 WHERE p.id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $pet_id);
}

$check_stmt->execute();
$pet_result = $check_stmt->get_result();

if ($pet_result->num_rows === 0) {
    // Pet not found or user doesn't have permission
    header("Location: dashboard.php");
    exit();
}

$pet = $pet_result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $record_type = $_POST['record_type'] ?? '';
    $record_date = $_POST['record_date'] ?? '';
    $description = $_POST['description'] ?? '';
    
    // Validate input
    if (empty($record_type) || empty($record_date) || empty($description)) {
        $error = "Please fill in all required fields.";
    } else {
        // Insert health record
        $insert_sql = "INSERT INTO health_records (pet_id, record_type, record_date, description, vet_id) 
                      VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("isssi", $pet_id, $record_type, $record_date, $description, $user_id);
        
        if ($insert_stmt->execute()) {
            $success = "Health record added successfully!";
            
            // Clear form data
            $record_type = '';
            $record_date = '';
            $description = '';
        } else {
            $error = "Error adding health record: " . $conn->error;
        }
    }
}

// Include header
include 'includes/header.php';
?>

<div style="max-width: 48rem; margin: 0 auto; padding: 1rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.875rem; font-weight: bold;">Add Health Record</h1>
        <a href="view_pet.php?id=<?php echo $pet_id; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to Pet
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
    
    <!-- Pet Information -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">
            Pet Information
        </h2>
        
        <div style="display: flex; align-items: center;">
            <div style="width: 4rem; height: 4rem; background-color: #e5e7eb; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                <i class="fas fa-paw" style="font-size: 1.5rem; color: #9ca3af;"></i>
            </div>
            <div>
                <h3 style="font-size: 1.125rem; font-weight: bold; margin-bottom: 0.25rem;">
                    <?php echo htmlspecialchars($pet['name']); ?>
                </h3>
                <p style="color: #6b7280;">
                    <?php echo htmlspecialchars($pet['species']); ?> - 
                    <?php echo htmlspecialchars($pet['breed']); ?>
                </p>
                <?php if ($user_type === 'veterinary' && isset($pet['owner_name'])): ?>
                    <p style="color: #4b5563; font-size: 0.875rem;">
                        <i class="fas fa-user mr-1"></i> Owner: <?php echo htmlspecialchars($pet['owner_name']); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Health Record Form -->
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">
            Health Record Details
        </h2>
        
        <form method="post" action="add_health_record.php?pet_id=<?php echo $pet_id; ?>">
            <div style="margin-bottom: 1rem;">
                <label for="record_type" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                    Record Type *
                </label>
                <select name="record_type" id="record_type" required style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    <option value="">Select Record Type</option>
                    <option value="Vaccination">Vaccination</option>
                    <option value="Check-up">Check-up</option>
                    <option value="Surgery">Surgery</option>
                    <option value="Medication">Medication</option>
                    <option value="Lab Test">Lab Test</option>
                    <option value="X-Ray">X-Ray</option>
                    <option value="Dental">Dental</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label for="record_date" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                    Record Date *
                </label>
                <input type="date" name="record_date" id="record_date" required 
                       value="<?php echo date('Y-m-d'); ?>"
                       max="<?php echo date('Y-m-d'); ?>"
                       style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label for="description" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                    Description *
                </label>
                <textarea name="description" id="description" rows="6" required 
                          style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;"
                          placeholder="Enter detailed information about the health record, including any findings, treatments, medications, dosages, and recommendations."
                ></textarea>
                <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                    Include all relevant details such as symptoms, diagnosis, treatment, medications, dosage, and follow-up instructions.
                </p>
            </div>
            
            <div style="display: flex; justify-content: flex-end;">
                <button type="button" onclick="window.location.href='view_pet.php?id=<?php echo $pet_id; ?>'" class="btn btn-secondary" style="margin-right: 0.5rem;">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Add Health Record
                </button>
            </div>
        </form>
    </div>
    
    <!-- Previous Health Records -->
    <?php
    // Get previous health records
    $records_sql = "SELECT * FROM health_records WHERE pet_id = ? ORDER BY record_date DESC LIMIT 5";
    $records_stmt = $conn->prepare($records_sql);
    $records_stmt->bind_param("i", $pet_id);
    $records_stmt->execute();
    $records_result = $records_stmt->get_result();
    
    if ($records_result->num_rows > 0):
    ?>
        <div style="margin-top: 1.5rem;">
            <h3 style="font-size: 1.125rem; font-weight: bold; margin-bottom: 1rem;">Recent Health Records</h3>
            
            <div class="card">
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Added By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($record = $records_result->fetch_assoc()): 
                                // Get vet name
                                $vet_sql = "SELECT username FROM users WHERE id = ?";
                                $vet_stmt = $conn->prepare($vet_sql);
                                $vet_stmt->bind_param("i", $record['vet_id']);
                                $vet_stmt->execute();
                                $vet_result = $vet_stmt->get_result();
                                $vet_name = $vet_result->num_rows > 0 ? $vet_result->fetch_assoc()['username'] : 'Unknown';
                            ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($record['record_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($record['record_type']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars(substr($record['description'], 0, 100) . (strlen($record['description']) > 100 ? '...' : ''))); ?></td>
                                    <td>Dr. <?php echo htmlspecialchars($vet_name); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 1rem; text-align: center;">
                    <a href="pet_health_history.php?pet_id=<?php echo $pet_id; ?>" class="btn btn-secondary" style="font-size: 0.875rem; padding: 0.375rem 0.75rem;">
                        View Complete Health History
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
