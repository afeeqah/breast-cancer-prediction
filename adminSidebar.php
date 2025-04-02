<?php
// Check if the admin is logged in and if their data exists
$admin = null;
if (isset($_SESSION['admin_username'])) {
    include('server.php'); // Include server.php to fetch admin data
    $admin_username = $_SESSION['admin_username'];
    $query = "SELECT * FROM admin WHERE username='$admin_username'";
    $result = mysqli_query($db, $query);
    $admin = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/adminSidebar.css"> <!-- Adjust CSS file path as needed -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Include jQuery -->
</head>
<body>
    
    <div class="sidebar">
        <h2>Admin Page</h2>
        <ul>
            <li><a href="adminHome.php">Home</a></li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Manage User</a>
                <ul class="dropdown-menu">
                    <li><a href="adminManageUser.php">Manage User Profile</a></li>
                    <li><a href="adminManagePrevent.php">Manage Prevention</a></li>
                    <li><a href="adminManageAdvice.php">Manage Advice</a></li>
                    <li><a href="adminManageHistory.php">Manage User History</a></li>
                    <li><a href="adminManageFeedback.php">Manage User Feedback</a></li>
                </ul>
            </li>
            <li><a href="adminManageDataset.php">Manage Datasets</a></li>
            <li><a href="adminManageForm.php">Manage Forms</a></li>
            <li><a href="adminViewModel.php">View Model</a></li>
            <li><a href="adminProfile.php">Admin Profile</a></li>
            <li><a href="home.php">Logout</a></li>
        </ul>
    </div>

<script>
    $(document).ready(function(){
        // Toggle visibility of dropdown content
        $('.dropdown-toggle').click(function(){
            $(this).next('.dropdown-menu').toggle();
        });
    });
</script>

</body>
</html>
