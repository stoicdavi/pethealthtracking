<?php
session_start();
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user is a pet owner
if ($_SESSION['user_type'] !== 'owner') {
    header("Location: dashboard.php");
    exit();
}

// Set page title
$page_title = "Add Reminder";

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get pet ID from URL if provided
$pet_id = isset($_GET['pet_id']) ? intval($_GET['pet_id']) : 0;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $pet_id = $_POST['pet_id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $reminder_date = $_POST['reminder_date'] ?? '';
    
    // Validate required fields
    if (empty($pet_id) || empty($title) || empty($reminder_date)) {
        $error = "Please fill in all required fields.";
    } else {
        // Insert reminder into database
        $is_completed = 0; // Default to not completed
        
        $sql = "INSERT INTO reminders (pet_id, title, description, reminder_date, is_completed) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssi", $pet_id, $title, $description, $reminder_date, $is_completed);
        
        if ($stmt->execute()) {
            $message = "Reminder added successfully!";
            
            // Clear form data
            $pet_id = 0;
            $title = '';
            $description = '';
            $reminder_date = '';
        } else {
            $error = "Error adding reminder: " . $conn->error;
        }
    }
}

// Get pets for selection
$pets_sql = "SELECT * FROM pets WHERE owner_id = ? ORDER BY name ASC";
$pets_stmt = $conn->prepare($pets_sql);
$pets_stmt->bind_param("i", $user_id);
$pets_stmt->execute();
$pets_result = $pets_stmt->get_result();

// Include header
include 'includes/header.php';
?>

<div style="max-width: 48rem; margin: 0 auto; padding: 1rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.875rem; font-weight: bold;">Add Reminder</h1>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    
    <?php if (!empty($message)): ?>
        <div class="flash-message flash-success" style="margin-bottom: 1.5rem;">
            <p><?php echo $message; ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="flash-message flash-error" style="margin-bottom: 1.5rem;">
            <p><?php echo $error; ?></p>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <form method="post" action="add_reminder.php">
            <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 1.5rem;">
                <div>
                    <label for="pet_id" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                        Select Pet *
                    </label>
                    <select name="pet_id" id="pet_id" required class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                        <option value="">Select a Pet</option>
                        <?php while ($pet = $pets_result->fetch_assoc()): ?>
                            <option value="<?php echo $pet['id']; ?>" <?php echo ($pet_id == $pet['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($pet['name']); ?> (<?php echo htmlspecialchars($pet['species']); ?> - <?php echo htmlspecialchars($pet['breed']); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <?php if ($pets_result->num_rows === 0): ?>
                        <p style="font-size: 0.875rem; color: #ef4444; margin-top: 0.5rem;">
                            You need to add a pet first.
                            <a href="add_pet.php" style="color: #2563eb; text-decoration: underline;">Add a pet</a>
                        </p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label for="title" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                        Reminder Title *
                    </label>
                    <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title ?? ''); ?>" required class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                </div>
                
                <div>
                    <label for="reminder_date" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                        Reminder Date *
                    </label>
                    <input type="date" name="reminder_date" id="reminder_date" value="<?php echo htmlspecialchars($reminder_date ?? ''); ?>" required class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                </div>
                
                <div>
                    <label for="description" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                        Description (Optional)
                    </label>
                    <textarea name="description" id="description" rows="4" class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                </div>
                
                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary">Add Reminder</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
