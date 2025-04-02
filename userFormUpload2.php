<?php
include('userNavbar.php');
include('server.php');

// Fetch selected features and dataset details from the database
$dataset_id = $_POST['dataset_id'];  // Ensure this line uses POST method
$query = "SELECT * FROM uploaded_datasets WHERE id = $dataset_id";
$result = mysqli_query($db, $query);
if (!$result) {
    die('Invalid query: ' . mysqli_error($db));
}
$dataset = mysqli_fetch_assoc($result);

$selected_features = json_decode($dataset['selected_features'], true);
$dataset_path = $dataset['dataset_path'];

if (!file_exists($dataset_path)) {
    die("File not found: " . $dataset_path);
}

$data = array_map('str_getcsv', file($dataset_path));
$header = array_shift($data);
$data = array_map('array_combine', array_fill(0, count($data), $header), $data);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Generated Form for Dataset</title>
    <link rel="stylesheet" type="text/css" href="css/userForm.css">
    <style>
        .form-group {
            margin-bottom: 15px;
        }
    </style>
    <script>
        function submitForm(event) {
            event.preventDefault();

            const formData = new FormData(document.getElementById("generated-form"));
            formData.append("dataset_id", <?php echo $dataset_id; ?>);
            formData.append("user_username", "<?php echo $_SESSION['user_username']; ?>");

            // Add additional hidden fields with required data
            formData.append("dataset_name", "<?php echo $dataset['dataset_name']; ?>");
            formData.append("dataset_path", "<?php echo $dataset['dataset_path']; ?>");
            formData.append("target_feature", "<?php echo $dataset['target_feature']; ?>");
            formData.append("selected_features", "<?php echo htmlspecialchars(json_encode($selected_features)); ?>");
            formData.append("accuracy", "<?php echo $dataset['accuracy']; ?>");
            formData.append("precision", "<?php echo $dataset['precision']; ?>");
            formData.append("recall", "<?php echo $dataset['recall']; ?>");
            formData.append("f1_score", "<?php echo $dataset['f1_score']; ?>");

            fetch("userSaveFormResult.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert("Error: " + data.error);
                } else {
                    window.location.href = "userResult.php";
                }
            })
            .catch(error => {
                alert("Error: " + error);
            });
        }
    </script>
</head>
<body>
    <div class="form-container">
        <h2>Fill Out the Form</h2>
        <form id="generated-form" onsubmit="submitForm(event)">
            <?php
            foreach ($selected_features as $feature) {
                echo '<div class="form-group">';
                echo '<label for="' . $feature . '">' . $feature . ':</label>';

                // Extract unique values for the feature
                $unique_values = array_unique(array_column($data, $feature));

                if (count($unique_values) <= 5) {
                    // Categorical feature: create a dropdown
                    echo '<select name="' . $feature . '" id="' . $feature . '">';
                    foreach ($unique_values as $value) {
                        echo '<option value="' . $value . '">' . $value . '</option>';
                    }
                    echo '</select>';
                } else {
                    // Numeric feature: create a numeric input
                    echo '<input type="number" name="' . $feature . '" id="' . $feature . '" required>';
                }

                echo '</div>';
            }
            ?>
            <input type="submit" value="Submit">
        </form>
    </div>
</body>
</html>

