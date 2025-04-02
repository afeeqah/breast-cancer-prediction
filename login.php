<?php
session_start();

$username = "";
$password = "";
$errors = array();

$db = mysqli_connect('localhost', 'root', '', 'breast');

// Login user
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = mysqli_real_escape_string($db, $_POST['password']);

    // Form validation
    if (empty($username) && !empty($password)) {
        array_push($errors, "Please enter username");
    } elseif (empty($password) && !empty($username)) {
        array_push($errors, "Please enter password");
    } elseif (empty($username) && empty($password)) {
        array_push($errors, "Please enter username and password");
    } else {
        // Check in both users and admin tables
        $query = "SELECT id, user_username as username, email, password, 'user' as user_type FROM users WHERE user_username='$username' AND password='$password'
                  UNION
                  SELECT id, admin_username as username, email, password, 'admin' as user_type FROM admin WHERE admin_username='$username' AND password='$password'";
        $results = mysqli_query($db, $query);
        
        // If user authentication is successful
        if (mysqli_num_rows($results) == 1) {
            $logged_in_user = mysqli_fetch_assoc($results);
            $_SESSION['user_id'] = $logged_in_user['id']; // Store user ID in session
            $_SESSION['user_type'] = $logged_in_user['user_type']; // Store user type in session
            $_SESSION['email'] = $logged_in_user['email']; // Store email in session

            if ($logged_in_user['user_type'] == 'admin') {
                $_SESSION['admin_id'] = $logged_in_user['id'];
                $_SESSION['admin_username'] = $logged_in_user['username'];
            } else {
                $_SESSION['user_username'] = $logged_in_user['username'];
            }
            $_SESSION['success'] = "You are now logged in";

            // Debug statements
            echo "User Type: " . $_SESSION['user_type'] . "<br>";
            echo "User ID: " . $_SESSION['user_id'] . "<br>";
            echo "Email: " . $_SESSION['email'] . "<br>";
            if (isset($_SESSION['admin_id'])) {
                echo "Admin ID: " . $_SESSION['admin_id'] . "<br>";
                echo "Admin Username: " . $_SESSION['admin_username'] . "<br>";
            }

            // Update last login time for both users and admins
            $currentDateTime = date("Y-m-d H:i:s");
            $updateQuery = "UPDATE users SET last_login='$currentDateTime' WHERE user_username='$username'";
            mysqli_query($db, $updateQuery);

            $updateQuery = "UPDATE admin SET last_login='$currentDateTime' WHERE admin_username='$username'";
            mysqli_query($db, $updateQuery);

            // Redirect based on user type
            if ($logged_in_user['user_type'] == 'admin') {
                header('location: adminHome.php');
                exit(); // Stop executing the script after redirection
            } else {
                header('location: userHome.php');
                exit(); // Stop executing the script after redirection
            }
        } else {
            array_push($errors, "Wrong username and/or password");
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="css/logreg.css">
    <style>
        .error {
            color: red;
            font-size: 12px;
            margin-top: 4px;
        }
        .required {
            color: red;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Login</h2>
    </div>
    
    <form method="post" action="login.php">
        <div class="input-group">
            <label>Username <?php if (empty($username)) echo "<span class='required'>*</span>"; ?></label>
            <input type="text" name="username" value="<?php echo $username; ?>">
            <?php if (in_array("Please enter username", $errors)) echo "<div class='error'>Please enter username</div>"; ?>
        </div><br>

        <div class="input-group">
            <label>Password <?php if (empty($password)) echo "<span class='required'>*</span>"; ?></label>
            <input type="password" name="password">
            <?php if (in_array("Please enter password", $errors)) echo "<div class='error'>Please enter password</div>"; ?>
        </div><br>

        <div class="input-group">
            <?php if (in_array("Please enter username and password", $errors)) echo "<div class='error'>Please enter username and password</div>"; ?>
            <?php if (in_array("Wrong username and/or password", $errors)) echo "<div class='error'>Wrong username and/or password</div>"; ?>
        </div><br>

        <div class="input-group">
            <button type="submit" class="btn" name="login">Login</button>
        </div><br>

        <p>
            Not yet a member? <a href="register.php">Register</a>
        </p>
        <p>
            <a href="home.php">Back</a>
        </p>
    </form>
</body>
</html>
