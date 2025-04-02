<?php
include('adminSidebar.php');
include('server.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['generate_form'])) {
        $form_name = $_POST['form_name'];
        $selected_features = json_decode($_POST['selected_features'], true);
        $columns = [];

        foreach ($selected_features as $feature) {
            $columns[] = [
                'name' => $feature,
                'type' => 'text', // You can adjust the type as needed
                'options' => ''
            ];
        }

        $columns = json_encode($columns);
        $published = 1; // Automatically publish the form

        $query = "INSERT INTO forms (name, columns, published) VALUES ('$form_name', '$columns', $published)";
        mysqli_query($db, $query);
        header("Location: adminManageForm.php");
        exit();
    }
}

// Fetch existing forms
$query = "SELECT * FROM forms";
$result = mysqli_query($db, $query);
$forms = mysqli_fetch_all($result, MYSQLI_ASSOC);

if (isset($_GET['publish'])) {
    $form_id = $_GET['publish'];
    $query = "UPDATE forms SET published = 1 WHERE id = '$form_id'";
    mysqli_query($db, $query);
    header("Location: adminManageForm.php");
    exit();
}

if (isset($_GET['delete'])) {
    $form_id = $_GET['delete'];
    $query = "DELETE FROM forms WHERE id = '$form_id'";
    mysqli_query($db, $query);
    header("Location: adminManageForm.php");
    exit();
}

$errors = $errors ?? [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Forms</title>
    <link rel="stylesheet" type="text/css" href="css/adminManageForm.css">
</head>
<body>
    <div class="container">
        <h2>Manage Forms</h2>
        
        <form action="adminManageForm.php" method="post">
            <div class="field">
                <label for="form_name">Form Name:</label>
                <input type="text" name="form_name" id="form_name" required>
            </div>
            <div class="field">
                <label for="selected_features">Selected Features:</label>
                <textarea name="selected_features" id="selected_features" rows="5" required></textarea>
            </div>
            <input type="submit" name="generate_form" value="Generate Form">
        </form>

        <h3>Existing Forms</h3>
        <table>
            <tr>
                <th>Form Name</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($forms as $form): ?>
            <tr>
                <td><?php echo htmlspecialchars($form['name']); ?></td>
                <td>
                    <a href="adminManageForm.php?delete=<?php echo $form['id']; ?>" onclick="return confirm('Are you sure you want to delete this form?')">Delete</a>
                    <?php if (!$form['published']): ?>
                    <a href="adminManageForm.php?publish=<?php echo $form['id']; ?>">Publish</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
