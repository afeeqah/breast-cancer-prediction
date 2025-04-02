<?php
include('userNavbar.php');
include('server.php');

// Check if user is logged in
if (!isset($_SESSION['user_username'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Retrieve user data and last login time from the database based on the current session
$username = $_SESSION['user_username'];
$query = "SELECT * FROM users WHERE user_username='$username'";
$result = mysqli_query($db, $query);
$user = mysqli_fetch_assoc($result);
$lastLogin = $user['last_login'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="css/userProfile.css">
    <style>
        /* CSS to set the maximum dimensions of the profile picture */
        .profile-picture {
            max-width: 360px;
            max-height: 360px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>User Profile</h2>
        <div class="profile-info">
            <!-- Display profile picture -->
            <?php if ($user['profile_picture']): ?>
                <img src="<?php echo $user['profile_picture']; ?>" alt="Profile Picture" class="profile-picture">
            <?php else: ?>
                <p>No profile picture available</p>
            <?php endif; ?>
            <p>Username: <?php echo $user['user_username']; ?></p>
            <p>Email: <?php echo $user['email']; ?></p>
        </div>

        <hr>
        <div class="btn-container">
            <a href="userEditProfile.php" class="btn">Edit Profile</a>
            <a href="userChangePass.php" class="btn">Change Password</a>
        </div>
        <hr>
        
        <div class="activity-log">
            <h3>Activity Log</h3>
            <p>Last Login: <?php echo $lastLogin; ?></p>
        </div>
    </div>
</body>
</html>
