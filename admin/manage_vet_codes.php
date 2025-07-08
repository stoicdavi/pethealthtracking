<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'veterinary' || $_SESSION['username'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Set page title
$page_title = "Manage Veterinarian Codes";

// Process form submission for generating new codes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_code'])) {
    $description = $_POST['description'] ?? '';
    $count = $_POST['count'] ?? 1;
    $count = max(1, min(10, intval($count))); // Limit between 1 and 10
    
    $success_count = 0;
    $generated_codes = [];
    
    for ($i = 0; $i < $count; $i++) {
        // Generate a unique code
        $code = 'VET-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $generated_codes[] = $code;
        
        // Insert the code
        $insert_sql = "INSERT INTO vet_codes (code, description) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ss", $code, $description);
        
        if ($insert_stmt->execute()) {
            $success_count++;
        }
    }
    
    if ($success_count > 0) {
        $success_message = "Successfully generated $success_count new vet code(s).";
        $codes_list = implode(", ", $generated_codes);
    } else {
        $error_message = "Failed to generate vet codes.";
    }
}

// Process form submission for deleting codes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_code'])) {
    $code_id = $_POST['code_id'] ?? 0;
    
    // Only delete unused codes
    $delete_sql = "DELETE FROM vet_codes WHERE id = ? AND is_used = 0";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $code_id);
    
    if ($delete_stmt->execute() && $delete_stmt->affected_rows > 0) {
        $success_message = "Vet code deleted successfully.";
    } else {
        $error_message = "Failed to delete vet code. It may be in use.";
    }
}

// Fetch all vet codes with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM vet_codes";
$count_result = $conn->query($count_sql);
$total_codes = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_codes / $per_page);

// Fetch codes for current page
$codes_sql = "SELECT vc.*, u.username 
              FROM vet_codes vc 
              LEFT JOIN users u ON vc.used_by = u.id 
              ORDER BY vc.created_at DESC
              LIMIT ? OFFSET ?";
$codes_stmt = $conn->prepare($codes_sql);
$codes_stmt->bind_param("ii", $per_page, $offset);
$codes_stmt->execute();
$codes_result = $codes_stmt->get_result();

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
            <h1>Manage Veterinarian Codes</h1>
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
        
        <?php if (isset($success_message)): ?>
            <div class="flash-message flash-success">
                <p><i class="fas fa-check-circle"></i> <?php echo $success_message; ?></p>
                <?php if (isset($codes_list)): ?>
                    <p style="margin-top: 0.5rem; font-size: 0.875rem;">Generated codes: <strong><?php echo $codes_list; ?></strong></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="flash-message flash-error">
                <p><i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Generate New Codes Form -->
        <div class="admin-panel">
            <h2>Generate New Veterinarian Codes</h2>
            
            <form method="post" action="">
                <div class="admin-form-row">
                    <div class="admin-form-col">
                        <label for="description">Description (Optional)</label>
                        <input type="text" id="description" name="description" placeholder="e.g., For Dr. Smith's clinic" 
                               class="admin-form-control">
                    </div>
                    
                    <div class="admin-form-col" style="max-width: 150px;">
                        <label for="count">Count</label>
                        <input type="number" id="count" name="count" value="1" min="1" max="10" 
                               class="admin-form-control">
                    </div>
                    
                    <div class="admin-form-col" style="display: flex; align-items: flex-end;">
                        <button type="submit" name="generate_code" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Generate Code(s)
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Existing Codes Table -->
        <div class="admin-panel">
            <h2>Existing Veterinarian Codes</h2>
            
            <?php if ($codes_result->num_rows > 0): ?>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Used By</th>
                                <th>Used At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($code = $codes_result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <span style="font-family: monospace; font-weight: bold;"><?php echo htmlspecialchars($code['code']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($code['description'] ?? ''); ?></td>
                                    <td>
                                        <?php if ($code['is_used']): ?>
                                            <span class="badge badge-danger">Used</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Available</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($code['created_at'])); ?></td>
                                    <td><?php echo $code['username'] ? htmlspecialchars($code['username']) : '-'; ?></td>
                                    <td><?php echo $code['used_at'] ? date('M d, Y', strtotime($code['used_at'])) : '-'; ?></td>
                                    <td>
                                        <?php if (!$code['is_used']): ?>
                                            <form method="post" action="" style="display: inline;">
                                                <input type="hidden" name="code_id" value="<?php echo $code['id']; ?>">
                                                <button type="submit" name="delete_code" class="btn btn-danger btn-sm" 
                                                        data-confirm="Are you sure you want to delete this code?">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
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
                            <a href="?page=<?php echo $page - 1; ?>" class="pagination-item">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($start_page > 1) {
                            echo '<a href="?page=1" class="pagination-item">1</a>';
                            if ($start_page > 2) {
                                echo '<span class="pagination-ellipsis">...</span>';
                            }
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++) {
                            echo '<a href="?page=' . $i . '" class="pagination-item ' . ($i === $page ? 'active' : '') . '">' . $i . '</a>';
                        }
                        
                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<span class="pagination-ellipsis">...</span>';
                            }
                            echo '<a href="?page=' . $total_pages . '" class="pagination-item">' . $total_pages . '</a>';
                        }
                        ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="pagination-item">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem 0;">
                    <div style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;">
                        <i class="fas fa-key"></i>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem;">
                        No veterinarian codes found
                    </h3>
                    <p style="color: #6b7280; margin-bottom: 1.5rem;">
                        Generate some codes using the form above.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/admin_footer.php';
?>
