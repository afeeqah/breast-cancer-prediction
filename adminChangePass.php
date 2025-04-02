<?php
// Include adminNavbar.php for navigation
include('adminSidebar.php');

// Include server.php for database connection and other functions
include('server.php');

// Define variables and initialize with empty values
$currentPassword = $newPassword = $confirmPassword = "";
$currentPasswordErr = $newPasswordErr = $confirmPasswordErr = "";
$updateError = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate current password
    if (empty($_POST["currentPassword"])) {
        $currentPasswordErr = "Please enter your current password";
    } else {
        $currentPassword = mysqli_real_escape_string($db, $_POST['currentPassword']);
    }

    // Validate new password
    if (empty($_POST["newPassword"])) {
        $newPasswordErr = "Please enter a new password";
    } elseif (strlen($_POST["newPassword"]) < 8) {
        $newPasswordErr = "Password must be at least 8 characters long";
    } else {
        $newPassword = mysqli_real_escape_string($db, $_POST['newPassword']);
    }

    // Validate confirm password
    if (empty($_POST["confirmPassword"])) {
        $confirmPasswordErr = "Please confirm your new password";
    } else {
        $confirmPassword = mysqli_real_escape_string($db, $_POST['confirmPassword']);
        if ($newPassword != $confirmPassword) {
            $confirmPasswordErr = "Passwords do not match";
        }
    }

    // Check if input errors exist before updating the password
    if (empty($currentPasswordErr) && empty($newPasswordErr) && empty($confirmPasswordErr)) {
        // Retrieve the current password associated with the admin from the database
        $username = $_SESSION['admin_username'];
        $query = "SELECT password FROM admin WHERE admin_username='$username'";
        $result = mysqli_query($db, $query);
        $row = mysqli_fetch_assoc($result);
        $currentPasswordFromDB = $row['password'];

        // Verify the current password
        if ($currentPassword == $currentPasswordFromDB) {
            // Update the password
            $updateQuery = "UPDATE admin SET password='$newPassword' WHERE admin_username='$username'";
            mysqli_query($db, $updateQuery);
            // Redirect to a success page or display a success message
            header("Location: adminProfile.php");
            exit();
        } else {
            // Display an error message if the current password is incorrect
            $updateError = "Incorrect current password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="css/userChangePass.css">
    <!-- Include jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        // Function to check password strength
        function checkPasswordStrength() {
            var password = $("#newPassword").val();
            var strengthMeter = $("#password-strength-meter");
            var strengthText = $("#password-strength-text");

            // Default strength level and text
            var strengthLevel = 0;
            var strengthTextValue = "";

            // Check password length
            if (password.length >= 8) {
                strengthLevel++;
            }

            // Check if password contains uppercase and lowercase letters
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) {
                strengthLevel++;
            }

            // Check if password contains numbers and special characters
            if (password.match(/[0-9]/) && password.match(/[!@#$%^&*]/)) {
                strengthLevel++;
            }

            // Set strength text based on strength level
            switch (strengthLevel) {
                case 0:
                    strengthTextValue = "Weak";
                    break;
                case 1:
                    strengthTextValue = "Moderate";
                    break;
                case 2:
                    strengthTextValue = "Strong";
                    break;
                case 3:
                    strengthTextValue = "Very Strong";
                    break;
            }

            // Update strength meter and text
            strengthMeter.val(strengthLevel * 25);
            strengthText.text(strengthTextValue);
        }

        $(document).ready(function() {
            // Check password strength when the new password input changes
            $("#newPassword").keyup(checkPasswordStrength);
        });
    </script>
    
</head>
<body>
    <div class="container">
        <h2>Change Password</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="input-group">
                <label for="currentPassword">Current Password:</label>
                <input type="password" name="currentPassword" required>
                <span class="error"><?php echo $currentPasswordErr; ?></span>
            </div>
            <div class="input-group">
                <label for="newPassword">New Password:</label>
                <input type="password" id="newPassword" name="newPassword" required>
                <meter max="100" id="password-strength-meter" value="0"></meter>
                <span id="password-strength-text"></span>
                <span class="error"><?php echo $newPasswordErr; ?></span>
            </div>
            <div class="input-group">
                <label for="confirmPassword">Confirm New Password:</label>
                <input type="password" name="confirmPassword" required>
                <span class="error"><?php echo $confirmPasswordErr; ?></span>
            </div>
            <div class="error"><?php echo $updateError; ?></div>
            <div class="btn-container">
                <button type="submit" class="btn" name="changePassword">Change Password</button>
                <button type="button" class="btn" onclick="window.location.href='adminProfile.php'">Back</button>
            </div>
        </form>
    </div>
</body>
</html>
