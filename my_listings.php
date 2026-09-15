<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'components/connect.php';

// AUTH CHECK

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = '';
    header('location:login.php');
    exit();
}

// ALWAYS initialize these — message.php needs them on every load

$success_msg = [];
$warning_msg = [];


// DELETE PROPERTY

if (isset($_POST['delete'])) {

    $delete_id = htmlspecialchars(strip_tags(trim($_POST['property_id'])));

    // Check the property exists AND belongs to this user
    $verify = $conn->prepare("SELECT * FROM `property` WHERE id = ? AND user_id = ?");
    $verify->execute([$delete_id, $user_id]);

    if ($verify->rowCount() > 0) {

        // Fetch image filenames so we can delete the files
        $select_images = $conn->prepare("SELECT image_01, image_02, image_03, image_04, image_05 FROM `property` WHERE id = ?");
        $select_images->execute([$delete_id]);
        $images = $select_images->fetch(PDO::FETCH_ASSOC);

        // Delete each image file if it exists on disk
        foreach ($images as $image_file) {
            if (!empty($image_file)) {
                $path = 'uploaded_files/' . $image_file;
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }

        foreach ($images as $image_file) {
    if (!empty($image_file)) {
        $path = 'uploaded_files/' . $image_file;
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
   
        // Finally delete the property itself
        $conn->prepare("DELETE FROM `property` WHERE id = ?")->execute([$delete_id]);

        $success_msg[] = 'Listing deleted successfully!';

    } else {
        $warning_msg[] = 'Listing not found or already deleted.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Listings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="my-listings">

    <h1 class="heading">My Listings</h1>

    <div class="box-container">

    <?php
        // Debug: Show user_id and count
        echo "<!-- Debug: Your user_id = " . htmlspecialchars($user_id) . " -->";
        
        $select_properties = $conn->prepare("SELECT * FROM `property` WHERE user_id = ? ORDER BY date DESC");
        $select_properties->execute([$user_id]);

        if ($select_properties->rowCount() > 0) {
            while ($fetch_property = $select_properties->fetch(PDO::FETCH_ASSOC)) {

                $property_id = $fetch_property['id'];

                // Count how many images this property has
                $total_images = 1; // image_01 is always required
                if (!empty($fetch_property['image_02'])) $total_images++;
                if (!empty($fetch_property['image_03'])) $total_images++;
                if (!empty($fetch_property['image_04'])) $total_images++;
                if (!empty($fetch_property['image_05'])) $total_images++;
    ?>

    <form action="" method="POST" class="box">
        <input type="hidden" name="property_id" value="<?= htmlspecialchars($property_id); ?>">
        <div class="thumb">
            <p><i class="far fa-image"></i><span><?= $total_images; ?></span></p>
            <img src="uploaded_files/<?= htmlspecialchars($fetch_property['image_01']); ?>" alt="">
        </div>
        <div class="price">
            <i class="fas fa-nepal-rupee-sign"></i>
            <span><?= htmlspecialchars($fetch_property['price']); ?></span>
        </div>
        <h3 class="name"><?= htmlspecialchars($fetch_property['property_name']); ?></h3>
        <p class="location">
            <i class="fas fa-map-marker-alt"></i>
            <span><?= htmlspecialchars($fetch_property['address']); ?></span>
        </p>
        <div class="flex-btn">
            <a href="update_property.php?get_id=<?= $property_id; ?>" class="btn">Update</a>
            <input type="submit" name="delete" value="Delete" class="btn"
                onclick="return confirm('Are you sure you want to delete this listing?');">
        </div>
        <a href="view_property.php?get_id=<?= $property_id; ?>" class="btn">View Property</a>
    </form>

    <?php
            }
        } else {
            echo '<p class="empty">No properties added yet! <a href="post_property.php" style="margin-top:1.5rem;" class="btn">Add New</a></p>';
        }
    ?>

    </div>

</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

<?php include 'components/message.php'; ?>

</body>
</html>