<?php
include('userNavbar.php');
include('server.php');

$user_username = $_SESSION['user_username'];

// Fetch data from dataset1_results
$query1 = "SELECT * FROM dataset1_results WHERE user_username = '$user_username'";
$result1 = mysqli_query($db, $query1);
$dataset1_history = mysqli_fetch_all($result1, MYSQLI_ASSOC);

// Fetch data from dataset2_results
$query2 = "SELECT * FROM dataset2_results WHERE user_username = '$user_username'";
$result2 = mysqli_query($db, $query2);
$dataset2_history = mysqli_fetch_all($result2, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User History</title>
    <link rel="stylesheet" type="text/css" href="css/userHistory.css">
</head>
<body>
    <div class="container">
        <h2>User History</h2>
        
        <h3>Dataset 1 History</h3>
        <div class="table-container">
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Breastfeeding Duration</th>
                    <th>BMI Weight</th>
                    <th>BMI Height</th>
                    <th>BMI</th>
                    <th>Age</th>
                    <th>Age at Menarche</th>
                    <th>Number of Children</th>
                    <th>Marital Status</th>
                    <th>Socioeconomic Status</th>
                    <th>Smoking</th>
                    <th>Urban Rural Residence</th>
                    <th>Reproductive History</th>
                    <th>Prediction</th>
                    <th>Accuracy</th>
                    <th>Precision</th>
                    <th>Recall</th>
                    <th>F1 Score</th>
                    <th>Created At</th>
                </tr>
                <?php foreach ($dataset1_history as $row): ?>
                <tr>
                    <?php foreach ($row as $key => $cell): ?>
                    <?php if ($key != 'user_username'): ?>
                    <td><?php echo htmlspecialchars($cell); ?></td>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <h3>Dataset 2 History</h3>
        <div class="table-container">
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Area Worst</th>
                    <th>Concave Points Worst</th>
                    <th>Concave Points Mean</th>
                    <th>Radius Worst</th>
                    <th>Perimeter Worst</th>
                    <th>Perimeter Mean</th>
                    <th>Concavity Mean</th>
                    <th>Area Mean</th>
                    <th>Concavity Worst</th>
                    <th>Radius Mean</th>
                    <th>Prediction</th>
                    <th>Accuracy</th>
                    <th>Precision</th>
                    <th>Recall</th>
                    <th>F1 Score</th>
                    <th>Created At</th>
                </tr>
                <?php foreach ($dataset2_history as $row): ?>
                <tr>
                    <?php foreach ($row as $key => $cell): ?>
                    <?php if ($key != 'user_username'): ?>
                    <td><?php echo htmlspecialchars($cell); ?></td>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>
