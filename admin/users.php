<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include '../components/connect.php';

if(isset($_COOKIE['admin_id'])){
    $admin_id = $_COOKIE['admin_id'];
}else{
    $admin_id = '';
    header('location:login.php');
}

if(isset($_POST['delete'])){
    try {
        // Fix 1: Replace FILTER_SANITIZE_STRING with htmlspecialchars
        $delete_id = htmlspecialchars($_POST['delete_id']);
        $delete_type = htmlspecialchars($_POST['delete_type']);

        // Check correct table
        if($delete_type == 'buyer'){
            $verify_delete = $conn->prepare("SELECT * FROM `buyers` WHERE id = ?");
        } else {
            $verify_delete = $conn->prepare("SELECT * FROM `sellers` WHERE id = ?");
        }
        $verify_delete->execute([$delete_id]);

        if($verify_delete->rowCount() > 0){

            // Delete property images if seller
            if($delete_type == 'seller'){
                $select_images = $conn->prepare("SELECT * FROM `property` WHERE user_id = ?");
                $select_images->execute([$delete_id]);
                while($fetch_images = $select_images->fetch(PDO::FETCH_ASSOC)){
                    if(!empty($fetch_images['image_01'])){ unlink('../uploaded_files/'.$fetch_images['image_01']); }
                    if(!empty($fetch_images['image_02'])){ unlink('../uploaded_files/'.$fetch_images['image_02']); }
                    if(!empty($fetch_images['image_03'])){ unlink('../uploaded_files/'.$fetch_images['image_03']); }
                    if(!empty($fetch_images['image_04'])){ unlink('../uploaded_files/'.$fetch_images['image_04']); }
                    if(!empty($fetch_images['image_05'])){ unlink('../uploaded_files/'.$fetch_images['image_05']); }
                }
                $delete_listings = $conn->prepare("DELETE FROM `property` WHERE user_id = ?");
                $delete_listings->execute([$delete_id]);
            }

            // Delete from requests
            $delete_requests = $conn->prepare("DELETE FROM `requests` WHERE sender = ? OR receiver = ?");
            $delete_requests->execute([$delete_id, $delete_id]);

            // Fix 2: Removed saved table delete since it doesn't exist

            // Delete from correct table
            if($delete_type == 'buyer'){
                $delete_user = $conn->prepare("DELETE FROM `buyers` WHERE id = ?");
            } else {
                $delete_user = $conn->prepare("DELETE FROM `sellers` WHERE id = ?");
            }
            $delete_user->execute([$delete_id]);
            $success_msg[] = 'User deleted!';

        } else {
            $warning_msg[] = 'User already deleted!';
        }

    } catch(PDOException $e) {
        $error_msg[] = 'Error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="grid">
    <h1 class="heading">Users</h1>

    <form action="" method="POST" class="search-form">
        <input type="text" name="search_box" placeholder="search users..." maxlength="100" required>
        <button type="submit" class="fas fa-search" name="search_btn"></button>
    </form>

    <div class="box-container">
    <?php
        $search_box = '';
        if(isset($_POST['search_box']) OR isset($_POST['search_btn'])){
         $search_box = htmlspecialchars($_POST['search_box']);
        }

        // Fetch buyers
        if($search_box != ''){
            $select_buyers = $conn->prepare("SELECT *, 'buyer' as user_type FROM `buyers` WHERE name LIKE '%{$search_box}%' OR number LIKE '%{$search_box}%' OR email LIKE '%{$search_box}%'");
            $select_sellers = $conn->prepare("SELECT *, 'seller' as user_type FROM `sellers` WHERE name LIKE '%{$search_box}%' OR number LIKE '%{$search_box}%' OR email LIKE '%{$search_box}%'");
        } else {
            $select_buyers = $conn->prepare("SELECT *, 'buyer' as user_type FROM `buyers`");
            $select_sellers = $conn->prepare("SELECT *, 'seller' as user_type FROM `sellers`");
        }

        $select_buyers->execute();
        $select_sellers->execute();

        // Merge both results
        $all_users = array_merge(
            $select_buyers->fetchAll(PDO::FETCH_ASSOC),
            $select_sellers->fetchAll(PDO::FETCH_ASSOC)
        );

        if(count($all_users) > 0){
            foreach($all_users as $fetch_users){
                $user_type = $fetch_users['user_type'];

                // Count properties only for sellers
                $total_properties = 0;
                if($user_type == 'seller'){
                    $count_property = $conn->prepare("SELECT * FROM `property` WHERE user_id = ?");
                    $count_property->execute([$fetch_users['id']]);
                    $total_properties = $count_property->rowCount();
                }
    ?>
    <div class="box">
        <p>type : <span style="text-transform:capitalize; color: <?= $user_type == 'seller' ? '#e74c3c' : '#27ae60'; ?>;"><?= $user_type; ?></span></p>
        <p>name : <span><?= $fetch_users['name']; ?></span></p>
        <p>number : <a href="tel:<?= $fetch_users['number']; ?>"><?= $fetch_users['number']; ?></a></p>
        <p>email : <a href="mailto:<?= $fetch_users['email']; ?>"><?= $fetch_users['email']; ?></a></p>
        <?php if($user_type == 'seller'){ ?>
            <p>properties listed : <span><?= $total_properties; ?></span></p>
        <?php } ?>
        <form action="" method="POST">
            <input type="hidden" name="delete_id" value="<?= $fetch_users['id']; ?>">
            <input type="hidden" name="delete_type" value="<?= $user_type; ?>">
            <input type="submit" value="delete user" onclick="return confirm('delete this user?');" name="delete" class="delete-btn">
        </form>
    </div>
    <?php
            }
        } else {
            echo '<p class="empty">no users found!</p>';
        }
    ?>
    </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="../js/admin_script.js"></script>
<?php include '../components/message.php'; ?>
</body>
</html>