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

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Set page title
$page_title = "View Appointment";

// Check if appointment ID is provided
if (!isset($_GET['id'])) {
    header("Location: my_appointments.php");
    exit();
}

$appointment_id = intval($_GET['id']);

// Get appointment details based on user type
if ($user_type == 'owner') {
    // For pet owners, check if they own the pet associated with this appointment
    $query = "SELECT a.*, p.name as pet_name, p.species, p.breed, p.date_of_birth, p.gender, p.weight, 
              u2.username as vet_name, u2.email as vet_email
              FROM appointments a 
              JOIN pets p ON a.pet_id = p.id 
              LEFT JOIN users u2 ON a.vet_id = u2.id
              WHERE a.id = ? AND p.owner_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $appointment_id, $user_id);
} else if ($user_type == 'veterinary') {
    // For vets, check if they are assigned to this appointment
    $query = "SELECT a.*, p.name as pet_name, p.species, p.breed, p.date_of_birth, p.gender, p.weight, 
              u1.username as owner_name, u1.email as owner_email
              FROM appointments a 
              JOIN pets p ON a.pet_id = p.id 
              JOIN users u1 ON p.owner_id = u1.id
              WHERE a.id = ? AND (a.vet_id = ? OR a.vet_id IS NULL)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $appointment_id, $user_id);
} else {
    header("Location: dashboard.php");
    exit();
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Appointment not found or user doesn't have permission
    header("Location: my_appointments.php");
    exit();
}

$appointment = $result->fetch_assoc();

// Include header
include 'includes/header.php';
?>

<div style="max-width: 48rem; margin: 0 auto; padding: 1rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.875rem; font-weight: bold;">Appointment Details</h1>
        <div>
            <a href="my_appointments.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Appointments
            </a>
            <?php if ($user_type == 'veterinary' && $appointment['status'] !== 'completed' && $appointment['status'] !== 'cancelled'): ?>
                <a href="update_appointment.php?id=<?php echo $appointment_id; ?>" class="btn btn-primary" style="margin-left: 0.5rem;">
                    <i class="fas fa-edit mr-1"></i> Update Status
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Appointment Status Banner -->
    <div style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 0.375rem; 
        <?php 
        if ($appointment['status'] === 'confirmed' || $appointment['status'] === 'approved') {
            echo 'background-color: #d1fae5; border-left: 4px solid #10b981;';
        } else if ($appointment['status'] === 'pending') {
            echo 'background-color: #fef3c7; border-left: 4px solid #d97706;';
        } else if ($appointment['status'] === 'completed') {
            echo 'background-color: #dbeafe; border-left: 4px solid #3b82f6;';
        } else {
            echo 'background-color: #fee2e2; border-left: 4px solid #ef4444;';
        }
        ?>">
        <div style="display: flex; align-items: center;">
            <div style="margin-right: 1rem; font-size: 1.5rem;">
                <?php 
                if ($appointment['status'] === 'confirmed' || $appointment['status'] === 'approved') {
                    echo '<i class="fas fa-check-circle" style="color: #10b981;"></i>';
                } else if ($appointment['status'] === 'pending') {
                    echo '<i class="fas fa-clock" style="color: #d97706;"></i>';
                } else if ($appointment['status'] === 'completed') {
                    echo '<i class="fas fa-calendar-check" style="color: #3b82f6;"></i>';
                } else {
                    echo '<i class="fas fa-times-circle" style="color: #ef4444;"></i>';
                }
                ?>
            </div>
            <div>
                <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 0.25rem;">
                    Status: <?php echo ucfirst($appointment['status']); ?>
                </h2>
                <p style="color: #4b5563;">
                    <?php 
                    if ($appointment['status'] === 'confirmed' || $appointment['status'] === 'approved') {
                        echo 'This appointment has been confirmed.';
                    } else if ($appointment['status'] === 'pending') {
                        echo 'This appointment is waiting for confirmation.';
                    } else if ($appointment['status'] === 'completed') {
                        echo 'This appointment has been completed.';
                    } else {
                        echo 'This appointment has been cancelled.';
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Appointment Details -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">
            Appointment Information
        </h2>
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
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
                <p style="font-weight: bold; color: #6b7280;">Reason for Visit</p>
                <p><?php echo htmlspecialchars($appointment['reason']); ?></p>
            </div>
            
            <div>
                <p style="font-weight: bold; color: #6b7280;">Created On</p>
                <p><?php echo date('F d, Y', strtotime($appointment['created_at'])); ?></p>
            </div>
        </div>
        
        <?php if (!empty($appointment['notes'])): ?>
            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                <p style="font-weight: bold; color: #6b7280;">Notes</p>
                <p><?php echo nl2br(htmlspecialchars($appointment['notes'])); ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pet Information -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">
            Pet Information
        </h2>
        
        <div style="display: flex; align-items: center; margin-bottom: 1rem;">
            <div style="width: 4rem; height: 4rem; background-color: #e5e7eb; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                <i class="fas fa-paw" style="font-size: 1.5rem; color: #9ca3af;"></i>
            </div>
            <div>
                <h3 style="font-size: 1.125rem; font-weight: bold; margin-bottom: 0.25rem;">
                    <?php echo htmlspecialchars($appointment['pet_name']); ?>
                </h3>
                <p style="color: #6b7280;">
                    <?php echo htmlspecialchars($appointment['species']); ?> - 
                    <?php echo htmlspecialchars($appointment['breed']); ?>
                </p>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
            <div>
                <p style="font-weight: bold; color: #6b7280;">Gender</p>
                <p><?php echo ucfirst(htmlspecialchars($appointment['gender'])); ?></p>
            </div>
            
            <div>
                <p style="font-weight: bold; color: #6b7280;">Age</p>
                <p>
                    <?php 
                    $birthDate = new DateTime($appointment['date_of_birth']);
                    $today = new DateTime();
                    $age = $birthDate->diff($today);
                    if ($age->y > 0) {
                        echo $age->y . ' year' . ($age->y > 1 ? 's' : '');
                    } else if ($age->m > 0) {
                        echo $age->m . ' month' . ($age->m > 1 ? 's' : '');
                    } else {
                        echo $age->d . ' day' . ($age->d > 1 ? 's' : '');
                    }
                    ?>
                </p>
            </div>
            
            <div>
                <p style="font-weight: bold; color: #6b7280;">Weight</p>
                <p><?php echo htmlspecialchars($appointment['weight']); ?> kg</p>
            </div>
        </div>
        
        <div style="margin-top: 1rem;">
            <a href="view_pet.php?id=<?php echo $appointment['pet_id']; ?>" class="btn btn-primary" style="font-size: 0.875rem; padding: 0.375rem 0.75rem;">
                <i class="fas fa-eye mr-1"></i> View Full Pet Profile
            </a>
        </div>
    </div>
    
    <!-- Contact Information -->
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">
            <?php echo $user_type == 'owner' ? 'Veterinarian Information' : 'Owner Information'; ?>
        </h2>
        
        <?php if ($user_type == 'owner'): ?>
            <?php if (!empty($appointment['vet_name'])): ?>
                <div style="display: flex; align-items: center;">
                    <div style="width: 3rem; height: 3rem; background-color: #dbeafe; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                        <i class="fas fa-user-md" style="font-size: 1.25rem; color: #3b82f6;"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 1.125rem; font-weight: bold; margin-bottom: 0.25rem;">
                            Dr. <?php echo htmlspecialchars($appointment['vet_name']); ?>
                        </h3>
                        <?php if (!empty($appointment['vet_email'])): ?>
                            <p style="color: #6b7280;">
                                <i class="fas fa-envelope mr-1"></i> <?php echo htmlspecialchars($appointment['vet_email']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <p style="color: #6b7280;">No veterinarian has been assigned to this appointment yet.</p>
            <?php endif; ?>
        <?php else: ?>
            <div style="display: flex; align-items: center;">
                <div style="width: 3rem; height: 3rem; background-color: #dbeafe; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                    <i class="fas fa-user" style="font-size: 1.25rem; color: #3b82f6;"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.125rem; font-weight: bold; margin-bottom: 0.25rem;">
                        <?php echo htmlspecialchars($appointment['owner_name']); ?>
                    </h3>
                    <?php if (!empty($appointment['owner_email'])): ?>
                        <p style="color: #6b7280;">
                            <i class="fas fa-envelope mr-1"></i> <?php echo htmlspecialchars($appointment['owner_email']); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Action Buttons -->
    <?php if ($appointment['status'] !== 'cancelled' && $appointment['status'] !== 'completed'): ?>
        <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
            <?php if ($user_type == 'owner'): ?>
                <form method="post" action="my_appointments.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                    <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">
                    <button type="submit" name="cancel_appointment" class="btn btn-danger">
                        <i class="fas fa-times mr-1"></i> Cancel Appointment
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
