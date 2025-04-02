<?php
session_start();

$username = "";
$email = "";
$password = "";
$errors = array();

$db = mysqli_connect('localhost', 'root', '', 'breast');

// Register user
if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $password = mysqli_real_escape_string($db, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($db, $_POST['confirm_password']);

    // Form validation
    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($email)) {
        array_push($errors, "Email is required");
    }
    if (empty($password)) {
        array_push($errors, "Password is required");
    }
    if (strlen($password) < 8) {
        array_push($errors, "Password must be at least 8 characters long");
    }
    if ($password != $confirm_password) {
        array_push($errors, "The two passwords do not match");
    }

    // Check if username or email already exists
    $user_check_query = "SELECT * FROM users WHERE user_username='$username' OR email='$email' LIMIT 1";
    $result = mysqli_query($db, $user_check_query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        if ($user['user_username'] === $username) {
            array_push($errors, "Username already exists");
        }

        if ($user['email'] === $email) {
            array_push($errors, "Email already exists");
        }
    }

    // If there are no errors, register the user
    if (count($errors) == 0) {
        $query = "INSERT INTO users (user_username, email, password) 
                  VALUES('$username', '$email', '$password')";
        mysqli_query($db, $query);
        $_SESSION['username'] = $username;
        $_SESSION['success'] = "Registration Successful";
        
        // Redirect to login.php
        header('location: login.php');
        exit(); // Stop executing the script after redirection
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
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
        <h2>User Registration</h2>
    </div>
    
    <form method="post" action="">
        
        <div class="input-group">
            <label>Username <?php if (empty($username)) echo "<span class='error'>*</span>"; ?></label>
            <input type="text" name="username" value="<?php echo $username; ?>">
            <?php if (in_array("Username is required", $errors)) echo "<div class='error'>Username is required</div>"; ?>
            <?php if (in_array("Username already exists", $errors)) echo "<div class='error'>Username already exists</div>"; ?>
        </div><br>

        <div class="input-group">
            <label>Email <?php if (empty($email)) echo "<span class='error'>*</span>"; ?></label>
            <input type="email" name="email" value="<?php echo $email; ?>">
            <?php if (in_array("Email is required", $errors)) echo "<div class='error'>Email is required</div>"; ?>
            <?php if (in_array("Email already exists", $errors)) echo "<div class='error'>Email already exists</div>"; ?>
        </div><br>

        <div class="input-group">
            <label>Password <?php if (empty($password)) echo "<span class='error'>*</span>"; ?></label>
            <input type="password" name="password">
            <?php if (in_array("Password is required", $errors)) echo "<div class='error'>Password is required</div>"; ?>
            <?php if (in_array("Password must be at least 8 characters long", $errors)) echo "<div class='error'>Password must be at least 8 characters long</div>"; ?>
        </div><br>

        <div class="input-group">
            <label>Confirm password <?php if (empty($confirm_password)) echo "<span class='error'>*</span>"; ?></label>
            <input type="password" name="confirm_password">
            <?php if (in_array("The two passwords do not match", $errors)) echo "<div class='error'>The two passwords do not match</div>"; ?>
        </div><br>

        <div class="input-group">
            <button type="submit" class="btn" name="register">Register</button>
        </div>
        <p>
            Already a member? <a href="login.php">Log in</a>
        </p>
        <p>
            <a href="home.php">Back</a>
        </p>
    </form>
    
    <script>
        // Check if registration successful and show popup
        <?php if (isset($_SESSION['success'])): ?>
            alert("<?php echo $_SESSION['success']; ?>");
            <?php unset($_SESSION['success']); ?>
        <?php endif ?>
    </script>
</body>
</html>
