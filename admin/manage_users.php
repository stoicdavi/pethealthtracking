<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'veterinary' || $_SESSION['username'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Set page title
$page_title = "Manage Users";

// Process user actions
$success_message = '';
$error_message = '';

// Handle user status toggle (activate/deactivate)
if (isset($_POST['toggle_status'])) {
    // Check if is_active column exists
    $check_column_sql = "SHOW COLUMNS FROM users LIKE 'is_active'";
    $column_result = $conn->query($check_column_sql);
    
    if ($column_result->num_rows == 0) {
        $error_message = "Cannot toggle user status. The is_active column does not exist in the users table. Please run the update_users_table.php script first.";
    } else {
        $user_id = $_POST['user_id'] ?? 0;
        $new_status = $_POST['new_status'] ?? '';
        
        if ($user_id && ($new_status === 'active' || $new_status === 'inactive')) {
            $is_active = ($new_status === 'active') ? 1 : 0;
            
            // Don't allow deactivating the admin account
            if ($user_id == $_SESSION['user_id'] && $is_active == 0) {
                $error_message = "You cannot deactivate your own admin account.";
            } else {
                $update_sql = "UPDATE users SET is_active = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $is_active, $user_id);
                
                if ($update_stmt->execute()) {
                    $status_text = $is_active ? 'activated' : 'deactivated';
                    $success_message = "User has been $status_text successfully.";
                } else {
                    $error_message = "Failed to update user status: " . $conn->error;
                }
            }
        }
    }
}

// Handle user role change
if (isset($_POST['change_role'])) {
    $user_id = $_POST['user_id'] ?? 0;
    $new_role = $_POST['new_role'] ?? '';
    
    if ($user_id && ($new_role === 'owner' || $new_role === 'veterinary')) {
        // Don't allow changing the admin's role
        if ($user_id == $_SESSION['user_id']) {
            $error_message = "You cannot change your own admin role.";
        } else {
            $update_sql = "UPDATE users SET user_type = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $new_role, $user_id);
            
            if ($update_stmt->execute()) {
                $success_message = "User role has been updated to " . ucfirst($new_role) . ".";
            } else {
                $error_message = "Failed to update user role: " . $conn->error;
            }
        }
    }
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'] ?? 0;
    
    if ($user_id) {
        // Don't allow deleting the admin account
        if ($user_id == $_SESSION['user_id']) {
            $error_message = "You cannot delete your own admin account.";
        } else {
            // Check if user has any pets or appointments
            $check_sql = "SELECT 
                            (SELECT COUNT(*) FROM pets WHERE owner_id = ?) as pet_count,
                            (SELECT COUNT(*) FROM appointments WHERE vet_id = ?) as appt_count";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $user_id, $user_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result()->fetch_assoc();
            
            if ($result['pet_count'] > 0 || $result['appt_count'] > 0) {
                $error_message = "Cannot delete user with associated pets or appointments. Deactivate the account instead.";
            } else {
                $delete_sql = "DELETE FROM users WHERE id = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("i", $user_id);
                
                if ($delete_stmt->execute()) {
                    $success_message = "User has been deleted successfully.";
                } else {
                    $error_message = "Failed to delete user: " . $conn->error;
                }
            }
        }
    }
}

// Handle password reset
if (isset($_POST['reset_password'])) {
    $user_id = $_POST['user_id'] ?? 0;
    
    if ($user_id) {
        // Generate a random password
        $new_password = substr(md5(uniqid(mt_rand(), true)), 0, 8);
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $update_sql = "UPDATE users SET password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Password has been reset. New password: $new_password";
        } else {
            $error_message = "Failed to reset password: " . $conn->error;
        }
    }
}

// Fetch users with pagination and filtering
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search and filter parameters
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build the query
$query = "SELECT * FROM users WHERE 1=1";
$count_query = "SELECT COUNT(*) as total FROM users WHERE 1=1";

// Prepare parameter arrays for both queries
$query_params = [];
$query_types = "";
$count_params = [];
$count_types = "";

// Add search condition if provided
if (!empty($search)) {
    $search_term = "%$search%";
    $query .= " AND (username LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $count_query .= " AND (username LIKE ? OR email LIKE ? OR phone LIKE ?)";
    
    // For main query
    $query_params[] = $search_term;
    $query_params[] = $search_term;
    $query_params[] = $search_term;
    $query_types .= "sss";
    
    // For count query
    $count_params[] = $search_term;
    $count_params[] = $search_term;
    $count_params[] = $search_term;
    $count_types .= "sss";
}

// Add role filter if provided
if (!empty($role_filter)) {
    $query .= " AND user_type = ?";
    $count_query .= " AND user_type = ?";
    
    // For main query
    $query_params[] = $role_filter;
    $query_types .= "s";
    
    // For count query
    $count_params[] = $role_filter;
    $count_types .= "s";
}

// Add status filter if provided
if ($status_filter !== '') {
    // Check if is_active column exists
    $check_column_sql = "SHOW COLUMNS FROM users LIKE 'is_active'";
    $column_result = $conn->query($check_column_sql);
    
    if ($column_result->num_rows > 0) {
        $is_active = ($status_filter === 'active') ? 1 : 0;
        $query .= " AND is_active = ?";
        $count_query .= " AND is_active = ?";
        
        // For main query
        $query_params[] = $is_active;
        $query_types .= "i";
        
        // For count query
        $count_params[] = $is_active;
        $count_types .= "i";
    }
}

// Add order by and limit to main query only
$query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$query_params[] = $per_page;
$query_params[] = $offset;
$query_types .= "ii";

// Prepare and execute the count query
$count_stmt = $conn->prepare($count_query);
if (!empty($count_params)) {
    $ref_count_params = [];
    $ref_count_params[] = &$count_types;
    foreach ($count_params as $key => $value) {
        $ref_count_params[] = &$count_params[$key];
    }
    call_user_func_array([$count_stmt, 'bind_param'], $ref_count_params);
}
$count_stmt->execute();
$total_users = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_users / $per_page);

// Prepare and execute the main query
$stmt = $conn->prepare($query);
if (!empty($query_params)) {
    $ref_query_params = [];
    $ref_query_params[] = &$query_types;
    foreach ($query_params as $key => $value) {
        $ref_query_params[] = &$query_params[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $ref_query_params);
}
$stmt->execute();
$users = $stmt->get_result();

// Get user statistics
$stats_sql = "SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN user_type = 'owner' THEN 1 ELSE 0 END) as owner_count,
                SUM(CASE WHEN user_type = 'veterinary' THEN 1 ELSE 0 END) as vet_count";

// Check if is_active column exists
$check_column_sql = "SHOW COLUMNS FROM users LIKE 'is_active'";
$column_result = $conn->query($check_column_sql);
if ($column_result->num_rows > 0) {
    $stats_sql .= ",
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_count";
} else {
    $stats_sql .= ",
                COUNT(*) as active_count,
                0 as inactive_count";
}

$stats_sql .= " FROM users";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

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
            <h1>Manage Users</h1>
            <div class="admin-user">
                <div class="admin-user-avatar">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="../logout.php" class="btn btn-sm btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <?php if (!empty($success_message)): ?>
            <div class="flash-message flash-success">
                <p><i class="fas fa-check-circle"></i> <?php echo $success_message; ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="flash-message flash-error">
                <p><i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?></p>
            </div>
        <?php endif; ?>
        
        <!-- User Statistics -->
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(59, 130, 246, 0.1);">
                    <i class="fas fa-users" style="color: #3b82f6;"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Users</h3>
                    <div class="stat-numbers">
                        <span class="stat-main"><?php echo $stats['total_users']; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(16, 185, 129, 0.1);">
                    <i class="fas fa-user" style="color: #10b981;"></i>
                </div>
                <div class="stat-details">
                    <h3>Pet Owners</h3>
                    <div class="stat-numbers">
                        <span class="stat-main"><?php echo $stats['owner_count']; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(245, 158, 11, 0.1);">
                    <i class="fas fa-user-md" style="color: #f59e0b;"></i>
                </div>
                <div class="stat-details">
                    <h3>Veterinarians</h3>
                    <div class="stat-numbers">
                        <span class="stat-main"><?php echo $stats['vet_count']; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(79, 70, 229, 0.1);">
                    <i class="fas fa-user-check" style="color: #4f46e5;"></i>
                </div>
                <div class="stat-details">
                    <h3>Active/Inactive</h3>
                    <div class="stat-numbers">
                        <span class="stat-main"><?php echo $stats['active_count']; ?>/<?php echo $stats['inactive_count']; ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Search and Filter Form -->
        <div class="admin-panel">
            <h2>Search and Filter</h2>
            
            <form method="GET" action="">
                <div class="admin-form-row">
                    <div class="admin-form-col">
                        <label for="search">Search</label>
                        <input type="text" id="search" name="search" placeholder="Search by username, email or phone" 
                               value="<?php echo htmlspecialchars($search); ?>" class="admin-form-control">
                    </div>
                    
                    <div class="admin-form-col">
                        <label for="role">Role</label>
                        <select id="role" name="role" class="admin-form-control">
                            <option value="">All Roles</option>
                            <option value="owner" <?php echo $role_filter === 'owner' ? 'selected' : ''; ?>>Pet Owner</option>
                            <option value="veterinary" <?php echo $role_filter === 'veterinary' ? 'selected' : ''; ?>>Veterinarian</option>
                        </select>
                    </div>
                    
                    <div class="admin-form-col">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="admin-form-control">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="admin-form-col" style="display: flex; align-items: flex-end;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <?php if (!empty($search) || !empty($role_filter) || $status_filter !== ''): ?>
                            <a href="manage_users.php" class="btn btn-secondary" style="margin-left: 0.5rem;">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Users Table -->
        <div class="admin-panel">
            <h2>User List</h2>
            
            <?php if ($users->num_rows > 0): ?>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : '-'; ?></td>
                                    <td>
                                        <span class="badge <?php echo $user['user_type'] === 'veterinary' ? 'badge-primary' : 'badge-success'; ?>">
                                            <?php echo ucfirst($user['user_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        // Check if is_active column exists in the result
                                        if (array_key_exists('is_active', $user)): 
                                        ?>
                                            <span class="badge <?php echo $user['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <!-- Toggle Status Button -->
                                            <?php 
                                            // Check if is_active column exists
                                            $check_column_sql = "SHOW COLUMNS FROM users LIKE 'is_active'";
                                            $column_result = $conn->query($check_column_sql);
                                            if ($column_result->num_rows > 0): 
                                            ?>
                                            <form method="post" action="" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <?php if (isset($user['is_active']) && $user['is_active']): ?>
                                                    <input type="hidden" name="new_status" value="inactive">
                                                    <button type="submit" name="toggle_status" class="btn btn-warning btn-sm" 
                                                            data-confirm="Are you sure you want to deactivate this user?">
                                                        <i class="fas fa-user-slash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <input type="hidden" name="new_status" value="active">
                                                    <button type="submit" name="toggle_status" class="btn btn-success btn-sm">
                                                        <i class="fas fa-user-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                            <?php endif; ?>
                                            
                                            <!-- Change Role Button -->
                                            <form method="post" action="" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <?php if ($user['user_type'] === 'owner'): ?>
                                                    <input type="hidden" name="new_role" value="veterinary">
                                                    <button type="submit" name="change_role" class="btn btn-info btn-sm" 
                                                            data-confirm="Change this user to Veterinarian?">
                                                        <i class="fas fa-user-md"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <input type="hidden" name="new_role" value="owner">
                                                    <button type="submit" name="change_role" class="btn btn-info btn-sm" 
                                                            data-confirm="Change this user to Pet Owner?">
                                                        <i class="fas fa-user"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                            
                                            <!-- Reset Password Button -->
                                            <form method="post" action="" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" name="reset_password" class="btn btn-secondary btn-sm" 
                                                        data-confirm="Are you sure you want to reset this user's password?">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                            </form>
                                            
                                            <!-- Delete User Button -->
                                            <form method="post" action="" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" name="delete_user" class="btn btn-danger btn-sm" 
                                                        data-confirm="Are you sure you want to delete this user? This action cannot be undone.">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>" class="pagination-item">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($start_page > 1) {
                            echo '<a href="?page=1&search=' . urlencode($search) . '&role=' . urlencode($role_filter) . '&status=' . urlencode($status_filter) . '" class="pagination-item">1</a>';
                            if ($start_page > 2) {
                                echo '<span class="pagination-ellipsis">...</span>';
                            }
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++) {
                            echo '<a href="?page=' . $i . '&search=' . urlencode($search) . '&role=' . urlencode($role_filter) . '&status=' . urlencode($status_filter) . '" class="pagination-item ' . ($i === $page ? 'active' : '') . '">' . $i . '</a>';
                        }
                        
                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<span class="pagination-ellipsis">...</span>';
                            }
                            echo '<a href="?page=' . $total_pages . '&search=' . urlencode($search) . '&role=' . urlencode($role_filter) . '&status=' . urlencode($status_filter) . '" class="pagination-item">' . $total_pages . '</a>';
                        }
                        ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>" class="pagination-item">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem 0;">
                    <div style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem;">
                        No users found
                    </h3>
                    <p style="color: #6b7280; margin-bottom: 1.5rem;">
                        <?php if (!empty($search) || !empty($role_filter) || $status_filter !== ''): ?>
                            No users match your search criteria. Try adjusting your filters.
                        <?php else: ?>
                            There are no users in the system yet.
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($search) || !empty($role_filter) || $status_filter !== ''): ?>
                        <a href="manage_users.php" class="btn btn-primary">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .action-buttons .btn {
        padding: 0.25rem 0.5rem;
    }
</style>

<?php
// Include footer
include '../includes/admin_footer.php';
?>
