<?php

include '../components/connect.php';

if(isset($_COOKIE['admin_id'])){
   $admin_id = $_COOKIE['admin_id'];
}else{
   $admin_id = '';
   header('location:login.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Dashboard</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>
   
<!-- header section starts  -->
<?php include '../components/admin_header.php'; ?>
<!-- header section ends -->

<!-- dashboard section starts  -->

<section class="dashboard">

   <h1 class="heading">dashboard</h1>

   <div class="box-container">

   <div class="box">
      <?php
         $select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ? LIMIT 1");
         $select_profile->execute([$admin_id]);
         $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
      ?>
      <h3>welcome!</h3>
      <p><?= $fetch_profile['name']; ?></p>
      <a href="update.php" class="btn">update profile</a>
   </div>

   <div class="box">
      <?php
         $select_listings = $conn->prepare("SELECT * FROM `property`");
         $select_listings->execute();
         $count_listings = $select_listings->rowCount();
      ?>
      <h3><?= $count_listings; ?></h3>
      <p>property posted</p>
      <a href="listings.php" class="btn">view listings</a>
   </div>

   <div class="box">
      <?php
$select_buyers = $conn->prepare("SELECT * FROM `buyers`");
$select_buyers->execute();
$count_buyers = $select_buyers->rowCount();

$select_sellers = $conn->prepare("SELECT * FROM `sellers`");
$select_sellers->execute();
$count_sellers = $select_sellers->rowCount();

$count_users = $count_buyers + $count_sellers;
      ?>
      <h3><?= $count_users; ?></h3>
      <p>total users</p>
      <a href="users.php" class="btn">view users</a>
   </div>

   <div class="box">
      <?php
         $select_approved = $conn->prepare("SELECT * FROM `property` WHERE status = 'approved'");
         $select_approved->execute();
         $count_approved = $select_approved->rowCount();
      ?>
      <h3><?= $count_approved; ?></h3>
      <p>View Approved</p>
      <a href="listings.php?filter=approved" class="btn">View Approved</a>
   </div>

   
   <div class="box">
      <?php
         $select_admins = $conn->prepare("SELECT * FROM `admins`");
         $select_admins->execute();
         $count_admins = $select_admins->rowCount();
         ?>
      <h3><?= $count_admins; ?></h3>
      <p>total admins</p>
      <a href="admins.php" class="btn">view admins</a>
   </div>
   
   <div class="box">
      <?php
         $count_messages = 0;
         try {
            $select_messages = $conn->prepare("SELECT * FROM `messages`");
            $select_messages->execute();
            $count_messages = $select_messages->rowCount();
         } catch (PDOException $e) {
            $count_messages = 0;
         }
         ?>
      <h3><?= $count_messages; ?></h3>
      <p>new messages</p>
      <a href="messages.php" class="btn">view messages</a>
   </div>

   <div class="box">
    <?php
       $select_pending = $conn->prepare("SELECT * FROM `property` WHERE approved = 'pending'");
       $select_pending->execute();
       $count_pending = $select_pending->rowCount();
    ?>
    <h3><?= $count_pending; ?></h3>
    <p>Pending Approval</p>
    <a href="listings.php?filter=pending" class="btn">View pending</a>
   </div>
   
</div>

</section>


<!-- dashboard section ends -->



<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<!-- custom js file link  -->
<script src="../js/admin_script.js"></script>

<?php include '../components/message.php'; ?>

</body>
</html>