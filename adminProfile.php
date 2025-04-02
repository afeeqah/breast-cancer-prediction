<?php
include('adminSidebar.php');
include('server.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Retrieve admin data and last login time from the database based on the current session
$username = $_SESSION['admin_username'];
$query = "SELECT * FROM admin WHERE admin_username='$username'";
$result = mysqli_query($db, $query);
$admin = mysqli_fetch_assoc($result);
$lastLogin = $admin['last_login'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="css/adminProfile.css">
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
        <h2>Admin Profile</h2>
        <div class="profile-info">
            <!-- Display profile picture -->
            <?php if ($admin['profile_picture']): ?>
                <img src="<?php echo $admin['profile_picture']; ?>" alt="Profile Picture" class="profile-picture">
            <?php else: ?>
                <p>No profile picture available</p>
            <?php endif; ?>
            <p>Username: <?php echo $admin['admin_username']; ?></p>
            <p>Email: <?php echo $admin['email']; ?></p>
        </div>

        <hr>
        <div class="btn-container">
            <a href="adminEditProfile.php" class="btn">Edit Profile</a>
            <a href="adminChangePass.php" class="btn">Change Password</a>
        </div>
        <hr>
        
        <div class="activity-log">
            <h3>Activity Log</h3>
            <p>Last Login: <?php echo $lastLogin; ?></p>
        </div>
    </div>
</body>
</html>
