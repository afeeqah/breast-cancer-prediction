<?php
include('userNavbar.php');
include('server.php');

// Check if user is logged in
if (!isset($_SESSION['user_username'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Fetch user data from the database
$username = $_SESSION['user_username'];
$query = "SELECT * FROM users WHERE user_username='$username'";
$result = mysqli_query($db, $query);
$user = mysqli_fetch_assoc($result);

// Define a variable to track whether the update was successful
$updateSuccess = false;

// Update profile information
if (isset($_POST['update_profile'])) {
    $newUsername = mysqli_real_escape_string($db, $_POST['username']);
    $newEmail = mysqli_real_escape_string($db, $_POST['email']);

    // Update user data in the database
    $updateQuery = "UPDATE users SET user_username='$newUsername', email='$newEmail' WHERE user_username='$username'";
    if (mysqli_query($db, $updateQuery)) {
        // Update session variable with new username
        $_SESSION['user_username'] = $newUsername;
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
        $updateQuery = "UPDATE users SET profile_picture='$folder' WHERE user_username='$newUsername'";
        mysqli_query($db, $updateQuery);
    }

    // Redirect to userProfile.php after updating
    header("Location: userProfile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Profile</title>
    <link rel="stylesheet" href="css/userEditProfile.css">
    <!-- Include jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        // Script to display success message and redirect to userProfile.php
        $(document).ready(function(){
            <?php if ($updateSuccess): ?>
                alert("Successfully Updated!");
                window.location.href = "userProfile.php"; // Redirect to userProfile.php
            <?php endif; ?>
        });
    </script>
</head>
<body>
    <div class="container">
        <h2>Edit Profile</h2>
        <form method="post" action="userEditProfile.php" enctype="multipart/form-data">
            <div class="input-group">
                <label for="username">Username:</label>
                <input type="text" name="username" value="<?php echo $user['user_username']; ?>" required>
            </div>
            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
            </div>
            <!-- Add profile picture update functionality -->
            <div class="input-group">
                <label for="profile_picture">Profile Picture:</label>
                <input type="file" name="profile_picture" accept="image/*">
            </div>
            <div class="btn-container">
                <button type="submit" name="update_profile" class="btn">Update</button>
                <button type="button" class="btn" onclick="window.location.href='userProfile.php'">Back</button>
            </div>
        </form>
    </div>
</body>
</html>
