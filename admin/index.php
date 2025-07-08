<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'veterinary' || $_SESSION['username'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Set page title
$page_title = "Admin Dashboard";

// Get admin user information
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Get system statistics
// Total users count
$users_sql = "SELECT COUNT(*) as count, user_type FROM users GROUP BY user_type";
$users_result = $conn->query($users_sql);
$user_counts = [];
while ($row = $users_result->fetch_assoc()) {
    $user_counts[$row['user_type']] = $row['count'];
}

// Total pets count
$pets_sql = "SELECT COUNT(*) as count FROM pets";
$pets_result = $conn->query($pets_sql);
$pet_count = $pets_result->fetch_assoc()['count'];

// Total appointments count
$appt_sql = "SELECT COUNT(*) as count FROM appointments";
$appt_result = $conn->query($appt_sql);
$appointment_count = $appt_result->fetch_assoc()['count'];

// Vet codes statistics
$codes_sql = "SELECT 
                COUNT(*) as total_codes,
                SUM(CASE WHEN is_used = 1 THEN 1 ELSE 0 END) as used_codes,
                SUM(CASE WHEN is_used = 0 THEN 1 ELSE 0 END) as available_codes
              FROM vet_codes";
$codes_result = $conn->query($codes_sql);
$codes_stats = $codes_result->fetch_assoc();

// Recent activity - last 5 appointments
$recent_activity_sql = "SELECT a.*, p.name as pet_name, u.username as owner_name, v.username as vet_name
                        FROM appointments a
                        JOIN pets p ON a.pet_id = p.id
                        JOIN users u ON p.owner_id = u.id
                        LEFT JOIN users v ON a.vet_id = v.id
                        ORDER BY a.created_at DESC
                        LIMIT 5";
$recent_activity = $conn->query($recent_activity_sql);

// Include header
include '../includes/admin_header.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <?php include 'sidebar.php'; ?>
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <button class="admin-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <h1>Admin Dashboard</h1>
            <div class="admin-user">
                <div class="admin-user-avatar">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
                <span><?php echo htmlspecialchars($user['username']); ?></span>
                <a href="../logout.php" class="btn btn-sm btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(59, 130, 246, 0.1);">
                    <i class="fas fa-users" style="color: #3b82f6;"></i>
                </div>
                <div class="stat-details">
                    <h3>Users</h3>
                    <div class="stat-numbers">
                        <span class="stat-main"><?php echo array_sum($user_counts); ?></span>
                        <div class="stat-breakdown">
                            <span><?php echo $user_counts['owner'] ?? 0; ?> Owners</span>
                            <span><?php echo $user_counts['veterinary'] ?? 0; ?> Vets</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(16, 185, 129, 0.1);">
                    <i class="fas fa-paw" style="color: #10b981;"></i>
                </div>
                <div class="stat-details">
                    <h3>Pets</h3>
                    <div class="stat-numbers">
                        <span class="stat-main"><?php echo $pet_count; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(245, 158, 11, 0.1);">
                    <i class="fas fa-calendar-check" style="color: #f59e0b;"></i>
                </div>
                <div class="stat-details">
                    <h3>Appointments</h3>
                    <div class="stat-numbers">
                        <span class="stat-main"><?php echo $appointment_count; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(79, 70, 229, 0.1);">
                    <i class="fas fa-key" style="color: #4f46e5;"></i>
                </div>
                <div class="stat-details">
                    <h3>Vet Codes</h3>
                    <div class="stat-numbers">
                        <span class="stat-main"><?php echo $codes_stats['total_codes']; ?></span>
                        <div class="stat-breakdown">
                            <span><?php echo $codes_stats['used_codes']; ?> Used</span>
                            <span><?php echo $codes_stats['available_codes']; ?> Available</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="admin-panels">
            <div class="admin-panel">
                <h2>Quick Actions</h2>
                <div class="admin-actions">
                    <a href="manage_vet_codes.php" class="admin-action-btn">
                        <i class="fas fa-key"></i>
                        <span>Manage Vet Codes</span>
                    </a>
                    <a href="manage_users.php" class="admin-action-btn">
                        <i class="fas fa-users-cog"></i>
                        <span>Manage Users</span>
                    </a>
                    <a href="system_settings.php" class="admin-action-btn">
                        <i class="fas fa-cogs"></i>
                        <span>System Settings</span>
                    </a>
                    
                </div>
            </div>
            
            <div class="admin-panel">
                <h2>Recent Activity</h2>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Pet</th>
                                <th>Owner</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($recent_activity->num_rows > 0) {
                                while ($activity = $recent_activity->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($activity['pet_name']); ?></td>
                                <td><?php echo htmlspecialchars($activity['owner_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($activity['appointment_date'])); ?></td>
                                <td>
                                    <span class="badge <?php 
                                        echo $activity['status'] === 'confirmed' ? 'badge-primary' : 
                                            ($activity['status'] === 'pending' ? 'badge-warning' : 
                                            ($activity['status'] === 'completed' ? 'badge-success' : 'badge-danger')); 
                                    ?>">
                                        <?php echo ucfirst($activity['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="../view_appointment.php?id=<?php echo $activity['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            } else {
                                echo '<tr><td colspan="5" style="text-align: center;">No recent activity</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="admin-panel">
                <h2>Recent Registrations</h2>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent_users_sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
                            $recent_users_result = $conn->query($recent_users_sql);
                            
                            if ($recent_users_result->num_rows > 0) {
                                while ($user = $recent_users_result->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge <?php echo $user['user_type'] === 'veterinary' ? 'badge-primary' : 'badge-success'; ?>">
                                        <?php echo ucfirst($user['user_type']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                            <?php 
                                endwhile;
                            } else {
                                echo '<tr><td colspan="4" style="text-align: center;">No users found</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="admin-panel">
                <h2>System Information</h2>
                <div class="system-info-grid">
                    <div class="system-info-item">
                        <h3>PHP Version</h3>
                        <p><?php echo phpversion(); ?></p>
                    </div>
                    <div class="system-info-item">
                        <h3>MySQL Version</h3>
                        <p><?php echo $conn->server_info; ?></p>
                    </div>
                    <div class="system-info-item">
                        <h3>Server Software</h3>
                        <p><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
                    </div>
                    <div class="system-info-item">
                        <h3>Application Version</h3>
                        <p>Pet Management v1.0</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/admin_footer.php';
?>
