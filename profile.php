<?php
session_start();
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Set page title
$page_title = "My Profile";

// Get user information
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Get statistics based on user type
if ($user['user_type'] === 'owner') {
    // Get pet count
    $pet_sql = "SELECT COUNT(*) as count FROM pets WHERE owner_id = ?";
    $pet_stmt = $conn->prepare($pet_sql);
    $pet_stmt->bind_param("i", $user_id);
    $pet_stmt->execute();
    $pet_count = $pet_stmt->get_result()->fetch_assoc()['count'];
    
    // Get appointment count
    $appt_sql = "SELECT COUNT(*) as count FROM appointments a 
                JOIN pets p ON a.pet_id = p.id 
                WHERE p.owner_id = ?";
    $appt_stmt = $conn->prepare($appt_sql);
    $appt_stmt->bind_param("i", $user_id);
    $appt_stmt->execute();
    $appointment_count = $appt_stmt->get_result()->fetch_assoc()['count'];
    
    // Get reminder count
    $reminder_sql = "SELECT COUNT(*) as count FROM reminders r 
                    JOIN pets p ON r.pet_id = p.id 
                    WHERE p.owner_id = ?";
    $reminder_stmt = $conn->prepare($reminder_sql);
    $reminder_stmt->bind_param("i", $user_id);
    $reminder_stmt->execute();
    $reminder_count = $reminder_stmt->get_result()->fetch_assoc()['count'];
} else {
    // For veterinarians
    // Get patient count
    $patient_sql = "SELECT COUNT(DISTINCT pet_id) as count FROM appointments WHERE vet_id = ?";
    $patient_stmt = $conn->prepare($patient_sql);
    $patient_stmt->bind_param("i", $user_id);
    $patient_stmt->execute();
    $patient_count = $patient_stmt->get_result()->fetch_assoc()['count'];
    
    // Get appointment count
    $appt_sql = "SELECT COUNT(*) as count FROM appointments WHERE vet_id = ?";
    $appt_stmt = $conn->prepare($appt_sql);
    $appt_stmt->bind_param("i", $user_id);
    $appt_stmt->execute();
    $appointment_count = $appt_stmt->get_result()->fetch_assoc()['count'];
    
    // Get completed appointment count
    $completed_sql = "SELECT COUNT(*) as count FROM appointments WHERE vet_id = ? AND status = 'completed'";
    $completed_stmt = $conn->prepare($completed_sql);
    $completed_stmt->bind_param("i", $user_id);
    $completed_stmt->execute();
    $completed_count = $completed_stmt->get_result()->fetch_assoc()['count'];
}

// Include header
include 'includes/header.php';
?>

<div style="max-width: 48rem; margin: 0 auto; padding: 1rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.875rem; font-weight: bold;">My Profile</h1>
        <a href="settings.php" class="btn btn-primary">
            <i class="fas fa-cog mr-1"></i> Edit Profile
        </a>
    </div>
    
    <div class="card" style="margin-bottom: 1.5rem;">
        <div style="display: flex; align-items: center; margin-bottom: 1.5rem;">
            <div style="width: 6rem; height: 6rem; background-color: #3b82f6; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-right: 1.5rem;">
                <span style="color: white; font-size: 2rem; font-weight: bold;">
                    <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                </span>
            </div>
            <div>
                <h2 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 0.25rem;">
                    <?php echo htmlspecialchars($user['username']); ?>
                </h2>
                <p style="color: #6b7280; margin-bottom: 0.25rem;">
                    <i class="fas fa-envelope mr-1"></i> <?php echo htmlspecialchars($user['email']); ?>
                </p>
                <?php if (!empty($user['phone'])): ?>
                    <p style="color: #6b7280;">
                        <i class="fas fa-phone mr-1"></i> <?php echo htmlspecialchars($user['phone']); ?>
                    </p>
                <?php endif; ?>
                <p style="margin-top: 0.5rem;">
                    <span class="badge <?php echo $user['user_type'] === 'owner' ? 'badge-confirmed' : 'badge-completed'; ?>">
                        <?php echo ucfirst($user['user_type']); ?>
                    </span>
                </p>
            </div>
        </div>
        
        <div style="border-top: 1px solid #e5e7eb; padding-top: 1.5rem;">
            <h3 style="font-size: 1.125rem; font-weight: bold; margin-bottom: 1rem;">Account Statistics</h3>
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                <?php if ($user['user_type'] === 'owner'): ?>
                    <div style="background-color: #dbeafe; padding: 1rem; border-radius: 0.375rem; text-align: center;">
                        <p style="font-size: 2rem; font-weight: bold; color: #1e40af;"><?php echo $pet_count; ?></p>
                        <p style="color: #1e40af;">Pets</p>
                    </div>
                    
                    <div style="background-color: #d1fae5; padding: 1rem; border-radius: 0.375rem; text-align: center;">
                        <p style="font-size: 2rem; font-weight: bold; color: #065f46;"><?php echo $appointment_count; ?></p>
                        <p style="color: #065f46;">Appointments</p>
                    </div>
                    
                    <div style="background-color: #fef3c7; padding: 1rem; border-radius: 0.375rem; text-align: center;">
                        <p style="font-size: 2rem; font-weight: bold; color: #92400e;"><?php echo $reminder_count; ?></p>
                        <p style="color: #92400e;">Reminders</p>
                    </div>
                <?php else: ?>
                    <div style="background-color: #dbeafe; padding: 1rem; border-radius: 0.375rem; text-align: center;">
                        <p style="font-size: 2rem; font-weight: bold; color: #1e40af;"><?php echo $patient_count; ?></p>
                        <p style="color: #1e40af;">Patients</p>
                    </div>
                    
                    <div style="background-color: #d1fae5; padding: 1rem; border-radius: 0.375rem; text-align: center;">
                        <p style="font-size: 2rem; font-weight: bold; color: #065f46;"><?php echo $appointment_count; ?></p>
                        <p style="color: #065f46;">Appointments</p>
                    </div>
                    
                    <div style="background-color: #fee2e2; padding: 1rem; border-radius: 0.375rem; text-align: center;">
                        <p style="font-size: 2rem; font-weight: bold; color: #991b1b;"><?php echo $completed_count; ?></p>
                        <p style="color: #991b1b;">Completed</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h3 style="font-size: 1.125rem; font-weight: bold; margin-bottom: 1rem;">Account Actions</h3>
        
        <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 1rem;">
            <a href="settings.php" style="display: flex; align-items: center; padding: 1rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; text-decoration: none; color: inherit;">
                <div style="width: 2.5rem; height: 2.5rem; background-color: #dbeafe; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                    <i class="fas fa-cog" style="color: #1e40af;"></i>
                </div>
                <div>
                    <h4 style="font-weight: bold; margin-bottom: 0.25rem;">Account Settings</h4>
                    <p style="color: #6b7280; font-size: 0.875rem;">Update your profile information and password</p>
                </div>
                <i class="fas fa-chevron-right ml-auto" style="color: #9ca3af;"></i>
            </a>
            
            <?php if ($user['user_type'] === 'owner'): ?>
                <a href="my_pets.php" style="display: flex; align-items: center; padding: 1rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; text-decoration: none; color: inherit;">
                    <div style="width: 2.5rem; height: 2.5rem; background-color: #d1fae5; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                        <i class="fas fa-paw" style="color: #065f46;"></i>
                    </div>
                    <div>
                        <h4 style="font-weight: bold; margin-bottom: 0.25rem;">My Pets</h4>
                        <p style="color: #6b7280; font-size: 0.875rem;">View and manage your pets</p>
                    </div>
                    <i class="fas fa-chevron-right ml-auto" style="color: #9ca3af;"></i>
                </a>
            <?php else: ?>
                <a href="my_patients.php" style="display: flex; align-items: center; padding: 1rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; text-decoration: none; color: inherit;">
                    <div style="width: 2.5rem; height: 2.5rem; background-color: #d1fae5; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                        <i class="fas fa-stethoscope" style="color: #065f46;"></i>
                    </div>
                    <div>
                        <h4 style="font-weight: bold; margin-bottom: 0.25rem;">My Patients</h4>
                        <p style="color: #6b7280; font-size: 0.875rem;">View and manage your patients</p>
                    </div>
                    <i class="fas fa-chevron-right ml-auto" style="color: #9ca3af;"></i>
                </a>
            <?php endif; ?>
            
            <a href="logout.php" style="display: flex; align-items: center; padding: 1rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; text-decoration: none; color: inherit;">
                <div style="width: 2.5rem; height: 2.5rem; background-color: #fee2e2; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                    <i class="fas fa-sign-out-alt" style="color: #991b1b;"></i>
                </div>
                <div>
                    <h4 style="font-weight: bold; margin-bottom: 0.25rem;">Logout</h4>
                    <p style="color: #6b7280; font-size: 0.875rem;">Sign out of your account</p>
                </div>
                <i class="fas fa-chevron-right ml-auto" style="color: #9ca3af;"></i>
            </a>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
