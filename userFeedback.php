<?php 
include('userNavbar.php'); 
include('server.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Feedback</title>
    <link rel="stylesheet" href="css/userFeedback.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function(){
            <?php if (isset($_GET['submitted']) && $_GET['submitted'] == 'true'): ?>
                alert("Thank you for your feedback!");
                window.location.href = "userHome.php";
            <?php endif; ?>
        });
    </script>
</head>
<body>
    <div class="container user-feedback-container">
        <h2>User Feedback</h2>
        <form method="post" action="userSubmitFB.php">
            <div class="input-group">
                <label for="feedback_type">Feedback Type:</label>
                <select name="feedback_type" id="feedback_type" required>
                    <option value="">Select Feedback Type</option>
                    <option value="suggestion">Suggestion</option>
                    <option value="bug_report">Bug Report</option>
                    <option value="general_feedback">General Feedback</option>
                </select>
            </div>
            <div class="input-group">
                <label for="comments">Comments:</label>
                <textarea name="comments" id="comments" rows="5" required></textarea>
            </div>
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
            <button type="submit" class="btn">Submit</button>
        </form>
    </div>
    
    <div class="container feedback-container">
        <h3>Your Previous Feedback</h3>
        <div class="feedback-list">
            <?php
            $user_id = $_SESSION['user_id'];
            $query = "SELECT feedback_type, comments, admin_reply FROM feedback WHERE user_id = $user_id ORDER BY created_at DESC";
            $result = mysqli_query($db, $query);
            while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="feedback-item">
                <div class="feedback-type"><?php echo ucfirst(str_replace('_', ' ', $row['feedback_type'])); ?></div>
                <div class="feedback-comments"><?php echo $row['comments']; ?></div>
                <?php if (!empty($row['admin_reply'])): ?>
                    <div class="admin-reply">Admin Reply: <?php echo $row['admin_reply']; ?></div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
