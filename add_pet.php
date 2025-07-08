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
$page_title = "Add Pet";

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $_POST['name'] ?? '';
    $species = $_POST['species'] ?? '';
    $breed = $_POST['breed'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $gender = strtolower($_POST['gender'] ?? '');
    $weight = $_POST['weight'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    // Validate required fields
    if (empty($name) || empty($species) || empty($breed) || empty($date_of_birth) || empty($gender) || empty($weight)) {
        $error = "Please fill in all required fields.";
    } else {
        // Handle image upload
        $image_path = '';
        
        if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'images/pets/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['pet_image']['name'], PATHINFO_EXTENSION);
            $file_name = 'pet_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
            $target_file = $upload_dir . $file_name;
            
            // Check if file is an actual image
            $check = getimagesize($_FILES['pet_image']['tmp_name']);
            if ($check === false) {
                $error = "File is not an image.";
            } else {
                // Check file size (limit to 5MB)
                if ($_FILES['pet_image']['size'] > 5000000) {
                    $error = "File is too large. Maximum size is 5MB.";
                } else {
                    // Allow only certain file formats
                    if ($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg" && $file_extension != "gif") {
                        $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
                    } else {
                        // Upload file
                        if (move_uploaded_file($_FILES['pet_image']['tmp_name'], $target_file)) {
                            $image_path = $target_file;
                        } else {
                            $error = "There was an error uploading your file.";
                        }
                    }
                }
            }
        }
        
        if (empty($error)) {
            // Insert pet into database
            $sql = "INSERT INTO pets (owner_id, name, species, breed, date_of_birth, gender, weight, notes, image_path) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssssdss", $user_id, $name, $species, $breed, $date_of_birth, $gender, $weight, $notes, $image_path);
            
            if ($stmt->execute()) {
                $pet_id = $conn->insert_id;
                $message = "Pet added successfully!";
                
                // Redirect to the pet's page after a short delay
                header("refresh:2;url=view_pet.php?id=" . $pet_id);
            } else {
                $error = "Error adding pet: " . $conn->error;
            }
        }
    }
}

// Include header
include 'includes/header.php';
?>

<div style="max-width: 48rem; margin: 0 auto; padding: 1rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.875rem; font-weight: bold;">Add New Pet</h1>
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
        <form method="post" action="add_pet.php" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 1.5rem;">
                <div style="grid-column: span 1;">
                    <label for="pet_image" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                        Pet Image (Optional)
                    </label>
                    <input type="file" name="pet_image" id="pet_image" accept="image/*" style="width: 100%;">
                    <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">Max file size: 5MB. Supported formats: JPG, PNG, GIF</p>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                    <div>
                        <label for="name" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                            Pet Name *
                        </label>
                        <input type="text" name="name" id="name" required class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    </div>
                    
                    <div>
                        <label for="species" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                            Species *
                        </label>
                        <select name="species" id="species" required class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                            <option value="">Select Species</option>
                            <option value="Dog">Dog</option>
                            <option value="Cat">Cat</option>
                            <option value="Bird">Bird</option>
                            <option value="Rabbit">Rabbit</option>
                            <option value="Hamster">Hamster</option>
                            <option value="Guinea Pig">Guinea Pig</option>
                            <option value="Fish">Fish</option>
                            <option value="Reptile">Reptile</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                    <div>
                        <label for="breed" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                            Breed *
                        </label>
                        <input type="text" name="breed" id="breed" required class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    </div>
                    
                    <div>
                        <label for="date_of_birth" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                            Date of Birth *
                        </label>
                        <input type="date" name="date_of_birth" id="date_of_birth" required class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                    <div>
                        <label for="gender" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                            Gender *
                        </label>
                        <select name="gender" id="gender" required class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="unknown">Unknown</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="weight" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                            Weight (kg) *
                        </label>
                        <input type="number" name="weight" id="weight" step="0.1" min="0" required class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    </div>
                </div>
                
                <div>
                    <label for="notes" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                        Special Notes (allergies, medical conditions, etc.)
                    </label>
                    <textarea name="notes" id="notes" rows="4" class="form-input" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;"></textarea>
                </div>
                
                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary">Add Pet</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
