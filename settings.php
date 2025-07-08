<?php
session_start();
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Set page title
$page_title = "Account Settings";

// Get current user data
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Process form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    // Validate input
    if (empty($username) || empty($email)) {
        $error_message = "Username and email are required.";
    } else {
        // Check if email is already in use by another user
        $check_email_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $check_email_stmt = $conn->prepare($check_email_sql);
        $check_email_stmt->bind_param("si", $email, $user_id);
        $check_email_stmt->execute();
        $check_email_result = $check_email_stmt->get_result();
        
        if ($check_email_result->num_rows > 0) {
            $error_message = "Email is already in use by another account.";
        } else {
            // Update user profile
            $update_sql = "UPDATE users SET username = ?, email = ?, phone = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssi", $username, $email, $phone, $user_id);
            
            if ($update_stmt->execute()) {
                $success_message = "Profile updated successfully!";
                
                // Update session data
                $_SESSION['username'] = $username;
                
                // Refresh user data
                $user_stmt->execute();
                $user = $user_stmt->get_result()->fetch_assoc();
            } else {
                $error_message = "Error updating profile: " . $conn->error;
            }
        }
    }
}

// Process form submission for password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error_message = "New password must be at least 8 characters long.";
    } else {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $update_password_sql = "UPDATE users SET password = ? WHERE id = ?";
            $update_password_stmt = $conn->prepare($update_password_sql);
            $update_password_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_password_stmt->execute()) {
                $success_message = "Password changed successfully!";
            } else {
                $error_message = "Error changing password: " . $conn->error;
            }
        } else {
            $error_message = "Current password is incorrect.";
        }
    }
}

// Include header
include 'includes/header.php';
?>

<div style="max-width: 48rem; margin: 0 auto; padding: 1rem 0;">
    <h1 style="font-size: 1.875rem; font-weight: bold; margin-bottom: 1.5rem;">Account Settings</h1>
    
    <?php if (!empty($success_message)): ?>
        <div class="flash-message flash-success" style="margin-bottom: 1.5rem;">
            <p><?php echo $success_message; ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="flash-message flash-error" style="margin-bottom: 1.5rem;">
            <p><?php echo $error_message; ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Profile Information Section -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">
            Profile Information
        </h2>
        
        <form method="post" action="settings.php">
            <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label for="username" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                        Username *
                    </label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required 
                           style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                </div>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label for="email" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                    Email Address *
                </label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required 
                       style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label for="phone" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                    Phone Number (Optional)
                </label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                       style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
            </div>
            
            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" name="update_profile" class="btn btn-primary">
                    Update Profile
                </button>
            </div>
        </form>
    </div>
    
    <!-- Change Password Section -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">
            Change Password
        </h2>
        
        <form method="post" action="settings.php">
            <div style="margin-bottom: 1rem;">
                <label for="current_password" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                    Current Password *
                </label>
                <input type="password" id="current_password" name="current_password" required 
                       style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label for="new_password" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                    New Password *
                </label>
                <input type="password" id="new_password" name="new_password" required 
                       style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                    Password must be at least 8 characters long
                </p>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label for="confirm_password" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                    Confirm New Password *
                </label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
            </div>
            
            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" name="change_password" class="btn btn-primary">
                    Change Password
                </button>
            </div>
        </form>
    </div>
    
    <!-- Account Preferences Section -->
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">
            Account Type
        </h2>
        
        <div style="margin-bottom: 1rem;">
            <p style="font-weight: bold; margin-bottom: 0.25rem;">Current Account Type:</p>
            <p><?php echo ucfirst($user['user_type']); ?></p>
        </div>
        
        <div style="background-color: #f9fafb; padding: 1rem; border-radius: 0.375rem;">
            <p style="color: #4b5563; font-size: 0.875rem;">
                Your account type determines what features you can access in the Pet Management System.
                <?php if ($user['user_type'] === 'owner'): ?>
                    As a pet owner, you can add pets, schedule appointments, and set reminders.
                <?php else: ?>
                    As a veterinarian, you can manage appointments and add health records for pets.
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
