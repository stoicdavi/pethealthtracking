<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'] ?? '';

// Set page title
$page_title = "Dashboard";

// Get user information
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Include header
include 'includes/header.php';

// Start dashboard container
echo '<div class="dashboard-container">';

// If user is admin, show admin dashboard
if (is_admin()) {
    ?>
    <div class="mb-6">
        <h1 style="font-size: 1.875rem; font-weight: bold; color: #1e40af; margin-bottom: 1.5rem;">Admin Dashboard</h1>
        
        <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 1.5rem;">
            <div class="card" style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">
                    <i class="fas fa-shield-alt mr-1"></i> Admin Tools
                </h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
                    <a href="admin/index.php" style="display: flex; align-items: center; padding: 1rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; text-decoration: none; color: inherit;">
                        <div style="width: 2.5rem; height: 2.5rem; background-color: #dbeafe; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                            <i class="fas fa-tachometer-alt" style="color: #3b82f6;"></i>
                        </div>
                        <div>
                            <h3 style="font-weight: 600; margin-bottom: 0.25rem;">Admin Dashboard</h3>
                            <p style="font-size: 0.875rem; color: #6b7280;">View system statistics and manage the application</p>
                        </div>
                    </a>
                    
                    <a href="admin/manage_users.php" style="display: flex; align-items: center; padding: 1rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; text-decoration: none; color: inherit;">
                        <div style="width: 2.5rem; height: 2.5rem; background-color: #dbeafe; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                            <i class="fas fa-users-cog" style="color: #3b82f6;"></i>
                        </div>
                        <div>
                            <h3 style="font-weight: 600; margin-bottom: 0.25rem;">Manage Users</h3>
                            <p style="font-size: 0.875rem; color: #6b7280;">Add, edit, or deactivate user accounts</p>
                        </div>
                    </a>
                    
                    <a href="admin/manage_vet_codes.php" style="display: flex; align-items: center; padding: 1rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; text-decoration: none; color: inherit;">
                        <div style="width: 2.5rem; height: 2.5rem; background-color: #dbeafe; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                            <i class="fas fa-key" style="color: #3b82f6;"></i>
                        </div>
                        <div>
                            <h3 style="font-weight: 600; margin-bottom: 0.25rem;">Manage Vet Codes</h3>
                            <p style="font-size: 0.875rem; color: #6b7280;">Generate and manage veterinarian registration codes</p>
                        </div>
                    </a>
                    
                    <a href="admin/system_settings.php" style="display: flex; align-items: center; padding: 1rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; text-decoration: none; color: inherit;">
                        <div style="width: 2.5rem; height: 2.5rem; background-color: #dbeafe; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                            <i class="fas fa-cogs" style="color: #3b82f6;"></i>
                        </div>
                        <div>
                            <h3 style="font-weight: 600; margin-bottom: 0.25rem;">System Settings</h3>
                            <p style="font-size: 0.875rem; color: #6b7280;">Configure application settings and preferences</p>
                        </div>
                    </a>
                </div>
            </div>
            
            <div class="card">
                <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">
                    <i class="fas fa-info-circle mr-1"></i> Admin Information
                </h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
                    <div>
                        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem;">Account Details</h3>
                        <p style="margin-bottom: 0.5rem;"><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                        <p style="margin-bottom: 0.5rem;"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Role:</strong> Administrator</p>
                    </div>
                    
                    <div>
                        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem;">Security Notice</h3>
                        <p style="color: #6b7280;">
                            As an administrator, you have access to sensitive system functions. Please ensure you:
                        </p>
                        <ul style="list-style-type: disc; padding-left: 1.5rem; margin-top: 0.5rem;">
                            <li>Use a strong, unique password</li>
                            <li>Log out when not using the system</li>
                            <li>Do not share your credentials</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
} else {
    // Regular user dashboard code continues here
    
    // Get pet count
    if ($user_type === 'owner') {
        $pet_sql = "SELECT COUNT(*) as count FROM pets WHERE owner_id = ?";
        $pet_stmt = $conn->prepare($pet_sql);
        $pet_stmt->bind_param("i", $user_id);
        $pet_stmt->execute();
        $pet_count = $pet_stmt->get_result()->fetch_assoc()['count'];
    } else {
        $pet_sql = "SELECT COUNT(DISTINCT pet_id) as count FROM appointments WHERE vet_id = ?";
        $pet_stmt = $conn->prepare($pet_sql);
        $pet_stmt->bind_param("i", $user_id);
        $pet_stmt->execute();
        $pet_count = $pet_stmt->get_result()->fetch_assoc()['count'];
    }

// Get upcoming appointments - fixed ORDER BY clause
$appt_sql = "SELECT a.*, p.name as pet_name, p.species, p.breed 
             FROM appointments a 
             JOIN pets p ON a.pet_id = p.id 
             WHERE " . ($user_type === 'owner' ? "p.owner_id = ?" : "a.vet_id = ?") . " 
             AND a.appointment_date >= CURDATE() 
             ORDER BY a.appointment_date ASC
             LIMIT 5";
$appt_stmt = $conn->prepare($appt_sql);
$appt_stmt->bind_param("i", $user_id);
$appt_stmt->execute();
$appointments = $appt_stmt->get_result();

// Get upcoming reminders
$reminder_sql = "SELECT r.*, p.name as pet_name, p.species 
                FROM reminders r 
                JOIN pets p ON r.pet_id = p.id 
                WHERE p.owner_id = ? AND r.reminder_date >= CURDATE() AND r.is_completed = 0
                ORDER BY r.reminder_date ASC 
                LIMIT 5";
$reminder_stmt = $conn->prepare($reminder_sql);
$reminder_stmt->bind_param("i", $user_id);
$reminder_stmt->execute();
$reminders = $reminder_stmt->get_result();

// Include header
include 'includes/header.php';
?>

<div class="mb-6">
    <h1 style="font-size: 1.875rem; font-weight: bold; margin-bottom: 1rem;">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
    
    <!-- Dashboard Stats -->
    <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 1rem; margin-bottom: 2rem;">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
            <div class="card" style="background-color: #dbeafe; border-left: 4px solid #2563eb; display: flex; align-items: center;">
                <div style="margin-right: 1rem; font-size: 2rem; color: #2563eb;">
                    <i class="fas fa-paw"></i>
                </div>
                <div>
                    <p style="font-size: 0.875rem; color: #4b5563;"><?php echo $user_type === 'owner' ? 'My Pets' : 'My Patients'; ?></p>
                    <p style="font-size: 1.5rem; font-weight: bold;"><?php echo $pet_count; ?></p>
                </div>
            </div>
            
            <div class="card" style="background-color: #d1fae5; border-left: 4px solid #10b981; display: flex; align-items: center;">
                <div style="margin-right: 1rem; font-size: 2rem; color: #10b981;">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <p style="font-size: 0.875rem; color: #4b5563;">Upcoming Appointments</p>
                    <p style="font-size: 1.5rem; font-weight: bold;"><?php echo $appointments->num_rows; ?></p>
                </div>
            </div>
            
            <?php if ($user_type === 'owner'): ?>
                <div class="card" style="background-color: #fef3c7; border-left: 4px solid #d97706; display: flex; align-items: center;">
                    <div style="margin-right: 1rem; font-size: 2rem; color: #d97706;">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div>
                        <p style="font-size: 0.875rem; color: #4b5563;">Pending Reminders</p>
                        <p style="font-size: 1.5rem; font-weight: bold;"><?php echo $reminders->num_rows; ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="card" style="background-color: #fee2e2; border-left: 4px solid #ef4444; display: flex; align-items: center;">
                    <div style="margin-right: 1rem; font-size: 2rem; color: #ef4444;">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <div>
                        <p style="font-size: 0.875rem; color: #4b5563;">Today's Appointments</p>
                        <?php
                        $today_sql = "SELECT COUNT(*) as count FROM appointments WHERE vet_id = ? AND appointment_date = CURDATE()";
                        $today_stmt = $conn->prepare($today_sql);
                        $today_stmt->bind_param("i", $user_id);
                        $today_stmt->execute();
                        $today_count = $today_stmt->get_result()->fetch_assoc()['count'];
                        ?>
                        <p style="font-size: 1.5rem; font-weight: bold;"><?php echo $today_count; ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 1.5rem;">
        <!-- Admin Section (Only for admin user) -->
        <?php if ($user_type === 'veterinary' && $user['username'] === 'admin'): ?>
        <div class="card" style="margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">
                <i class="fas fa-shield-alt mr-1"></i> Admin Tools
            </h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
                <a href="admin/index.php" style="display: flex; align-items: center; padding: 1rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; text-decoration: none; color: inherit;">
                    <div style="width: 2.5rem; height: 2.5rem; background-color: #dbeafe; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                        <i class="fas fa-tachometer-alt" style="color: #1e40af;"></i>
                    </div>
                    <div>
                        <h4 style="font-weight: bold; margin-bottom: 0.25rem;">Admin Dashboard</h4>
                        <p style="color: #6b7280; font-size: 0.875rem;">Access the admin control panel</p>
                    </div>
                </a>
                <a href="admin/manage_vet_codes.php" style="display: flex; align-items: center; padding: 1rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; text-decoration: none; color: inherit;">
                    <div style="width: 2.5rem; height: 2.5rem; background-color: #dbeafe; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                        <i class="fas fa-key" style="color: #1e40af;"></i>
                    </div>
                    <div>
                        <h4 style="font-weight: bold; margin-bottom: 0.25rem;">Manage Vet Codes</h4>
                        <p style="color: #6b7280; font-size: 0.875rem;">Generate and manage veterinarian registration codes</p>
                    </div>
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Upcoming Appointments Section -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 style="font-size: 1.25rem; font-weight: bold;">Upcoming Appointments</h2>
                <a href="<?php echo $user_type === 'owner' ? 'add_appointment.php' : 'my_appointments.php'; ?>" class="btn btn-primary" style="font-size: 0.875rem; padding: 0.25rem 0.75rem;">
                    <?php echo $user_type === 'owner' ? 'Schedule Appointment' : 'View All'; ?>
                </a>
            </div>
            
            <?php if ($appointments->num_rows > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Pet</th>
                                <th>Date & Time</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($appt = $appointments->fetch_assoc()): ?>
                                <tr>
                                    <td style="display: flex; align-items: center;">
                                        <!-- Replaced image with icon -->
                                        <div style="width: 2.5rem; height: 2.5rem; border-radius: 9999px; background-color: #e5e7eb; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                                            <i class="fas fa-paw" style="color: #9ca3af;"></i>
                                        </div>
                                        <div>
                                            <p style="font-weight: 600;"><?php echo htmlspecialchars($appt['pet_name']); ?></p>
                                            <p style="font-size: 0.75rem; color: #6b7280;"><?php echo htmlspecialchars($appt['species']); ?> - <?php echo htmlspecialchars($appt['breed']); ?></p>
                                        </div>
                                    </td>
                                    <td>
                                        <p style="font-weight: 600;"><?php echo date('M d, Y', strtotime($appt['appointment_date'])); ?></p>
                                        <?php if (isset($appt['appointment_time'])): ?>
                                            <p style="font-size: 0.75rem; color: #6b7280;"><?php echo date('h:i A', strtotime($appt['appointment_time'])); ?></p>
                                        <?php endif; ?>
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
                <div style="text-align: center; padding: 2rem 0;">
                    <div style="font-size: 3rem; color: #d1d5db; margin-bottom: 1rem;">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <p style="color: #6b7280; margin-bottom: 1rem;">No upcoming appointments found.</p>
                    <?php if ($user_type === 'owner'): ?>
                        <a href="add_appointment.php" class="btn btn-primary">Schedule an Appointment</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($user_type === 'owner'): ?>
            <!-- Reminders Section -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h2 style="font-size: 1.25rem; font-weight: bold;">Upcoming Reminders</h2>
                    <a href="add_reminder.php" class="btn btn-primary" style="font-size: 0.875rem; padding: 0.25rem 0.75rem;">
                        Add Reminder
                    </a>
                </div>
                
                <?php if ($reminders->num_rows > 0): ?>
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Pet</th>
                                    <th>Due Date</th>
                                    <th>Title</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reminder = $reminders->fetch_assoc()): ?>
                                    <tr>
                                        <td style="display: flex; align-items: center;">
                                            <!-- Replaced image with icon -->
                                            <div style="width: 2.5rem; height: 2.5rem; border-radius: 9999px; background-color: #e5e7eb; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                                                <i class="fas fa-paw" style="color: #9ca3af;"></i>
                                            </div>
                                            <div>
                                                <p style="font-weight: 600;"><?php echo htmlspecialchars($reminder['pet_name']); ?></p>
                                                <p style="font-size: 0.75rem; color: #6b7280;"><?php echo htmlspecialchars($reminder['species']); ?></p>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                            $due_date = new DateTime($reminder['reminder_date']);
                                            $today = new DateTime();
                                            $interval = $today->diff($due_date);
                                            $days_remaining = $interval->days;
                                            $is_overdue = $due_date < $today;
                                            ?>
                                            <p style="font-weight: 600;"><?php echo date('M d, Y', strtotime($reminder['reminder_date'])); ?></p>
                                            <p style="font-size: 0.75rem; <?php echo $is_overdue ? 'color: #ef4444;' : 'color: #6b7280;'; ?>">
                                                <?php 
                                                if ($is_overdue) {
                                                    echo "Overdue by {$days_remaining} day" . ($days_remaining > 1 ? 's' : '');
                                                } else {
                                                    echo "In {$days_remaining} day" . ($days_remaining > 1 ? 's' : '');
                                                }
                                                ?>
                                            </p>
                                        </td>
                                        <td><?php echo htmlspecialchars($reminder['title']); ?></td>
                                        <td>
                                            <form method="post" action="mark_reminder.php" style="display: inline;">
                                                <input type="hidden" name="reminder_id" value="<?php echo $reminder['id']; ?>">
                                                <button type="submit" name="mark_complete" class="btn btn-success" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                                    Mark Complete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem 0;">
                        <div style="font-size: 3rem; color: #d1d5db; margin-bottom: 1rem;">
                            <i class="fas fa-bell-slash"></i>
                        </div>
                        <p style="color: #6b7280; margin-bottom: 1rem;">No upcoming reminders found.</p>
                        <a href="add_reminder.php" class="btn btn-primary">Add a Reminder</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Recent Patients Section for Veterinarians -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h2 style="font-size: 1.25rem; font-weight: bold;">Recent Patients</h2>
                    <a href="my_patients.php" class="btn btn-primary" style="font-size: 0.875rem; padding: 0.25rem 0.75rem;">
                        View All Patients
                    </a>
                </div>
                
                <?php
                // Removed p.image_path from the query
                $patients_sql = "SELECT DISTINCT p.id, p.name, p.species, p.breed, MAX(a.appointment_date) as last_visit 
                                FROM pets p 
                                JOIN appointments a ON p.id = a.pet_id 
                                WHERE a.vet_id = ? 
                                GROUP BY p.id, p.name, p.species, p.breed 
                                ORDER BY last_visit DESC 
                                LIMIT 5";
                $patients_stmt = $conn->prepare($patients_sql);
                $patients_stmt->bind_param("i", $user_id);
                $patients_stmt->execute();
                $patients = $patients_stmt->get_result();
                
                if ($patients->num_rows > 0):
                ?>
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Pet</th>
                                    <th>Owner</th>
                                    <th>Last Visit</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($patient = $patients->fetch_assoc()): 
                                    // Get owner info
                                    $owner_sql = "SELECT username FROM users WHERE id = (SELECT owner_id FROM pets WHERE id = ?)";
                                    $owner_stmt = $conn->prepare($owner_sql);
                                    $owner_stmt->bind_param("i", $patient['id']);
                                    $owner_stmt->execute();
                                    $owner = $owner_stmt->get_result()->fetch_assoc();
                                ?>
                                    <tr>
                                        <td style="display: flex; align-items: center;">
                                            <!-- Replaced image with icon -->
                                            <div style="width: 2.5rem; height: 2.5rem; border-radius: 9999px; background-color: #e5e7eb; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                                                <i class="fas fa-paw" style="color: #9ca3af;"></i>
                                            </div>
                                            <div>
                                                <p style="font-weight: 600;"><?php echo htmlspecialchars($patient['name']); ?></p>
                                                <p style="font-size: 0.75rem; color: #6b7280;"><?php echo htmlspecialchars($patient['species']); ?> - <?php echo htmlspecialchars($patient['breed']); ?></p>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($owner['username']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($patient['last_visit'])); ?></td>
                                        <td>
                                            <a href="view_pet.php?id=<?php echo $patient['id']; ?>" style="color: #2563eb; text-decoration: none; margin-right: 0.5rem;">View</a>
                                            <a href="add_health_record.php?pet_id=<?php echo $patient['id']; ?>" style="color: #10b981; text-decoration: none;">Add Record</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem 0;">
                        <div style="font-size: 3rem; color: #d1d5db; margin-bottom: 1rem;">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <p style="color: #6b7280;">No patients found. They will appear here once you have appointments.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
} // Close the else statement from line 112

// Close dashboard container
echo '</div>';

// Include footer
include 'includes/footer.php';
?>
