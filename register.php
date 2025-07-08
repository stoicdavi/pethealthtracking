<?php
session_start();
require_once 'includes/db_connect.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Set page title
$page_title = "Register";

// Process registration form
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_type = $_POST['user_type'] ?? 'owner';
    $vet_code = $_POST['vet_code'] ?? '';
    
    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($user_type === 'veterinary' && empty($vet_code)) {
        $error = "Veterinarian registration code is required.";
    } else {
        // Check if email already exists
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Email already in use. Please use a different email or login.";
        } else {
            // If registering as a vet, validate the vet code
            $valid_vet_code = true;
            if ($user_type === 'veterinary') {
                $code_sql = "SELECT id FROM vet_codes WHERE code = ? AND is_used = 0";
                $code_stmt = $conn->prepare($code_sql);
                $code_stmt->bind_param("s", $vet_code);
                $code_stmt->execute();
                $code_result = $code_stmt->get_result();
                
                if ($code_result->num_rows === 0) {
                    $error = "Invalid or already used veterinarian registration code.";
                    $valid_vet_code = false;
                } else {
                    $code_id = $code_result->fetch_assoc()['id'];
                }
            }
            
            if ($valid_vet_code) {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Start transaction
                $conn->begin_transaction();
                
                try {
                    // Insert new user
                    $insert_sql = "INSERT INTO users (username, email, password, user_type) VALUES (?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("ssss", $username, $email, $hashed_password, $user_type);
                    $insert_stmt->execute();
                    
                    $user_id = $conn->insert_id;
                    
                    // If veterinarian, mark the code as used
                    if ($user_type === 'veterinary') {
                        $update_code_sql = "UPDATE vet_codes SET is_used = 1, used_by = ?, used_at = NOW() WHERE id = ?";
                        $update_code_stmt = $conn->prepare($update_code_sql);
                        $update_code_stmt->bind_param("ii", $user_id, $code_id);
                        $update_code_stmt->execute();
                    }
                    
                    // Commit transaction
                    $conn->commit();
                    
                    $success = "Registration successful! You can now login.";
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $conn->rollback();
                    $error = "Error creating account: " . $e->getMessage();
                }
            }
        }
    }
}

// Include header
include 'includes/header.php';
?>

<div style="min-height: calc(100vh - 14rem); display: flex; align-items: center; justify-content: center; padding: 2rem 0;">
    <div style="max-width: 32rem; width: 100%;">
        <div class="card">
            <h1 style="font-size: 1.5rem; font-weight: bold; text-align: center; margin-bottom: 1.5rem;">Create an Account</h1>
            
            <?php if (!empty($error)): ?>
                <div class="flash-message flash-error" style="margin-bottom: 1rem;">
                    <p><?php echo $error; ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="flash-message flash-success" style="margin-bottom: 1rem;">
                    <p><?php echo $success; ?></p>
                    <p style="margin-top: 0.5rem;"><a href="login.php" style="color: #047857; text-decoration: underline;">Click here to login</a></p>
                </div>
            <?php else: ?>
                <form method="post" action="register.php">
                    <div style="margin-bottom: 1rem;">
                        <label for="username" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Username</label>
                        <input type="text" id="username" name="username" required class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label for="email" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Email Address</label>
                        <input type="email" id="email" name="email" required class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label for="password" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Password</label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" required class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                            <span toggle="#password" class="password-toggle" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">Must be at least 8 characters long</p>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label for="confirm_password" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Confirm Password</label>
                        <div style="position: relative;">
                            <input type="password" id="confirm_password" name="confirm_password" required class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                            <span toggle="#confirm_password" class="password-toggle" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Account Type</label>
                        <div style="display: flex; gap: 1rem;">
                            <label style="display: flex; align-items: center;">
                                <input type="radio" name="user_type" value="owner" checked id="user_type_owner" style="margin-right: 0.5rem;">
                                Pet Owner
                            </label>
                            <label style="display: flex; align-items: center;">
                                <input type="radio" name="user_type" value="veterinary" id="user_type_vet" style="margin-right: 0.5rem;">
                                Veterinarian
                            </label>
                        </div>
                    </div>
                    
                    <div id="vet_code_section" style="margin-bottom: 1.5rem; display: none;">
                        <label for="vet_code" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Veterinarian Registration Code</label>
                        <input type="text" id="vet_code" name="vet_code" class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                        <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">Required for veterinarian registration. Contact admin for a code.</p>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
                    </div>
                    
                    <div style="text-align: center;">
                        <p>Already have an account? <a href="login.php" style="color: #2563eb; text-decoration: none;">Login here</a></p>
                    </div>
                </form>
                
                <script>
                    // Show/hide vet code field based on selected account type
                    document.addEventListener('DOMContentLoaded', function() {
                        const ownerRadio = document.getElementById('user_type_owner');
                        const vetRadio = document.getElementById('user_type_vet');
                        const vetCodeSection = document.getElementById('vet_code_section');
                        
                        function updateVetCodeVisibility() {
                            if (vetRadio.checked) {
                                vetCodeSection.style.display = 'block';
                            } else {
                                vetCodeSection.style.display = 'none';
                            }
                        }
                        
                        ownerRadio.addEventListener('change', updateVetCodeVisibility);
                        vetRadio.addEventListener('change', updateVetCodeVisibility);
                        
                        // Initial check
                        updateVetCodeVisibility();
                        
                        // Password toggle functionality
                        const togglePasswordButtons = document.querySelectorAll('.password-toggle');
                        
                        togglePasswordButtons.forEach(function(button) {
                            button.addEventListener('click', function() {
                                // Toggle the type attribute
                                const password = document.querySelector(this.getAttribute('toggle'));
                                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                                password.setAttribute('type', type);
                                
                                // Toggle the icon
                                this.querySelector('i').classList.toggle('fa-eye');
                                this.querySelector('i').classList.toggle('fa-eye-slash');
                            });
                        });
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
