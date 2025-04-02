<?php 
include('adminSidebar.php'); 
include('server.php');

$errors = array();
$limit = 5; // Number of users per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Function to sanitize form inputs
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to retrieve user information for display
function getUsers($searchTerm = '', $limit, $offset) {
    global $db;
    $query = "SELECT * FROM users";
    if (!empty($searchTerm)) {
        $query .= " WHERE user_username LIKE '%$searchTerm%' OR email LIKE '%$searchTerm%'";
    }
    $query .= " LIMIT $limit OFFSET $offset";
    $result = mysqli_query($db, $query);
    $users = mysqli_fetch_all($result, MYSQLI_ASSOC);
    return $users;
}

// Function to check if username exists
function usernameExists($username) {
    global $db;
    $query = "SELECT * FROM users WHERE user_username='$username' LIMIT 1";
    $result = mysqli_query($db, $query);
    $user = mysqli_fetch_assoc($result);
    return $user ? true : false;
}

// Function to check if email exists
function emailExists($email) {
    global $db;
    $query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($db, $query);
    $user = mysqli_fetch_assoc($result);
    return $user ? true : false;
}

// Function to add a new user
function addUser($username, $email, $password) {
    global $db, $errors;
    if (usernameExists($username)) {
        array_push($errors, "Username already exists");
    }
    if (emailExists($email)) {
        array_push($errors, "Email already exists");
    }
    if (empty($errors)) {
        // Store password as plain text for demonstration purposes
        $query = "INSERT INTO users (user_username, email, password, created_at) VALUES ('$username', '$email', '$password', NOW())";
        if (mysqli_query($db, $query)) {
            array_push($errors, "User added successfully");
        } else {
            array_push($errors, "Failed to add user: " . mysqli_error($db));
        }
    }
}

// Function to delete a user
function deleteUser($id) {
    global $db;
    $query = "DELETE FROM users WHERE id=$id";
    mysqli_query($db, $query);
}

// Function to update user information
function updateUser($id, $username, $email, $password) {
    global $db, $errors;
    if (empty($errors)) {
        // Store password as plain text for demonstration purposes
        $query = "UPDATE users SET user_username='$username', email='$email', password='$password' WHERE id=$id";
        if (mysqli_query($db, $query)) {
            array_push($errors, "User updated successfully");
        } else {
            array_push($errors, "Failed to update user: " . mysqli_error($db));
        }
    }
}

// Export users to CSV
if (isset($_POST['export_users'])) {
    $filename = "users_" . date('Ymd') . ".csv";
    $output = fopen("php://output", "w");
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=$filename");
    
    $header = array('ID', 'Username', 'Email', 'Password', 'Profile Picture', 'Last Login', 'Registration Date');
    fputcsv($output, $header);
    
    $users = getUsers('', PHP_INT_MAX, 0); // Get all users without pagination
    
    foreach ($users as $user) {
        fputcsv($output, $user);
    }
    fclose($output);
    exit();
}

// Check if the form is submitted for adding a new user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $username = test_input($_POST['username']);
    $email = test_input($_POST['email']);
    $password = test_input($_POST['password']);
    
    // Validate password length
    if (strlen($password) < 8) {
        array_push($errors, "Password must be at least 8 characters long");
    } else {
        // Add new user
        addUser($username, $email, $password);
    }
}

// Check if the form is submitted for deleting users
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $id = test_input($_POST['id']);
    deleteUser($id);
    // Refresh the page after deleting the user
    header("Location: adminManageUser.php");
    exit();
}

// Check if the form is submitted for updating a user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $id = test_input($_POST['id']);
    $username = test_input($_POST['username']);
    $email = test_input($_POST['email']);
    $password = test_input($_POST['password']);
    // Validate password length
    if (strlen($password) < 8) {
        array_push($errors, "Password must be at least 8 characters long");
    } else {
        // Update user
        updateUser($id, $username, $email, $password);
    }
}

// Retrieve users for display
$searchTerm = isset($_GET['search']) ? test_input($_GET['search']) : '';
$users = getUsers($searchTerm, $limit, $offset);

// Get the total number of users for pagination
$total_users_query = "SELECT COUNT(*) AS total FROM users";
if (!empty($searchTerm)) {
    $total_users_query .= " WHERE user_username LIKE '%$searchTerm%' OR email LIKE '%$searchTerm%'";
}
$total_users_result = mysqli_query($db, $total_users_query);
$total_users = mysqli_fetch_assoc($total_users_result)['total'];
$total_pages = ceil($total_users / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Manage Users</title>
    <link rel="stylesheet" href="css/adminManageUser.css">
</head>
<body>
<div class="container">
    <h2>User Management</h2>

    <!-- Search and export form container -->
    <div class="form-row">
        <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="search-form">
            <input type="text" name="search" placeholder="Search by username or email" value="<?php echo $searchTerm; ?>">
            <button type="submit">Search</button>
        </form>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <button type="submit" name="export_users">Export Users to CSV</button>
        </form>
    </div>

    <!-- Display errors -->
    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && count($errors) > 0): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Add user form container -->
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="form-row">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="add_user">Add User</button>
    </form>

    <!-- Table to display users -->
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Password</th>
            <th>Profile Picture</th>
            <th>Last Login</th>
            <th>Registration Date</th>
            <th>Action</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo $user['user_username']; ?></td>
                <td><?php echo $user['email']; ?></td>
                <td><?php echo $user['password']; ?></td>
                <td><img src="<?php echo $user['profile_picture']; ?>" alt="Profile Picture" width="50" height="50"></td>
                <td><?php echo $user['last_login']; ?></td>
                <td><?php echo $user['created_at']; ?></td>
                <td>
                    <!-- Edit button -->
                    <button class="action-btn" onclick="openPopup(<?php echo $user['id']; ?>, '<?php echo $user['user_username']; ?>', '<?php echo $user['email']; ?>', '<?php echo $user['password']; ?>', '<?php echo $user['profile_picture']; ?>')">
                        <img src="uploads/edit.png" alt="Edit">
                    </button>
                    <!-- Delete form -->
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="inline-form">
                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                        <button type="submit" name="delete_user" class="action-btn">
                            <img src="uploads/delete.png" alt="Delete">
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <!-- Pagination links -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="adminManageUser.php?page=<?php echo $i; ?>&search=<?php echo $searchTerm; ?>" class="page-link <?php if ($i == $page) echo 'active'; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
</div>
<!-- Popup form for editing -->
<div id="editPopup" class="popup">
    <h2>Edit User</h2>
    <form id="editForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input type="hidden" id="editId" name="id">
        <input type="text" id="editUsername" name="username" placeholder="Username">
        <input type="email" id="editEmail" name="email" placeholder="Email">
        <input type="password" id="editPassword" name="password" placeholder="New Password">
        <input type="hidden" id="editProfilePicture" name="profile_picture">
        <button type="submit" name="update_user">Update</button>
    </form>
    <button onclick="closePopup()">Close</button>
</div>
<script>
    // Function to open the edit popup and fill the form with user data
    function openPopup(id, username, email, password, profilePicture) {
        document.getElementById('editId').value = id;
        document.getElementById('editUsername').value = username;
        document.getElementById('editEmail').value = email;
        document.getElementById('editPassword').value = password;
        document.getElementById('editProfilePicture').value = profilePicture;
        document.getElementById('editPopup').style.display = 'block';
    }

    // Function to close the edit popup
    function closePopup() {
        document.getElementById('editPopup').style.display = 'none';
    }
</script>
</body>
</html>
