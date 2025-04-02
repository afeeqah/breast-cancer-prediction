<?php
include('userNavbar.php');
include('server.php');

// Fetch published forms
$query = "SELECT * FROM forms WHERE published = 1";
$result = mysqli_query($db, $query);
$forms = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Check Breast Cancer Prediction</title>
    <link rel="stylesheet" type="text/css" href="css/userCheck.css">
</head>
<body>
    <h2>Choose Dataset</h2>
    <div class="form-container">
        <!-- Existing dataset options -->
        <form action="userFormDataset1.php" method="post">
            <input type="image" src="uploads/dataset1.png" alt="Use Dataset 1" class="dataset-button">
        </form>
        <form action="userFormDataset2.php" method="post">
            <input type="image" src="uploads/dataset2.png" alt="Use Dataset 2" class="dataset-button">
        </form>
        <form action="userForm.php" method="post">
            <input type="image" src="uploads/dataset3.png" alt="Use Dataset 3" class="dataset-button">
        </form>
        
        <!-- Dynamically generated forms -->
        <?php foreach ($forms as $form): ?>
            <form action="userForm.php" method="post">
                <input type="hidden" name="form_id" value="<?php echo $form['id']; ?>">
                <button type="submit" class="dataset-button"><?php echo htmlspecialchars($form['name']); ?></button>
            </form>
        <?php endforeach; ?>
    </div>
</body>
</html>
