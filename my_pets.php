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

// Set page title
$page_title = $user_type === 'veterinary' ? "My Patients" : "My Pets";

// Fetch pets based on user type
if ($user_type === 'veterinary') {
    // For veterinarians, show all pets they've treated
    $sql = "SELECT DISTINCT p.*, u.username as owner_name FROM pets p 
            JOIN appointments a ON p.id = a.pet_id 
            JOIN users u ON p.owner_id = u.id
            WHERE a.vet_id = ? 
            ORDER BY p.name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
} else {
    // For pet owners, show only their pets
    $sql = "SELECT * FROM pets WHERE owner_id = ? ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

// Include header
include 'includes/header.php';
?>

<div style="max-width: 64rem; margin: 0 auto; padding: 1rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.875rem; font-weight: bold;"><?php echo $user_type === 'veterinary' ? "My Patients" : "My Pets"; ?></h1>
        <?php if ($user_type === 'owner'): ?>
            <a href="add_pet.php" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Add New Pet
            </a>
        <?php endif; ?>
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
                                    
                                    <?php if ($user_type === 'veterinary' && isset($pet['owner_name'])): ?>
                                        <p style="color: #4b5563; margin-bottom: 0.5rem;">
                                            <i class="fas fa-user mr-1"></i> Owner: <?php echo htmlspecialchars($pet['owner_name']); ?>
                                        </p>
                                    <?php endif; ?>
                                    
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
                                    
                                    <?php if ($user_type === 'owner'): ?>
                                        <a href="edit_pet.php?id=<?php echo $pet['id']; ?>" class="btn btn-secondary" style="margin-bottom: 0.5rem; display: block;">
                                            <i class="fas fa-edit mr-1"></i> Edit
                                        </a>
                                        <a href="add_appointment.php?pet_id=<?php echo $pet['id']; ?>" class="btn btn-success" style="margin-bottom: 0.5rem; display: block;">
                                            <i class="fas fa-calendar-plus mr-1"></i> Schedule
                                        </a>
                                    <?php else: ?>
                                        <a href="add_health_record.php?pet_id=<?php echo $pet['id']; ?>" class="btn btn-success" style="margin-bottom: 0.5rem; display: block;">
                                            <i class="fas fa-notes-medical mr-1"></i> Add Record
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($pet['notes'])): ?>
                                <div style="background-color: #f9fafb; padding: 0.75rem; border-radius: 0.375rem; margin-top: 0.5rem;">
                                    <p style="font-weight: bold; margin-bottom: 0.25rem;">Notes:</p>
                                    <p style="color: #4b5563;"><?php echo nl2br(htmlspecialchars($pet['notes'])); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($user_type === 'veterinary'): ?>
                                <?php
                                // Get last appointment info
                                $appt_sql = "SELECT appointment_date, status FROM appointments 
                                            WHERE pet_id = ? AND vet_id = ? 
                                            ORDER BY appointment_date DESC LIMIT 1";
                                $appt_stmt = $conn->prepare($appt_sql);
                                $appt_stmt->bind_param("ii", $pet['id'], $user_id);
                                $appt_stmt->execute();
                                $last_appt = $appt_stmt->get_result()->fetch_assoc();
                                
                                if ($last_appt):
                                ?>
                                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                                        <p style="font-weight: bold; margin-bottom: 0.25rem;">Last Visit:</p>
                                        <div style="display: flex; align-items: center;">
                                            <span style="margin-right: 1rem;"><?php echo date('M d, Y', strtotime($last_appt['appointment_date'])); ?></span>
                                            <span class="badge <?php 
                                                echo $last_appt['status'] === 'confirmed' ? 'badge-confirmed' : 
                                                    ($last_appt['status'] === 'pending' ? 'badge-pending' : 
                                                    ($last_appt['status'] === 'completed' ? 'badge-completed' : 'badge-cancelled')); 
                                            ?>">
                                                <?php echo ucfirst($last_appt['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
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
            <h2 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem;">
                <?php echo $user_type === 'veterinary' ? "No patients found" : "No pets found"; ?>
            </h2>
            <p style="color: #6b7280; margin-bottom: 1.5rem;">
                <?php if ($user_type === 'veterinary'): ?>
                    You don't have any patients yet. They will appear here once you have appointments.
                <?php else: ?>
                    You haven't added any pets yet. Click the button below to add your first pet.
                <?php endif; ?>
            </p>
            <?php if ($user_type === 'owner'): ?>
                <a href="add_pet.php" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Add New Pet
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
