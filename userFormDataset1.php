<?php
// Include the user navbar for consistent navigation
include('userNavbar.php');

// Load the selected features from the JSON file
$selected_features_path = 'selected_features1.json';
$selected_features = json_decode(file_get_contents($selected_features_path), true);

// Load the original CSV file to get unique values for categorical features
$original_data_path = 'data1.csv';
$data = array_map('str_getcsv', file($original_data_path));
$header = array_shift($data);
$csv = [];
foreach ($data as $row) {
    $csv[] = array_combine($header, $row);
}

// Identify categorical columns and their unique values
$unique_values = [];
foreach ($csv[0] as $key => $value) {
    if (!is_numeric($value)) {
        $unique_values[$key] = array_filter(array_unique(array_column($csv, $key)), fn($v) => !empty($v));
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Form for Dataset 1</title>
    <link rel="stylesheet" type="text/css" href="css/userForm.css">
    <script>
        // JavaScript function to calculate BMI based on user input
        function calculateBMI() {
            var weight = parseFloat(document.getElementById("bmi_weight").value);
            var height = parseFloat(document.getElementById("bmi_height").value) / 100; // Convert cm to meters
            if (weight && height) {
                var bmi = weight / (height * height);
                document.getElementById("bmi_value").value = bmi.toFixed(2);
            }
        }

            // JavaScript function to validate form before submission
    function validateForm() {
        var inputs = document.querySelectorAll("input, select");
        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].value === "") {
                alert("Please fill out all fields.");
                return false;
            }
        }
        return true;
    }
</script>

</head>
<body>
    <div class="form-container">
        <h2>Form for Dataset 1</h2>
        <!-- Form that submits to userResult.php with POST method -->
        <form action="userResult.php" method="post" onsubmit="return validateForm()">
            <input type="hidden" name="dataset" value="dataset1">
            <?php
            // Loop through each selected feature to create form fields
            foreach ($selected_features as $feature) {
                $feature_name = str_replace('_', ' ', ucfirst($feature));
                if (isset($unique_values[$feature])) {
                    // Create a dropdown for categorical features
                    echo "<label for='{$feature}'>{$feature_name}:</label>";
                    echo "<select name='{$feature}' id='{$feature}'>";
                    foreach ($unique_values[$feature] as $value) {
                        echo "<option value='{$value}'>{$value}</option>";
                    }
                    echo "</select><br>";
                } else {
                    // Create appropriate input fields for other features
if ($feature === 'bmi') {
    echo "<label for='bmi_weight'>Weight (kg):</label>";
    echo "<input type='number' step='any' name='bmi_weight' id='bmi_weight' oninput='calculateBMI()' placeholder='Weight' required><br>";
    echo "<label for='bmi_height'>Height (cm):</label>";
    echo "<input type='number' step='any' name='bmi_height' id='bmi_height' oninput='calculateBMI()' placeholder='Height' required><br>";
    echo "<label for='bmi'>BMI:</label>";
    echo "<input type='text' name='bmi' id='bmi_value' readonly required><br>";
} else {
    if ($feature === 'breastfeeding_duration') {
        echo "<label for='{$feature}'>{$feature_name}:</label>";
        echo "<select name='{$feature}' id='{$feature}'>";
        for ($i = 0; $i <= 24; $i++) {
            echo "<option value='$i'>$i</option>";
        }
        echo "</select><br>";
    } elseif ($feature === 'age' || $feature === 'age_at_menarche') {
        echo "<label for='{$feature}'>{$feature_name}:</label>";
        echo "<select name='{$feature}' id='{$feature}'>";
        for ($i = 1; $i <= 100; $i++) {
            echo "<option value='$i'>$i</option>";
        }
        echo "</select><br>";
    } elseif ($feature === 'number_of_children') {
        echo "<label for='{$feature}'>{$feature_name}:</label>";
        echo "<select name='{$feature}' id='{$feature}'>";
        for ($i = 0; $i <= 20; $i++) {
            echo "<option value='$i'>$i</option>";
        }
        echo "</select><br>";
    } elseif ($feature === 'urban_rural_residence') {
        echo "<label for='{$feature}'>{$feature_name}:</label>";
        echo "<select name='{$feature}' id='{$feature}' required>";
        echo "<option value='Urban'>Urban</option>";
        echo "<option value='Rural'>Rural</option>";
        echo "</select><br>";
    } else {
        echo "<label for='{$feature}'>{$feature_name}:</label>";
        echo "<input type='text' name='{$feature}' id='{$feature}' placeholder='Enter {$feature_name}' required><br>";
    }
}

                }
            }
            ?>
            <!-- Submit button for the form -->
            <input type="submit" value="Predict">
        </form>
    </div>
</body>
</html>
