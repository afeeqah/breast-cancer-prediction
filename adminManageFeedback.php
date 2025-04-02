<?php
include('adminSidebar.php');
include('server.php');

// Handle feedback deletion and reply
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_feedback'])) {
        $feedback_id = $_POST['feedback_id'];
        $query = "DELETE FROM feedback WHERE id = $feedback_id";
        mysqli_query($db, $query);
    } elseif (isset($_POST['reply_feedback'])) {
        $feedback_id = $_POST['feedback_id'];
        $admin_reply = mysqli_real_escape_string($db, $_POST['admin_reply']);
        $query = "UPDATE feedback SET admin_reply = '$admin_reply' WHERE id = $feedback_id";
        mysqli_query($db, $query);
    }
}

// Handle search
$search = '';
$feedback_type = '';
$date = '';

if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($db, $_GET['search']);
}
if (isset($_GET['feedback_type'])) {
    $feedback_type = mysqli_real_escape_string($db, $_GET['feedback_type']);
}
if (isset($_GET['date'])) {
    $date = mysqli_real_escape_string($db, $_GET['date']);
}

// Pagination settings
$limit = 5; // Number of feedbacks per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Retrieve feedback with search and pagination
$query = "SELECT feedback.id, users.user_username, feedback.feedback_type, feedback.comments, feedback.created_at, feedback.admin_reply 
          FROM feedback 
          JOIN users ON feedback.user_id = users.id 
          WHERE (users.user_username LIKE '%$search%' OR feedback.comments LIKE '%$search%') 
          AND (feedback.feedback_type LIKE '%$feedback_type%')
          AND (feedback.created_at LIKE '%$date%')
          ORDER BY feedback.created_at DESC 
          LIMIT $start, $limit";
$result = mysqli_query($db, $query);

// Total number of feedbacks for pagination
$query_count = "SELECT COUNT(*) AS total FROM feedback 
                JOIN users ON feedback.user_id = users.id 
                WHERE (users.user_username LIKE '%$search%' OR feedback.comments LIKE '%$search%') 
                AND (feedback.feedback_type LIKE '%$feedback_type%')
                AND (feedback.created_at LIKE '%$date%')";
$count_result = mysqli_query($db, $query_count);
$total_feedback = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_feedback / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage User Feedback</title>
    <link rel="stylesheet" href="css/adminManageFeedback.css">
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
    <h2>Manage User Feedback</h2>
    
    <!-- Search Form -->
    <form method="get" action="adminManageFeedback.php" class="search-form">
        <input type="text" name="search" placeholder="Search by username or feedback" value="<?php echo $search; ?>">
        <select name="feedback_type">
            <option value="">All Types</option>
            <option value="suggestion" <?php if ($feedback_type == 'suggestion') echo 'selected'; ?>>Suggestion</option>
            <option value="bug_report" <?php if ($feedback_type == 'bug_report') echo 'selected'; ?>>Bug Report</option>
            <option value="general_feedback" <?php if ($feedback_type == 'general_feedback') echo 'selected'; ?>>General Feedback</option>
        </select>
        <input type="text" id="date" name="date" placeholder="Date" value="<?php echo $date; ?>">
        <button type="submit">Search</button>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Feedback Type</th>
            <th>Comments</th>
            <th>Date</th>
            <th>Admin Reply</th>
            <th>Action</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['user_username']; ?></td>
            <td><?php echo ucfirst(str_replace('_', ' ', $row['feedback_type'])); ?></td>
            <td><?php echo $row['comments']; ?></td>
            <td><?php echo $row['created_at']; ?></td>
            <td>
                <form method="post" action="adminManageFeedback.php" class="inline-form">
                    <textarea name="admin_reply" rows="3"><?php echo $row['admin_reply']; ?></textarea>
                    <input type="hidden" name="feedback_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="reply_feedback" class="btn-reply">Reply</button>
                </form>
            </td>
            <td>
                <form method="post" action="adminManageFeedback.php" class="inline-form">
                    <input type="hidden" name="feedback_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="delete_feedback" class="action-btn">
                        <img src="uploads/delete.png" alt="Delete">
                    </button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <!-- Pagination -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&feedback_type=<?php echo $feedback_type; ?>&date=<?php echo $date; ?>" class="page-link <?php if ($page == $i) echo 'active'; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
</div>
</body>
</html>
