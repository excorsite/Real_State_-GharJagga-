<?php  
include 'components/connect.php';

if(!isset($_COOKIE['user_id'])){
    header('location:login.php');
    exit;
}

$user_id = $_COOKIE['user_id'];

// Get user type from cookie
$user_type = isset($_COOKIE['user_type']) ? $_COOKIE['user_type'] : '';

// Only sellers can access this dashboard
if($user_type !== 'seller'){
    header('location:home.php');
    exit;
}

// Fetch from sellers table
$select_user = $conn->prepare("SELECT * FROM `sellers` WHERE id = ? LIMIT 1");
$select_user->execute([$user_id]);
$user = $select_user->fetch(PDO::FETCH_ASSOC);

if(!$user){
    header('location:login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="dashboard">
    <span class="eyebrow">Seller Control Panel</span>
    <h1 class="heading">Welcome back, <?= htmlspecialchars(explode(' ', $user['name'])[0]); ?></h1>
    <div class="box-container">

        <div class="box">
            <i class="fas fa-user-tie" style="font-size:2.6rem;color:var(--accent-color);margin-bottom:1rem;"></i>
            <h3 style="font-size:2rem;"><?= htmlspecialchars($user['name']); ?></h3>
            <p>Your seller profile</p>
            <a href="update.php" class="btn">Update Profile</a>
        </div>

        <div class="box">
            <?php
                $count_properties = $conn->prepare("SELECT * FROM `property` WHERE user_id = ? AND approved = 1");
                $count_properties->execute([$user_id]);
                $total_properties = $count_properties->rowCount();
            ?>
            <i class="fas fa-building" style="font-size:2.6rem;color:var(--main-color);margin-bottom:1rem;"></i>
            <h3><?= $total_properties; ?></h3>
            <p>Total Properties Listed</p>
            <a href="my_listings.php" class="btn">View My Listings</a>
        </div>

        <div class="box">
            <?php
                $count_pending = $conn->prepare("SELECT * FROM `property` WHERE user_id = ? AND approved = 0");
                $count_pending->execute([$user_id]);
                $total_pending = $count_pending->rowCount();
            ?>
            <i class="fas fa-hourglass-half" style="font-size:2.6rem;color:var(--accent-color);margin-bottom:1rem;"></i>
            <h3><?= $total_pending; ?></h3>
            <p>Pending Approval</p>
            <a href="my_listings.php" class="btn">View Pending Properties</a>
        </div>

        <div class="box">
            <i class="fas fa-plus-circle" style="font-size:2.6rem;color:var(--main-color);margin-bottom:1rem;"></i>
            <h1>Post New Property</h1>
            <p>Add a new house, apartment, or land</p>
            <a href="post_property.php" class="btn">Post Property Now</a>
        </div>

    </div>
</section>

<?php include 'components/footer.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="js/script.js"></script>
<?php include 'components/message.php'; ?>
</body>
</html>