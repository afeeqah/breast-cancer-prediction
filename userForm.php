<?php
include('userNavbar.php');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Form for Dataset: BRCA</title>
    <link rel="stylesheet" type="text/css" href="css/userForm.css">
</head>
<body>
    <div class="form-container">
        <h2>Form for Dataset: BRCA</h2>
        <form action="userResult.php" method="post">
            <input type="hidden" name="dataset" value="dataset">

            <label for="Protein4">Protein 4:</label>
            <input type="number" step="any" name="Protein4" id="Protein4" placeholder="Enter value"><br>

            <label for="Protein1">Protein 1:</label>
            <input type="number" step="any" name="Protein1" id="Protein1" placeholder="Enter value"><br>

            <label for="Protein2">Protein 2:</label>
            <input type="number" step="any" name="Protein2" id="Protein2" placeholder="Enter value"><br>

            <label for="Protein3">Protein 3:</label>
            <input type="number" step="any" name="Protein3" id="Protein3" placeholder="Enter value"><br>

            <label for="Age">Age:</label>
            <input type="number" step="any" name="Age" id="Age" placeholder="Enter age"><br>

            <label for="Surgery_type">Surgery Type:</label>
            <input type="text" name="Surgery_type" id="Surgery_type" placeholder="Enter surgery type"><br>

            <label for="Tumour_Stage">Tumour Stage:</label>
            <input type="text" name="Tumour_Stage" id="Tumour_Stage" placeholder="Enter tumour stage"><br>

            <label for="Histology">Histology:</label>
            <input type="text" name="Histology" id="Histology" placeholder="Enter histology"><br>

            <label for="HER2_status">HER2 Status:</label>
            <input type="text" name="HER2_status" id="HER2_status" placeholder="Enter HER2 status"><br>

            <label for="Gender">Gender:</label>
            <input type="text" name="Gender" id="Gender" placeholder="Enter gender"><br>

            <input type="submit" value="Predict">
        </form>
    </div>
</body>
</html>
