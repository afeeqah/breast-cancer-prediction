<?php
include('userNavbar.php');
include('server.php');

$query = "SELECT * FROM advice ORDER BY order_index";
$result = mysqli_query($db, $query);
$advices = mysqli_fetch_all($result, MYSQLI_ASSOC);

$current_section = '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advice for Breast Cancer</title>
    <link rel="stylesheet" href="css/userAdvicePrevent.css">
</head>
<body>
    <div class="container">
        <h2>Advice for Breast Cancer</h2>
        <?php foreach ($advices as $advice): ?>
            <?php if ($current_section != $advice['section']): ?>
                <h3><?php echo $advice['section']; ?></h3>
                <?php $current_section = $advice['section']; ?>
            <?php endif; ?>
            <div class="advice" style="border-left-color: <?php echo htmlspecialchars($advice['color']); ?>;">
                <h4><?php echo $advice['title']; ?></h4>
                <p><?php echo $advice['content']; ?></p>
                <?php
                $images = json_decode($advice['images'], true);
                $descriptions = json_decode($advice['image_descriptions'], true);
                foreach ($images as $index => $img): ?>
                    <div class="existing-image">
                        <img src="<?php echo htmlspecialchars($img); ?>" alt="Image" style="width: <?php echo htmlspecialchars($advice['image_size']); ?>; max-width: 100%;">
                        <p><?php echo htmlspecialchars($descriptions[$index] ?? ''); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
