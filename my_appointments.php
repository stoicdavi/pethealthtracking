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
$user_type = $_SESSION['user_type'] ?? '';

// Set page title
$page_title = "My Appointments";

// Handle appointment cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment'])) {
    $appointment_id = $_POST['appointment_id'] ?? 0;
    
    if ($appointment_id > 0) {
        // Check if the user has permission to cancel this appointment
        if ($user_type === 'owner') {
            $check_sql = "SELECT a.* FROM appointments a 
                         JOIN pets p ON a.pet_id = p.id 
                         WHERE a.id = ? AND p.owner_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $appointment_id, $user_id);
        } else {
            $check_sql = "SELECT * FROM appointments WHERE id = ? AND vet_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $appointment_id, $user_id);
        }
        
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update appointment status to cancelled
            $update_sql = "UPDATE appointments SET status = 'cancelled' WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $appointment_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['success_message'] = "Appointment cancelled successfully.";
            } else {
                $_SESSION['error_message'] = "Error cancelling appointment: " . $conn->error;
            }
        } else {
            $_SESSION['error_message'] = "You don't have permission to cancel this appointment.";
        }
        
        // Redirect to refresh the page and avoid form resubmission
        header("Location: my_appointments.php");
        exit();
    }
}

// Get appointments based on user type
if ($user_type === 'owner') {
    // For pet owners, get appointments for their pets
    $sql = "SELECT a.*, p.name as pet_name, p.species, p.breed, 
            u.username as vet_name 
            FROM appointments a 
            JOIN pets p ON a.pet_id = p.id 
            LEFT JOIN users u ON a.vet_id = u.id 
            WHERE p.owner_id = ? 
            ORDER BY a.appointment_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
} else {
    // For veterinarians, get appointments assigned to them
    $sql = "SELECT a.*, p.name as pet_name, p.species, p.breed, 
            u.username as owner_name 
            FROM appointments a 
            JOIN pets p ON a.pet_id = p.id 
            JOIN users u ON p.owner_id = u.id 
            WHERE a.vet_id = ? OR a.vet_id IS NULL 
            ORDER BY a.appointment_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$appointments = $stmt->get_result();

// Include header
include 'includes/header.php';
?>

<div style="max-width: 64rem; margin: 0 auto; padding: 1rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.875rem; font-weight: bold;">My Appointments</h1>
        <?php if ($user_type === 'owner'): ?>
            <a href="add_appointment.php" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Schedule New Appointment
            </a>
        <?php endif; ?>
    </div>
    
    <!-- Filter Controls -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <form method="get" action="my_appointments.php" style="display: flex; flex-wrap: wrap; gap: 1rem;">
            <div style="flex: 1; min-width: 200px;">
                <label for="status" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                    Filter by Status
                </label>
                <select name="status" id="status" onchange="this.form.submit()" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo isset($_GET['status']) && $_GET['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo isset($_GET['status']) && $_GET['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="confirmed" <?php echo isset($_GET['status']) && $_GET['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="completed" <?php echo isset($_GET['status']) && $_GET['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo isset($_GET['status']) && $_GET['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            
            <div style="flex: 1; min-width: 200px;">
                <label for="date_range" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                    Filter by Date
                </label>
                <select name="date_range" id="date_range" onchange="this.form.submit()" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    <option value="">All Dates</option>
                    <option value="upcoming" <?php echo isset($_GET['date_range']) && $_GET['date_range'] === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                    <option value="past" <?php echo isset($_GET['date_range']) && $_GET['date_range'] === 'past' ? 'selected' : ''; ?>>Past</option>
                    <option value="today" <?php echo isset($_GET['date_range']) && $_GET['date_range'] === 'today' ? 'selected' : ''; ?>>Today</option>
                    <option value="week" <?php echo isset($_GET['date_range']) && $_GET['date_range'] === 'week' ? 'selected' : ''; ?>>This Week</option>
                    <option value="month" <?php echo isset($_GET['date_range']) && $_GET['date_range'] === 'month' ? 'selected' : ''; ?>>This Month</option>
                </select>
            </div>
            
            <?php if ($user_type === 'owner' && $appointments->num_rows > 0): ?>
                <div style="flex: 1; min-width: 200px;">
                    <label for="pet_id" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                        Filter by Pet
                    </label>
                    <select name="pet_id" id="pet_id" onchange="this.form.submit()" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                        <option value="">All Pets</option>
                        <?php
                        // Get unique pets from appointments
                        $pets_sql = "SELECT DISTINCT p.id, p.name FROM pets p 
                                    JOIN appointments a ON p.id = a.pet_id 
                                    WHERE p.owner_id = ?";
                        $pets_stmt = $conn->prepare($pets_sql);
                        $pets_stmt->bind_param("i", $user_id);
                        $pets_stmt->execute();
                        $pets_result = $pets_stmt->get_result();
                        
                        while ($pet = $pets_result->fetch_assoc()):
                        ?>
                            <option value="<?php echo $pet['id']; ?>" <?php echo isset($_GET['pet_id']) && $_GET['pet_id'] == $pet['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($pet['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            <?php endif; ?>
            
            <div style="align-self: flex-end;">
                <button type="submit" name="filter" class="btn btn-primary" style="padding: 0.5rem 1rem;">
                    <i class="fas fa-filter mr-1"></i> Apply Filters
                </button>
                <a href="my_appointments.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-left: 0.5rem;">
                    <i class="fas fa-sync-alt mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
    
    <!-- Appointments List -->
    <div class="card">
        <?php
        // Apply filters if set
        $filtered_appointments = [];
        $appointments->data_seek(0); // Reset result pointer
        
        while ($appointment = $appointments->fetch_assoc()) {
            $include = true;
            
            // Filter by status
            if (isset($_GET['status']) && !empty($_GET['status'])) {
                if ($appointment['status'] !== $_GET['status']) {
                    $include = false;
                }
            }
            
            // Filter by date range
            if (isset($_GET['date_range']) && !empty($_GET['date_range'])) {
                $today = date('Y-m-d');
                $appointment_date = $appointment['appointment_date'];
                
                switch ($_GET['date_range']) {
                    case 'upcoming':
                        if ($appointment_date < $today) {
                            $include = false;
                        }
                        break;
                    case 'past':
                        if ($appointment_date >= $today) {
                            $include = false;
                        }
                        break;
                    case 'today':
                        if ($appointment_date !== $today) {
                            $include = false;
                        }
                        break;
                    case 'week':
                        $week_start = date('Y-m-d', strtotime('monday this week'));
                        $week_end = date('Y-m-d', strtotime('sunday this week'));
                        if ($appointment_date < $week_start || $appointment_date > $week_end) {
                            $include = false;
                        }
                        break;
                    case 'month':
                        $month_start = date('Y-m-01');
                        $month_end = date('Y-m-t');
                        if ($appointment_date < $month_start || $appointment_date > $month_end) {
                            $include = false;
                        }
                        break;
                }
            }
            
            // Filter by pet
            if (isset($_GET['pet_id']) && !empty($_GET['pet_id'])) {
                if ($appointment['pet_id'] != $_GET['pet_id']) {
                    $include = false;
                }
            }
            
            if ($include) {
                $filtered_appointments[] = $appointment;
            }
        }
        
        if (count($filtered_appointments) > 0):
        ?>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Pet</th>
                            <?php if ($user_type === 'owner'): ?>
                                <th>Veterinarian</th>
                            <?php else: ?>
                                <th>Owner</th>
                            <?php endif; ?>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filtered_appointments as $appointment): ?>
                            <tr>
                                <td>
                                    <p style="font-weight: 600;"><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></p>
                                    <?php if (!empty($appointment['appointment_time'])): ?>
                                        <p style="font-size: 0.75rem; color: #6b7280;"><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <p style="font-weight: 600;"><?php echo htmlspecialchars($appointment['pet_name']); ?></p>
                                    <p style="font-size: 0.75rem; color: #6b7280;"><?php echo htmlspecialchars($appointment['species']); ?> - <?php echo htmlspecialchars($appointment['breed']); ?></p>
                                </td>
                                <?php if ($user_type === 'owner'): ?>
                                    <td>
                                        <?php if (!empty($appointment['vet_name'])): ?>
                                            Dr. <?php echo htmlspecialchars($appointment['vet_name']); ?>
                                        <?php else: ?>
                                            <span style="color: #6b7280;">Not assigned</span>
                                        <?php endif; ?>
                                    </td>
                                <?php else: ?>
                                    <td><?php echo htmlspecialchars($appointment['owner_name']); ?></td>
                                <?php endif; ?>
                                <td><?php echo htmlspecialchars($appointment['reason']); ?></td>
                                <td>
                                    <span class="badge <?php 
                                        echo $appointment['status'] === 'confirmed' ? 'badge-confirmed' : 
                                            ($appointment['status'] === 'pending' ? 'badge-pending' : 
                                            ($appointment['status'] === 'completed' ? 'badge-completed' : 'badge-cancelled')); 
                                    ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view_appointment.php?id=<?php echo $appointment['id']; ?>" class="btn btn-primary" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; margin-bottom: 0.25rem; display: inline-block;">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>
                                    
                                    <?php if ($appointment['status'] !== 'cancelled' && $appointment['status'] !== 'completed'): ?>
                                        <?php if (strtotime($appointment['appointment_date']) > time()): ?>
                                            <form method="post" action="my_appointments.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                <button type="submit" name="cancel_appointment" class="btn btn-danger" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                                    <i class="fas fa-times mr-1"></i> Cancel
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($user_type !== 'owner'): ?>
                                            <a href="update_appointment.php?id=<?php echo $appointment['id']; ?>" class="btn btn-success" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; margin-top: 0.25rem; display: inline-block;">
                                                <i class="fas fa-check mr-1"></i> Update Status
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem 0;">
                <div style="font-size: 3rem; color: #d1d5db; margin-bottom: 1rem;">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <p style="color: #6b7280; margin-bottom: 1rem;">No appointments found matching your filters.</p>
                <?php if ($user_type === 'owner'): ?>
                    <a href="add_appointment.php" class="btn btn-primary">Schedule an Appointment</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
