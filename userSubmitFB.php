<?php
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include server.php for database connection
    include('server.php');

    // Retrieve form data
    $feedbackType = mysqli_real_escape_string($db, $_POST['feedback_type']);
    $comments = mysqli_real_escape_string($db, $_POST['comments']);
    $user_id = mysqli_real_escape_string($db, $_POST['user_id']);

    // SQL query to insert feedback into the database
    $query = "INSERT INTO feedback (feedback_type, comments, created_at, user_id) VALUES ('$feedbackType', '$comments', NOW(), '$user_id')";

    // Execute the query
    if (mysqli_query($db, $query)) {
        // Redirect back to userFeedback.php with a success flag
        header("Location: userFeedback.php?submitted=true");
        exit();
    } else {
        // Redirect back to userFeedback.php with an error flag
        header("Location: userFeedback.php?submitted=false");
        exit();
    }
} else {
    // If the form is not submitted, redirect to userFeedback.php
    header("Location: userFeedback.php");
    exit();
}
