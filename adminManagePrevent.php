<?php
include('adminSidebar.php');
include('server.php');

function saveImages($files) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $images = [];
    foreach ($files['name'] as $key => $name) {
        if (in_array($files['type'][$key], $allowed_types)) {
            $image = 'uploads/' . basename($name);
            move_uploaded_file($files['tmp_name'][$key], $image);
            $images[] = $image;
        }
    }
    return $images;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $section = mysqli_real_escape_string($db, $_POST['section']);
        $title = mysqli_real_escape_string($db, $_POST['title']);
        $content = mysqli_real_escape_string($db, $_POST['content']);
        $color = mysqli_real_escape_string($db, $_POST['color']);
        if ($color == 'custom') {
            $color = mysqli_real_escape_string($db, $_POST['custom_color']);
        }
        $image_size = mysqli_real_escape_string($db, $_POST['image_size']);
        $custom_width = mysqli_real_escape_string($db, $_POST['custom_width']);
        $custom_height = mysqli_real_escape_string($db, $_POST['custom_height']);
        $image_descriptions = isset($_POST['image_descriptions']) ? json_encode($_POST['image_descriptions']) : '';

        $images = saveImages($_FILES['images']);
        $images = json_encode($images);

        $query = "INSERT INTO prevention (section, title, content, color, image_size, custom_width, custom_height, images, image_descriptions) VALUES ('$section', '$title', '$content', '$color', '$image_size', '$custom_width', '$custom_height', '$images', '$image_descriptions')";
        mysqli_query($db, $query);
    }

    if (isset($_POST['action']) && $_POST['action'] == 'edit') {
        $id = intval($_POST['id']);
        $section = mysqli_real_escape_string($db, $_POST['section']);
        $title = mysqli_real_escape_string($db, $_POST['title']);
        $content = mysqli_real_escape_string($db, $_POST['content']);
        $color = mysqli_real_escape_string($db, $_POST['color']);
        if ($color == 'custom') {
            $color = mysqli_real_escape_string($db, $_POST['custom_color']);
        }
        $image_size = mysqli_real_escape_string($db, $_POST['image_size']);
        $custom_width = mysqli_real_escape_string($db, $_POST['custom_width']);
        $custom_height = mysqli_real_escape_string($db, $_POST['custom_height']);
        $image_descriptions = isset($_POST['image_descriptions']) ? json_encode($_POST['image_descriptions']) : '';

        $images = saveImages($_FILES['images']);
        $images = !empty($images) ? json_encode($images) : null;

        if ($images) {
            $query = "UPDATE prevention SET section='$section', title='$title', content='$content', color='$color', images='$images', image_size='$image_size', image_descriptions='$image_descriptions', custom_width='$custom_width', custom_height='$custom_height' WHERE id=$id";
        } else {
            $query = "UPDATE prevention SET section='$section', title='$title', content='$content', color='$color', image_size='$image_size', image_descriptions='$image_descriptions', custom_width='$custom_width', custom_height='$custom_height' WHERE id=$id";
        }

        mysqli_query($db, $query);
    }

    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $id = intval($_POST['id']);
        $query = "DELETE FROM prevention WHERE id=$id";
        mysqli_query($db, $query);
    }

    // Handle reordering of sections
    if (isset($_POST['action']) && $_POST['action'] == 'reorder') {
        $order = json_decode($_POST['order'], true);
        foreach ($order as $index => $id) {
            $query = "UPDATE prevention SET order_index=$index WHERE id=$id";
            mysqli_query($db, $query);
        }
    }

    // Redirect to avoid resubmission
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

$query = "SELECT * FROM prevention ORDER BY order_index";
$result = mysqli_query($db, $query);
$preventions = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Prevention</title>
    <link rel="stylesheet" href="css/adminManageAdvicePrevent.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script>
        $(function() {
            $(".sortable").sortable({
                update: function(event, ui) {
                    var order = $(this).sortable('toArray').toString();
                    $.post('adminManagePrevent.php', {action: 'reorder', order: JSON.stringify(order)});
                }
            });
            $(".sortable").disableSelection();

            // Show custom size inputs if 'custom' is selected
            $('#image_size').on('change', function() {
                if ($(this).val() == 'custom') {
                    $('#custom_size').show();
                } else {
                    $('#custom_size').hide();
                }
            });

            // Show custom color input if 'custom' is selected
            $('#color').on('change', function() {
                if ($(this).val() == 'custom') {
                    $('#custom_color').show();
                } else {
                    $('#custom_color').hide();
                }
            });
        });

        function editPrevention(id, section, title, content, color, image_size, custom_width, custom_height, images, image_descriptions) {
            document.getElementById('id').value = id;
            document.getElementById('section').value = section;
            document.getElementById('title').value = title;
            document.getElementById('content').value = content;
            document.getElementById('color').value = color;
            if (color === 'custom') {
                document.getElementById('custom_color').value = color;
                $('#custom_color').show();
            }
            document.getElementById('image_size').value = image_size;
            if (image_size === 'custom') {
                $('#custom_size').show();
                document.getElementById('custom_width').value = custom_width;
                document.getElementById('custom_height').value = custom_height;
            } else {
                $('#custom_size').hide();
            }
            // Update the existing images and descriptions
            const existingImagesContainer = document.getElementById('existing_images');
            existingImagesContainer.innerHTML = '';
            const imagesArray = JSON.parse(images || '[]');
            const descriptionsArray = JSON.parse(image_descriptions || '[]');
            imagesArray.forEach((img, index) => {
                existingImagesContainer.innerHTML += `
                    <div class="existing-image">
                        <img src="${img}" alt="Image" style="width: ${image_size}; max-width: 100%;">
                        <input type="text" name="image_descriptions[]" value="${descriptionsArray[index] || ''}" placeholder="Image description">
                    </div>
                `;
            });
            document.querySelector('form input[name="action"]').value = 'edit';
            document.querySelector('form button[type="submit"]').textContent = 'Edit Prevention';
            $('#popup-container').addClass('active');
            $('#popup-overlay').addClass('active');
        }

        function closePopup() {
            $('#popup-container').removeClass('active');
            $('#popup-overlay').removeClass('active');
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Manage Prevention</h2>
        <form method="post" action="adminManagePrevent.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <input type="hidden" id="id" name="id" value="">
            <label for="section">Section:</label>
            <input type="text" id="section" name="section" required>
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
            <label for="content">Content:</label>
            <textarea id="content" name="content" required></textarea>
            <label for="color">Color:</label>
            <select id="color" name="color" required>
                <option value="#000000">Black</option>
                <option value="#800000">Maroon</option>
                <option value="#D3D3D3">Light Grey</option>
                <option value="custom">Custom</option>
            </select>
            <input type="color" id="custom_color" name="custom_color" style="display:none;">
            <label for="image_size">Image Size:</label>
            <select id="image_size" name="image_size" required>
                <option value="150px">Big</option>
                <option value="100px">Medium</option>
                <option value="50px">Small</option>
                <option value="custom">Custom</option>
            </select>
            <div id="custom_size" style="display:none;">
                <label for="custom_width">Custom Width:</label>
                <input type="text" id="custom_width" name="custom_width" placeholder="e.g., 400px">
                <label for="custom_height">Custom Height:</label>
                <input type="text" id="custom_height" name="custom_height" placeholder="e.g., 300px">
            </div>
            <label for="images">Images (max 5):</label>
            <input type="file" id="images" name="images[]" accept="image/*" multiple>
            <div id="existing_images"></div>
            <label for="image_descriptions">Image Descriptions (Optional):</label>
            <textarea id="image_descriptions" name="image_descriptions[]"></textarea>
            <button type="submit" class="action-button">Add Prevention</button>
        </form>
        <div class="prevention-list">
            <h3>Existing Prevention</h3>
            <ul class="sortable scrollable-list">
                <?php foreach ($preventions as $prevention): ?>
                    <li id="<?php echo $prevention['id']; ?>" class="prevention" style="border-left-color: <?php echo htmlspecialchars($prevention['color']); ?>;">
                        <h4>Section: <?php echo htmlspecialchars($prevention['section']); ?></h4>
                        <h5><?php echo htmlspecialchars($prevention['title']); ?></h5>
                        <p><?php echo htmlspecialchars($prevention['content']); ?></p>
                        <?php
                        $images = json_decode($prevention['images'], true);
                        $descriptions = json_decode($prevention['image_descriptions'], true);
                        foreach ($images as $index => $img): ?>
                            <div class="existing-image">
                                <img src="<?php echo htmlspecialchars($img); ?>" alt="Image" style="width: <?php echo htmlspecialchars($prevention['image_size']); ?>; max-width: 100%;">
                                <p><?php echo htmlspecialchars($descriptions[$index] ?? ''); ?></p>
                            </div>
                        <?php endforeach; ?>
                        <form method="post" action="adminManagePrevent.php" style="display:inline-block;">
                            <input type="hidden" name="id" value="<?php echo $prevention['id']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="action-button">Delete</button>
                        
                        <button onclick="editPrevention(
                            <?php echo $prevention['id']; ?>,
                            '<?php echo addslashes($prevention['section']); ?>',
                            '<?php echo addslashes($prevention['title']); ?>',
                            '<?php echo addslashes($prevention['content']); ?>',
                            '<?php echo addslashes($prevention['color']); ?>',
                            '<?php echo addslashes($prevention['image_size']); ?>',
                            '<?php echo addslashes($prevention['custom_width'] ?? ''); ?>',
                            '<?php echo addslashes($prevention['custom_height'] ?? ''); ?>',
                            '<?php echo addslashes($prevention['images']); ?>',
                            '<?php echo addslashes($prevention['image_descriptions']); ?>'
                        )" class="action-button">Edit</button>
                        <span class="arrow" onclick="moveUp(<?php echo $prevention['id']; ?>)">&#9650;</span>
                        <span class="arrow" onclick="moveDown(<?php echo $prevention['id']; ?>)">&#9660;</span>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div id="popup-container" class="popup-container">
        <form method="post" action="adminManagePrevent.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" id="id" name="id" value="">
            <label for="section">Section:</label>
            <input type="text" id="section" name="section" required>
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
            <label for="content">Content:</label>
            <textarea id="content" name="content" required></textarea>
            <label for="color">Color:</label>
            <select id="color" name="color" required>
                <option value="#000000">Black</option>
                <option value="#800000">Maroon</option>
                <option value="#D3D3D3">Light Grey</option>
                <option value="custom">Custom</option>
            </select>
            <input type="color" id="custom_color" name="custom_color" style="display:none;">
            <label for="image_size">Image Size:</label>
            <select id="image_size" name="image_size" required>
                <option value="150px">Big</option>
                <option value="100px">Medium</option>
                <option value="50px">Small</option>
                <option value="custom">Custom</option>
            </select>
            <div id="custom_size" style="display:none;">
                <label for="custom_width">Custom Width:</label>
                <input type="text" id="custom_width" name="custom_width" placeholder="e.g., 400px">
                <label for="custom_height">Custom Height:</label>
                <input type="text" id="custom_height" name="custom_height" placeholder="e.g., 300px">
            </div>
            <label for="images">Images (max 5):</label>
            <input type="file" id="images" name="images[]" accept="image/*" multiple>
            <div id="existing_images"></div>
            <label for="image_descriptions">Image Descriptions (Optional):</label>
            <textarea id="image_descriptions" name="image_descriptions[]"></textarea>
            <button type="submit" class="action-button">Save Changes</button>
            <button type="button" onclick="closePopup()" class="action-button">Cancel</button>
        </form>
    </div>
    <div id="popup-overlay" class="popup-overlay" onclick="closePopup()"></div>
    <script>
        function moveUp(id) {
            var element = document.getElementById(id);
            var prev = element.previousElementSibling;
            if (prev) {
                element.parentNode.insertBefore(element, prev);
                updateOrder();
            }
        }

        function moveDown(id) {
            var element = document.getElementById(id);
            var next = element.nextElementSibling;
            if (next) {
                element.parentNode.insertBefore(next, element);
                updateOrder();
            }
        }

        function updateOrder() {
            var order = [];
            document.querySelectorAll('.sortable > li').forEach((item, index) => {
                order.push(item.id);
            });
            $.post('adminManagePrevent.php', {action: 'reorder', order: JSON.stringify(order)});
        }

        function closePopup() {
            $('#popup-container').removeClass('active');
            $('#popup-overlay').removeClass('active');
        }
    </script>
</body>
</html>
