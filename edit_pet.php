<?php
session_start();
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if pet ID is provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$pet_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Check if the user has permission to edit this pet
$check_sql = "SELECT * FROM pets WHERE id = ? AND owner_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $pet_id, $user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    // Pet not found or user doesn't have permission
    header("Location: dashboard.php");
    exit();
}

$pet = $result->fetch_assoc();

// Set page title
$page_title = "Edit Pet - " . $pet['name'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $_POST['name'];
    $species = $_POST['species'];
    $breed = $_POST['breed'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = strtolower($_POST['gender']); // Convert to lowercase to match database enum
    $weight = $_POST['weight'];
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    
    // Validate required fields
    if (empty($name) || empty($species) || empty($breed) || empty($date_of_birth) || empty($gender) || empty($weight)) {
        $error = "Please fill in all required fields.";
    } else {
        // Handle image upload if a new image is provided
        $image_path = $pet['image_path']; // Default to current image
        
        if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'images/pets/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['pet_image']['name'], PATHINFO_EXTENSION);
            $file_name = 'pet_' . $pet_id . '_' . time() . '.' . $file_extension;
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
            // Update pet information in the database
            $update_sql = "UPDATE pets SET name = ?, species = ?, breed = ?, date_of_birth = ?, gender = ?, 
                          weight = ?, notes = ?, image_path = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssssdssi", $name, $species, $breed, $date_of_birth, $gender, $weight, $notes, $image_path, $pet_id);
            
            if ($update_stmt->execute()) {
                $message = "Pet information updated successfully!";
                
                // Refresh pet data
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                $pet = $result->fetch_assoc();
            } else {
                $error = "Error updating pet information: " . $conn->error;
            }
        }
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-blue-800">Edit Pet</h1>
        <div>
            <a href="view_pet.php?id=<?php echo $pet_id; ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                Back to Pet Details
            </a>
            <a href="dashboard.php" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Dashboard
            </a>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p><?php echo $message; ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p><?php echo $error; ?></p>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="edit_pet.php?id=<?php echo $pet_id; ?>" method="post" enctype="multipart/form-data" data-validate="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex flex-col md:flex-row">
                        <div class="md:w-1/3 mb-4 md:mb-0">
                            <?php if (!empty($pet['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($pet['image_path']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>" class="w-full rounded-lg mb-2">
                            <?php else: ?>
                                <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center mb-2">
                                    <span class="text-gray-500">No Image Available</span>
                                </div>
                            <?php endif; ?>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="pet_image">
                                Update Pet Image
                            </label>
                            <input type="file" name="pet_image" id="pet_image" class="w-full">
                            <p class="text-xs text-gray-500 mt-1">Max file size: 5MB. Supported formats: JPG, PNG, GIF</p>
                        </div>
                        <div class="md:w-2/3 md:pl-6">
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                                    Pet Name *
                                </label>
                                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($pet['name']); ?>" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="species">
                                    Species *
                                </label>
                                <select name="species" id="species" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    <option value="Dog" <?php echo $pet['species'] === 'Dog' ? 'selected' : ''; ?>>Dog</option>
                                    <option value="Cat" <?php echo $pet['species'] === 'Cat' ? 'selected' : ''; ?>>Cat</option>
                                    <option value="Bird" <?php echo $pet['species'] === 'Bird' ? 'selected' : ''; ?>>Bird</option>
                                    <option value="Rabbit" <?php echo $pet['species'] === 'Rabbit' ? 'selected' : ''; ?>>Rabbit</option>
                                    <option value="Hamster" <?php echo $pet['species'] === 'Hamster' ? 'selected' : ''; ?>>Hamster</option>
                                    <option value="Guinea Pig" <?php echo $pet['species'] === 'Guinea Pig' ? 'selected' : ''; ?>>Guinea Pig</option>
                                    <option value="Fish" <?php echo $pet['species'] === 'Fish' ? 'selected' : ''; ?>>Fish</option>
                                    <option value="Reptile" <?php echo $pet['species'] === 'Reptile' ? 'selected' : ''; ?>>Reptile</option>
                                    <option value="Other" <?php echo $pet['species'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="breed">
                        Breed *
                    </label>
                    <input type="text" name="breed" id="breed" value="<?php echo htmlspecialchars($pet['breed']); ?>" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="date_of_birth">
                        Date of Birth *
                    </label>
                    <input type="date" name="date_of_birth" id="date_of_birth" value="<?php echo htmlspecialchars($pet['date_of_birth']); ?>" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="gender">
                        Gender *
                    </label>
                    <select name="gender" id="gender" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="male" <?php echo $pet['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo $pet['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                        <option value="unknown" <?php echo $pet['gender'] === 'unknown' ? 'selected' : ''; ?>>Unknown</option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="weight">
                        Weight (kg) *
                    </label>
                    <input type="number" name="weight" id="weight" value="<?php echo htmlspecialchars($pet['weight']); ?>" min="0" step="0.1" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="notes">
                        Special Notes (allergies, medical conditions, etc.)
                    </label>
                    <textarea name="notes" id="notes" rows="4"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo htmlspecialchars($pet['notes']); ?></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Update Pet Information
                </button>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-red-600 mb-4">Danger Zone</h2>
        <p class="mb-4 text-gray-700">Permanently delete this pet and all associated records. This action cannot be undone.</p>
        <form action="delete_pet.php" method="post" onsubmit="return confirm('Are you sure you want to delete this pet? This action cannot be undone.');">
            <input type="hidden" name="pet_id" value="<?php echo $pet_id; ?>">
            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Delete Pet
            </button>
        </form>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
