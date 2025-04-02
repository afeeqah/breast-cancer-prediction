<?php
include('userNavbar.php');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Form for Dataset 2</title>
    <link rel="stylesheet" type="text/css" href="css/userForm.css">
</head>
<body>
    <div class="form-container">
        <h2>Form for Dataset 2</h2>
        <form action="userResult.php" method="post">
            <input type="hidden" name="dataset" value="dataset2">

            <label for="area_worst">Area worst:</label>
            <input type="number" step="any" name="area_worst" id="area_worst" placeholder="B: 642.5 | M: 758.6"><br>

            <label for="concave_points_worst">Concave points worst:</label>
            <input type="number" step="any" name="concave_points_worst" id="concave_points_worst" placeholder="B: 0.107 | M: 0.08172"><br>

            <label for="concave_points_mean">Concave points mean:</label>
            <input type="number" step="any" name="concave_points_mean" id="concave_points_mean" placeholder="B: 0.02587 | M: 0.01888"><br>

            <label for="radius_worst">Radius worst:</label>
            <input type="number" step="any" name="radius_worst" id="radius_worst" placeholder="B: 14.45 | M: 15.5"><br>

            <label for="perimeter_worst">Perimeter worst:</label>
            <input type="number" step="any" name="perimeter_worst" id="perimeter_worst" placeholder="B: 92.61 | M: 102.9"><br>

            <label for="perimeter_mean">Perimeter mean:</label>
            <input type="number" step="any" name="perimeter_mean" id="perimeter_mean" placeholder="B: 82.63 | M: 88.52"><br>

            <label for="concavity_mean">Concavity mean:</label>
            <input type="number" step="any" name="concavity_mean" id="concavity_mean" placeholder="B: 0.04527 | M: 0.04105"><br>

            <label for="area_mean">Area mean:</label>
            <input type="number" step="any" name="area_mean" id="area_mean" placeholder="B: 506.3 | M: 597.8"><br>

            <label for="concavity_worst">Concavity worst:</label>
            <input type="number" step="any" name="concavity_worst" id="concavity_worst" placeholder="B: 0.1804 | M: 0.1579"><br>

            <label for="radius_mean">Radius mean:</label>
            <input type="number" step="any" name="radius_mean" id="radius_mean" placeholder="B: 12.85 | M: 13.87"><br>

            <input type="submit" value="Predict">
        </form>
    </div>
</body>
</html>
