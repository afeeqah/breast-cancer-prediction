<?php 
include('adminSidebar.php'); 
include('server.php');

// Initialize search variables
$search = '';
$date = '';

if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($db, $_GET['search']);
}
if (isset($_GET['date']) && !empty($_GET['date'])) {
    $date = mysqli_real_escape_string($db, $_GET['date']);
    $date = date('Y-m-d', strtotime($date)); // Convert date to the correct format
}

// Construct search conditions
function buildSearchConditions($search, $date, $columns) {
    $conditions = [];
    if (!empty($search)) {
        $search_conditions = [];
        foreach ($columns as $column) {
            $search_conditions[] = "`$column` LIKE '%$search%'";
        }
        $conditions[] = '(' . implode(' OR ', $search_conditions) . ')';
    }
    if (!empty($date)) {
        $conditions[] = "`created_at` LIKE '%$date%'";
    }
    return !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
}

// Columns for each table
$columns1 = ['user_username', 'prediction', 'accuracy', 'precision', 'recall', 'f1_score'];
$columns2 = ['user_username', 'prediction', 'accuracy', 'precision', 'recall', 'f1_score'];
$columns3 = ['user_username', 'dataset_name', 'target_feature', 'selected_features', 'accuracy', 'precision', 'recall', 'f1_score'];
$columns4 = ['user_username', 'prediction', 'accuracy', 'precision', 'recall', 'f1_score'];

// Retrieve history for Dataset 1 form submissions
$query1 = "SELECT * FROM dataset1_results " . buildSearchConditions($search, $date, $columns1) . " ORDER BY created_at DESC";
$result1 = mysqli_query($db, $query1);

// Retrieve history for Dataset 2 form submissions
$query2 = "SELECT * FROM dataset2_results " . buildSearchConditions($search, $date, $columns2) . " ORDER BY created_at DESC";
$result2 = mysqli_query($db, $query2);

// Retrieve history for uploaded datasets
$query3 = "SELECT * FROM uploaded_datasets " . buildSearchConditions($search, $date, $columns3) . " ORDER BY created_at DESC";
$result3 = mysqli_query($db, $query3);

// Retrieve history for results of uploaded datasets
$query4 = "SELECT * FROM uploaded_dataset_results " . buildSearchConditions($search, $date, $columns4) . " ORDER BY created_at DESC";
$result4 = mysqli_query($db, $query4);

// Handle history deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_history'])) {
    $history_id = $_POST['history_id'];
    $table = $_POST['table'];
    $query = "DELETE FROM $table WHERE id = $history_id";
    mysqli_query($db, $query);
    header("Location: adminManageHistory.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Manage History</title>
    <link rel="stylesheet" href="css/adminManageHistory.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(function() {
            $("#date").datepicker({ dateFormat: 'yy-mm-dd' });
        });
    </script>
</head>
<body>
    <div class="container">
        <h2>Manage User History</h2>
        
        <!-- Search Form -->
        <form method="get" action="adminManageHistory.php" class="search-form">
            <input type="text" name="search" placeholder="Search by any field" value="<?php echo htmlspecialchars($search); ?>">
            <input type="text" id="date" name="date" placeholder="Date" value="<?php echo htmlspecialchars($date); ?>">
            <button type="submit">Search</button>
        </form>

        <h3>Dataset 1 Form Submissions</h3>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Data</th>
                    <th>Prediction</th>
                    <th>Accuracy</th>
                    <th>Precision</th>
                    <th>Recall</th>
                    <th>F1 Score</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($result1)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['user_username']; ?></td>
                    <td class="data-column"><?php echo json_encode($row); ?></td>
                    <td><?php echo $row['prediction']; ?></td>
                    <td><?php echo $row['accuracy']; ?></td>
                    <td><?php echo $row['precision']; ?></td>
                    <td><?php echo $row['recall']; ?></td>
                    <td><?php echo $row['f1_score']; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td>
                        <form method="post" action="adminManageHistory.php" class="inline-form">
                            <input type="hidden" name="history_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="table" value="dataset1_results">
                            <button type="submit" name="delete_history" class="action-btn">
                                <img src="uploads/delete.png" alt="Delete">
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <h3>Dataset 2 Form Submissions</h3>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Data</th>
                    <th>Prediction</th>
                    <th>Accuracy</th>
                    <th>Precision</th>
                    <th>Recall</th>
                    <th>F1 Score</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($result2)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['user_username']; ?></td>
                    <td class="data-column"><?php echo json_encode($row); ?></td>
                    <td><?php echo $row['prediction']; ?></td>
                    <td><?php echo $row['accuracy']; ?></td>
                    <td><?php echo $row['precision']; ?></td>
                    <td><?php echo $row['recall']; ?></td>
                    <td><?php echo $row['f1_score']; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td>
                        <form method="post" action="adminManageHistory.php" class="inline-form">
                            <input type="hidden" name="history_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="table" value="dataset2_results">
                            <button type="submit" name="delete_history" class="action-btn">
                                <img src="uploads/delete.png" alt="Delete">
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        

        
    </div>
</body>
</html>
