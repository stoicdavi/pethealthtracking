<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Restrict admin access to this page
restrict_admin_access();

// Check if user is logged in and is a veterinarian
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'veterinary') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = "My Patients";

// Fetch all pets that the veterinarian has treated or has appointments with
$sql = "SELECT DISTINCT p.*, u.username as owner_name, u.email as owner_email, u.phone as owner_phone 
        FROM pets p 
        JOIN appointments a ON p.id = a.pet_id 
        JOIN users u ON p.owner_id = u.id
        WHERE a.vet_id = ? 
        ORDER BY p.name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Include header
include 'includes/header.php';

// Start dashboard container
echo '<div class="dashboard-container">';
include 'includes/header.php';
?>

<div style="max-width: 64rem; margin: 0 auto; padding: 1rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.875rem; font-weight: bold;">My Patients</h1>
        <div>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Search and filter options -->
    <div class="card" style="margin-bottom: 1.5rem; padding: 1rem;">
        <form method="GET" action="" style="display: flex; flex-wrap: wrap; gap: 1rem;">
            <div style="flex: 1; min-width: 200px;">
                <label for="search" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Search</label>
                <input type="text" id="search" name="search" placeholder="Search by pet name or owner" 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                       style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label for="species" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Species</label>
                <select id="species" name="species" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    <option value="">All Species</option>
                    <option value="Dog" <?php echo (isset($_GET['species']) && $_GET['species'] === 'Dog') ? 'selected' : ''; ?>>Dog</option>
                    <option value="Cat" <?php echo (isset($_GET['species']) && $_GET['species'] === 'Cat') ? 'selected' : ''; ?>>Cat</option>
                    <option value="Bird" <?php echo (isset($_GET['species']) && $_GET['species'] === 'Bird') ? 'selected' : ''; ?>>Bird</option>
                    <option value="Other" <?php echo (isset($_GET['species']) && $_GET['species'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label for="last_visit" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Last Visit</label>
                <select id="last_visit" name="last_visit" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    <option value="">Any Time</option>
                    <option value="7" <?php echo (isset($_GET['last_visit']) && $_GET['last_visit'] === '7') ? 'selected' : ''; ?>>Last 7 days</option>
                    <option value="30" <?php echo (isset($_GET['last_visit']) && $_GET['last_visit'] === '30') ? 'selected' : ''; ?>>Last 30 days</option>
                    <option value="90" <?php echo (isset($_GET['last_visit']) && $_GET['last_visit'] === '90') ? 'selected' : ''; ?>>Last 3 months</option>
                    <option value="180" <?php echo (isset($_GET['last_visit']) && $_GET['last_visit'] === '180') ? 'selected' : ''; ?>>Last 6 months</option>
                </select>
            </div>
            <div style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn btn-primary" style="height: 38px;">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <?php if (isset($_GET['search']) || isset($_GET['species']) || isset($_GET['last_visit'])): ?>
                    <a href="my_patients.php" class="btn btn-secondary" style="margin-left: 0.5rem; height: 38px;">
                        <i class="fas fa-times mr-1"></i> Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 1.5rem;">
            <?php while ($pet = $result->fetch_assoc()): ?>
                <div class="card" style="overflow: hidden;">
                    <div style="display: flex; flex-direction: column; md:flex-direction: row;">
                        <div style="width: 100%; max-width: 12rem; height: 12rem; background-color: #e5e7eb; display: flex; align-items: center; justify-content: center; margin-right: 1.5rem; margin-bottom: 1rem;">
                            <?php if (!empty($pet['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($pet['image_path']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fas fa-paw" style="font-size: 3rem; color: #9ca3af;"></i>
                            <?php endif; ?>
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h2 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($pet['name']); ?></h2>
                                    <p style="color: #6b7280; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($pet['species']); ?> - <?php echo htmlspecialchars($pet['breed']); ?></p>
                                    
                                    <div style="margin-bottom: 0.5rem;">
                                        <p style="color: #4b5563; margin-bottom: 0.25rem;">
                                            <i class="fas fa-user mr-1"></i> Owner: <?php echo htmlspecialchars($pet['owner_name']); ?>
                                        </p>
                                        <p style="color: #4b5563; margin-bottom: 0.25rem;">
                                            <i class="fas fa-envelope mr-1"></i> <?php echo htmlspecialchars($pet['owner_email']); ?>
                                        </p>
                                        <?php if (!empty($pet['owner_phone'])): ?>
                                        <p style="color: #4b5563; margin-bottom: 0.25rem;">
                                            <i class="fas fa-phone mr-1"></i> <?php echo htmlspecialchars($pet['owner_phone']); ?>
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;">
                                        <span style="display: inline-flex; align-items: center; background-color: #e5e7eb; color: #4b5563; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem;">
                                            <i class="fas fa-venus-mars mr-1"></i> <?php echo ucfirst($pet['gender']); ?>
                                        </span>
                                        <span style="display: inline-flex; align-items: center; background-color: #e5e7eb; color: #4b5563; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem;">
                                            <i class="fas fa-birthday-cake mr-1"></i> 
                                            <?php 
                                            $birthDate = new DateTime($pet['date_of_birth']);
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
                                        </span>
                                        <span style="display: inline-flex; align-items: center; background-color: #e5e7eb; color: #4b5563; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem;">
                                            <i class="fas fa-weight mr-1"></i> <?php echo htmlspecialchars($pet['weight']); ?> kg
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <a href="view_pet.php?id=<?php echo $pet['id']; ?>" class="btn btn-primary" style="margin-bottom: 0.5rem; display: block;">
                                        <i class="fas fa-eye mr-1"></i> View Details
                                    </a>
                                    <a href="pet_health_history.php?pet_id=<?php echo $pet['id']; ?>" class="btn btn-info" style="margin-bottom: 0.5rem; display: block;">
                                        <i class="fas fa-history mr-1"></i> Health History
                                    </a>
                                    <a href="add_health_record.php?pet_id=<?php echo $pet['id']; ?>" class="btn btn-success" style="margin-bottom: 0.5rem; display: block;">
                                        <i class="fas fa-notes-medical mr-1"></i> Add Record
                                    </a>
                                </div>
                            </div>
                            
                            <?php if (!empty($pet['notes'])): ?>
                                <div style="background-color: #f9fafb; padding: 0.75rem; border-radius: 0.375rem; margin-top: 0.5rem;">
                                    <p style="font-weight: bold; margin-bottom: 0.25rem;">Medical Notes:</p>
                                    <p style="color: #4b5563;"><?php echo nl2br(htmlspecialchars($pet['notes'])); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php
                            // Get last appointment info
                            $appt_sql = "SELECT appointment_date, status, reason FROM appointments 
                                        WHERE pet_id = ? AND vet_id = ? 
                                        ORDER BY appointment_date DESC LIMIT 1";
                            $appt_stmt = $conn->prepare($appt_sql);
                            $appt_stmt->bind_param("ii", $pet['id'], $user_id);
                            $appt_stmt->execute();
                            $last_appt = $appt_stmt->get_result()->fetch_assoc();
                            
                            // Get next appointment info if any
                            $next_appt_sql = "SELECT appointment_date, status, reason FROM appointments 
                                            WHERE pet_id = ? AND vet_id = ? AND appointment_date > NOW()
                                            ORDER BY appointment_date ASC LIMIT 1";
                            $next_appt_stmt = $conn->prepare($next_appt_sql);
                            $next_appt_stmt->bind_param("ii", $pet['id'], $user_id);
                            $next_appt_stmt->execute();
                            $next_appt = $next_appt_stmt->get_result()->fetch_assoc();
                            ?>
                            
                            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb; display: flex; flex-wrap: wrap; gap: 2rem;">
                                <?php if ($last_appt): ?>
                                <div style="flex: 1; min-width: 200px;">
                                    <p style="font-weight: bold; margin-bottom: 0.25rem;">Last Visit:</p>
                                    <div style="display: flex; align-items: center; margin-bottom: 0.25rem;">
                                        <span style="margin-right: 1rem;"><?php echo date('M d, Y', strtotime($last_appt['appointment_date'])); ?></span>
                                        <span class="badge <?php 
                                            echo $last_appt['status'] === 'confirmed' ? 'badge-confirmed' : 
                                                ($last_appt['status'] === 'pending' ? 'badge-pending' : 
                                                ($last_appt['status'] === 'completed' ? 'badge-completed' : 'badge-cancelled')); 
                                        ?>">
                                            <?php echo ucfirst($last_appt['status']); ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($last_appt['reason'])): ?>
                                        <p style="color: #6b7280; font-size: 0.875rem;"><?php echo htmlspecialchars($last_appt['reason']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($next_appt): ?>
                                <div style="flex: 1; min-width: 200px;">
                                    <p style="font-weight: bold; margin-bottom: 0.25rem;">Next Appointment:</p>
                                    <div style="display: flex; align-items: center; margin-bottom: 0.25rem;">
                                        <span style="margin-right: 1rem;"><?php echo date('M d, Y', strtotime($next_appt['appointment_date'])); ?></span>
                                        <span class="badge <?php 
                                            echo $next_appt['status'] === 'confirmed' ? 'badge-confirmed' : 
                                                ($next_appt['status'] === 'pending' ? 'badge-pending' : 
                                                ($next_appt['status'] === 'completed' ? 'badge-completed' : 'badge-cancelled')); 
                                        ?>">
                                            <?php echo ucfirst($next_appt['status']); ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($next_appt['reason'])): ?>
                                        <p style="color: #6b7280; font-size: 0.875rem;"><?php echo htmlspecialchars($next_appt['reason']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="card" style="text-align: center; padding: 3rem 0;">
            <div style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;">
                <i class="fas fa-paw"></i>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem;">No patients found</h2>
            <p style="color: #6b7280; margin-bottom: 1.5rem;">
                You don't have any patients yet. They will appear here once you have appointments.
            </p>
        </div>
    <?php endif; ?>
</div>

<?php
// Close dashboard container
echo '</div>';

// Include footer
include 'includes/footer.php';
?>
