<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'veterinary' || $_SESSION['username'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Set page title
$page_title = "System Settings";

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
            <h1>System Settings</h1>
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
        
        <div class="admin-panel">
            <h2>Application Settings</h2>
            
            <div style="text-align: center; padding: 3rem 0;">
                <div style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;">
                    <i class="fas fa-cogs"></i>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem;">
                    System Settings Coming Soon
                </h3>
                <p style="color: #6b7280; margin-bottom: 1.5rem;">
                    This feature is currently under development. Check back later!
                </p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/admin_footer.php';
?>
