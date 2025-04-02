<?php
include('adminSidebar.php'); // Include adminNavbar.php instead of userNavbar.php
include('server.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Fetch admin data from the database
$username = $_SESSION['admin_username'];
$query = "SELECT * FROM admin WHERE admin_username='$username'";
$result = mysqli_query($db, $query);
$admin = mysqli_fetch_assoc($result);

// Define a variable to track whether the update was successful
$updateSuccess = false;

// Update profile information
if (isset($_POST['update_profile'])) {
    $newUsername = mysqli_real_escape_string($db, $_POST['username']);
    $newEmail = mysqli_real_escape_string($db, $_POST['email']);

    // Update admin data in the database
    $updateQuery = "UPDATE admin SET admin_username='$newUsername', email='$newEmail' WHERE admin_username='$username'";
    if (mysqli_query($db, $updateQuery)) {
        // Update session variable with new username
        $_SESSION['admin_username'] = $newUsername;
        $updateSuccess = true;
    }

    // Handle profile picture update
    if ($_FILES['profile_picture']['name'] != '') {
        $fileName = $_FILES['profile_picture']['name'];
        $tempName = $_FILES['profile_picture']['tmp_name'];
        $folder = "uploads/".$fileName;

        // Move uploaded file to the uploads directory
        move_uploaded_file($tempName, $folder);

        // Update profile picture path in the database
        $updateQuery = "UPDATE admin SET profile_picture='$folder' WHERE admin_username='$newUsername'";
        mysqli_query($db, $updateQuery);
    }

    // Redirect to adminProfile.php after updating
    header("Location: adminProfile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin Profile</title>
    <link rel="stylesheet" href="css/userEditProfile.css">
    <!-- Include jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>Edit Profile</h2>
        <?php if ($updateSuccess): ?>

        <!-- Display success message -->
        <script>
            $(document).ready(function(){
                alert("Successfully Updated!");
                window.location.href = "adminProfile.php"; // Redirect to adminProfile.php
            });
        </script>
        
        <?php endif; ?>
        <form method="post" action="adminEditProfile.php" enctype="multipart/form-data">
            <div class="input-group">
                <label for="username">Username:</label>
                <input type="text" name="username" value="<?php echo $admin['admin_username']; ?>" required>
            </div>
            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" name="email" value="<?php echo $admin['email']; ?>" required>
            </div>
            <!-- Add profile picture update functionality -->
            <div class="input-group">
                <label for="profile_picture">Profile Picture:</label>
                <input type="file" name="profile_picture" accept="image/*">
            </div>
            <div class="btn-container">
                <button type="submit" name="update_profile" class="btn">Update</button>
                <button type="button" class="btn" onclick="window.location.href='adminProfile.php'">Back</button>
            </div>
        </form>
    </div>
</body>
</html>
