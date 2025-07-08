<?php
session_start();
require_once 'includes/db_connect.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Set page title
$page_title = "Login";

// Process login form
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($login) || empty($password)) {
        $error = "Please enter both username/email and password.";
    } else {
        // Check if user exists by username or email
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username/email or password.";
            }
        } else {
            $error = "Invalid username/email or password.";
        }
    }
}

// Include header
include 'includes/header.php';
?>

<div style="min-height: calc(100vh - 14rem); display: flex; align-items: center; justify-content: center; padding: 2rem 0;">
    <div style="max-width: 28rem; width: 100%;">
        <div class="card">
            <h1 style="font-size: 1.5rem; font-weight: bold; text-align: center; margin-bottom: 1.5rem;">Login to Your Account</h1>
            
            <?php if (!empty($error)): ?>
                <div class="flash-message flash-error" style="margin-bottom: 1rem;">
                    <p><?php echo $error; ?></p>
                </div>
            <?php endif; ?>
            
        
            
            <form method="post" action="login.php">
                <div style="margin-bottom: 1rem;">
                    <label for="login" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Username or Email</label>
                    <input type="text" id="login" name="login" required class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label for="password" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Password</label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" required class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                        <span toggle="#password" class="password-toggle" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
                </div>
                
                <div style="text-align: center;">
                    <p>Don't have an account? <a href="register.php" style="color: #2563eb; text-decoration: none;">Register here</a></p>
                </div>
            </form>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Password toggle functionality
                    const togglePassword = document.querySelector('.password-toggle');
                    
                    togglePassword.addEventListener('click', function() {
                        // Toggle the type attribute
                        const password = document.querySelector(this.getAttribute('toggle'));
                        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                        password.setAttribute('type', type);
                        
                        // Toggle the icon
                        this.querySelector('i').classList.toggle('fa-eye');
                        this.querySelector('i').classList.toggle('fa-eye-slash');
                    });
                });
            </script>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
