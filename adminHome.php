<?php
include('adminSidebar.php');
include('server.php');

if (!isset($_SESSION['admin_username'])) {
    header('Location: login.php');
    exit();
}

// Fetch real values from the database
$total_users_query = "SELECT COUNT(*) AS total_users FROM users";
$total_users_result = mysqli_query($db, $total_users_query);
$total_users = mysqli_fetch_assoc($total_users_result)['total_users'];

$total_datasets_query = "SELECT COUNT(*) AS total_datasets FROM (
    SELECT id FROM dataset
    UNION ALL
    SELECT id FROM uploaded_datasets
) AS combined_datasets";
$total_datasets_result = mysqli_query($db, $total_datasets_query);
$total_datasets = mysqli_fetch_assoc($total_datasets_result)['total_datasets'];

$total_predictions_query = "SELECT COUNT(*) AS total_predictions FROM (
    SELECT user_username FROM dataset1_results
    UNION ALL
    SELECT user_username FROM dataset2_results
    UNION ALL
    SELECT user_username FROM uploaded_dataset_results
) AS total_predictions";
$total_predictions_result = mysqli_query($db, $total_predictions_query);

// Check if query execution was successful
if ($total_predictions_result) {
    $total_predictions = mysqli_fetch_assoc($total_predictions_result)['total_predictions'];
} else {
    $total_predictions = 0; // Default value in case of error
}

// Fetch total feedback count
$total_feedback_query = "SELECT COUNT(*) AS total_feedback FROM feedback";
$total_feedback_result = mysqli_query($db, $total_feedback_query);
$total_feedback = mysqli_fetch_assoc($total_feedback_result)['total_feedback'];

$recent_activities_query = "
    SELECT 'New User Registration' AS activity_type, user_username AS username, created_at AS activity_time FROM users
    UNION ALL
    SELECT 'New Prediction from Dataset 1' AS activity_type, user_username AS username, created_at AS activity_time FROM dataset1_results
    UNION ALL
    SELECT 'New Prediction from Dataset 2' AS activity_type, user_username AS username, created_at AS activity_time FROM dataset2_results
    UNION ALL
    SELECT 'New User Dataset Upload' AS activity_type, user_username AS username, created_at AS activity_time FROM uploaded_datasets
    UNION ALL
    SELECT 'New Prediction from Uploaded Dataset' AS activity_type, user_username AS username, created_at AS activity_time FROM uploaded_dataset_results
    ORDER BY activity_time DESC
    LIMIT 3
";
$recent_activities_result = mysqli_query($db, $recent_activities_query);

if (!$recent_activities_result) {
    die('Error in recent activities query: ' . mysqli_error($db));
}

$user_feedback_query = "SELECT f.*, u.user_username FROM feedback f JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC LIMIT 3";
$user_feedback_result = mysqli_query($db, $user_feedback_query);

if (!$user_feedback_result) {
    die('Error in user feedback query: ' . mysqli_error($db));
}

$data_insights_query = "
    SELECT MONTH(created_at) AS month, COUNT(*) AS predictions FROM (
        SELECT created_at FROM dataset1_results
        UNION ALL
        SELECT created_at FROM dataset2_results
        UNION ALL
        SELECT created_at FROM uploaded_dataset_results
    ) AS all_predictions
    WHERE YEAR(created_at) = YEAR(CURDATE())
    GROUP BY MONTH(created_at)
";
$data_insights_result = mysqli_query($db, $data_insights_query);

if (!$data_insights_result) {
    die('Error in data insights query: ' . mysqli_error($db));
}

$predictions_per_month = array_fill(1, 12, 0); // Initialize array for all 12 months with 0

while ($row = mysqli_fetch_assoc($data_insights_result)) {
    $predictions_per_month[$row['month']] = $row['predictions'];
}

$data_insights_users_query = "
    SELECT MONTH(created_at) AS month, COUNT(*) AS users FROM users
    WHERE YEAR(created_at) = YEAR(CURDATE())
    GROUP BY MONTH(created_at)
";
$data_insights_users_result = mysqli_query($db, $data_insights_users_query);

if (!$data_insights_users_result) {
    die('Error in users data insights query: ' . mysqli_error($db));
}

$users_per_month = array_fill(1, 12, 0); // Initialize array for all 12 months with 0

while ($row = mysqli_fetch_assoc($data_insights_users_result)) {
    $users_per_month[$row['month']] = $row['users'];
}

$data_insights_datasets_query = "
    SELECT MONTH(created_at) AS month, COUNT(*) AS datasets FROM (
        SELECT date_uploaded AS created_at FROM dataset
        UNION ALL
        SELECT created_at FROM uploaded_datasets
    ) AS all_datasets
    WHERE YEAR(created_at) = YEAR(CURDATE())
    GROUP BY MONTH(created_at)
";
$data_insights_datasets_result = mysqli_query($db, $data_insights_datasets_query);

if (!$data_insights_datasets_result) {
    die('Error in datasets data insights query: ' . mysqli_error($db));
}

$datasets_per_month = array_fill(1, 12, 0); // Initialize array for all 12 months with 0

while ($row = mysqli_fetch_assoc($data_insights_datasets_result)) {
    $datasets_per_month[$row['month']] = $row['datasets'];
}

// Fetch data insights for user feedback
$data_insights_feedback_query = "
    SELECT MONTH(created_at) AS month, COUNT(*) AS feedbacks FROM feedback
    WHERE YEAR(created_at) = YEAR(CURDATE())
    GROUP BY MONTH(created_at)
";
$data_insights_feedback_result = mysqli_query($db, $data_insights_feedback_query);

if (!$data_insights_feedback_result) {
    die('Error in feedback data insights query: ' . mysqli_error($db));
}

$feedback_per_month = array_fill(1, 12, 0); // Initialize array for all 12 months with 0

while ($row = mysqli_fetch_assoc($data_insights_feedback_result)) {
    $feedback_per_month[$row['month']] = $row['feedbacks'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/adminHome.css?v=1.2">
</head>
<body>
    <div class="container">
        <h1>Welcome, Admin!</h1>
        <p>This is the admin dashboard for the Breast Cancer Prediction System.</p>
        
        <div class="section">
            <div class="overview">
                <div class="overview-item">
                    <h3>Total Users</h3>
                    <p><?php echo $total_users; ?></p>
                </div>
                <div class="overview-item">
                    <h3>Total Datasets</h3>
                    <p><?php echo $total_datasets; ?></p>
                </div>
                <div class="overview-item">
                    <h3>Total Predictions</h3>
                    <p><?php echo $total_predictions; ?></p>
                </div>
                <div class="overview-item">
                    <h3>Total Feedback</h3>
                    <p><?php echo $total_feedback; ?></p>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2>Recent Activity</h2>
            <?php while($activity = mysqli_fetch_assoc($recent_activities_result)) { ?>
                <p><?php echo $activity['username']; ?>: <?php echo $activity['activity_type']; ?> (<?php echo $activity['activity_time']; ?>)</p>
            <?php } ?>
        </div>

        <div class="section">
            <h2>Data Insights</h2>
            <canvas id="dataInsightsChart"></canvas>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                var ctx = document.getElementById('dataInsightsChart').getContext('2d');
                var dataInsightsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                        datasets: [
                            {
                                label: 'Number of Predictions',
                                data: <?php echo json_encode(array_values($predictions_per_month)); ?>,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Number of Users',
                                data: <?php echo json_encode(array_values($users_per_month)); ?>,
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Number of Datasets',
                                data: <?php echo json_encode(array_values($datasets_per_month)); ?>,
                                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                                borderColor: 'rgba(255, 206, 86, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Number of Feedbacks',
                                data: <?php echo json_encode(array_values($feedback_per_month)); ?>,
                                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                                borderColor: 'rgba(153, 102, 255, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            </script>
        </div>

        <div class="section">
            <h2>User Feedback</h2>
            <?php while($feedback = mysqli_fetch_assoc($user_feedback_result)) { ?>
                <p><strong><?php echo $feedback['user_username']; ?></strong>: "<?php echo $feedback['comments']; ?>" (<?php echo $feedback['created_at']; ?>)</p>
            <?php } ?>
        </div>
