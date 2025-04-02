<?php

// Check if the user is logged in and if their data exists
$user = null;
if (isset($_SESSION['username'])) {
    include('server.php'); // Include server.php to fetch user data
    $username = $_SESSION['username'];
    $query = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($db, $query);
    $user = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/userNavbar.css">
</head>
<body>
    
<nav class="navbar" id="user-navbar">
    <div class="navbar-container">
        <h1 class="logo">Breast Cancer Prediction</h1>
        <ul class="nav-links">
            <li><a href="userHome.php">Home</a></li>
            <li><a href="userAbout.php">About</a></li>
            <li><a href="userHow.php">How to Use</a></li>
            <li><a href="userCheck.php">Breast Cancer Prediction</a></li>
            
            <li class="dropdown">
                <?php if ($user && isset($user['profile_picture']) && $user['profile_picture']): ?>
                    <a href="#" class="dropbtn"><img src="<?php echo $user['profile_picture']; ?>" alt="Profile Picture" class="profile-picture"></a>
                <?php else: ?>
                    <a href="#" class="dropbtn">Profile</a>
                <?php endif; ?>
                <div class="dropdown-content">
                    <a href="userProfile.php">My Profile</a>
                    <a href="userHistory.php">Check History</a>
                    <a href="userFeedback.php">Feedback</a>
                    <a href="home.php">Logout</a>
                </div>
            </li>
        </ul>
    </div>
</nav>

</body>
</html>
