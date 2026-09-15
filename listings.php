<?php
include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

include 'components/save_send.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>All Listings</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- listings section starts  -->

<section class="listings">

   <span class="eyebrow">Browse our full portfolio</span>
   <h1 class="heading">All listings</h1>

   <div class="box-container">
      <?php
         // select only approved properties, ordered by date desc
         $select_properties = $conn->prepare("SELECT * FROM `property` WHERE approved = 1 ORDER BY date DESC");
         $select_properties->execute();
         if($select_properties->rowCount() > 0){
            while($fetch_property = $select_properties->fetch(PDO::FETCH_ASSOC)){

               // fetch property owner
               $select_user = $conn->prepare("SELECT * FROM `sellers` WHERE id = ?");
               $select_user->execute([$fetch_property['user_id']]);
               $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

               // count images (always count image_01 as present when not empty)
               $image_count_02 = !empty($fetch_property['image_02']) ? 1 : 0;
               $image_count_03 = !empty($fetch_property['image_03']) ? 1 : 0;
               $image_count_04 = !empty($fetch_property['image_04']) ? 1 : 0;
               $image_count_05 = !empty($fetch_property['image_05']) ? 1 : 0;
               $image_01_present = !empty($fetch_property['image_01']) ? 1 : 0;
               $total_images = $image_01_present + $image_count_02 + $image_count_03 + $image_count_04 + $image_count_05;

               // check if saved only when user is logged in
               $is_saved = false;
               if($user_id !== ''){
                  try {
                     $select_saved = $conn->prepare("SELECT * FROM `saved` WHERE property_id = ? AND user_id = ?");
                     $select_saved->execute([$fetch_property['id'], $user_id]);
                     $is_saved = $select_saved->rowCount() > 0;
                  } catch (PDOException $e) {
                     $is_saved = false;
                  }
               }

               // safe values for output
               $prop_id = $fetch_property['id'];
               $prop_image = !empty($fetch_property['image_01']) ? htmlspecialchars($fetch_property['image_01'], ENT_QUOTES) : 'placeholder.jpg';
               $owner_initial = isset($fetch_user['name']) ? htmlspecialchars(substr($fetch_user['name'], 0, 1), ENT_QUOTES) : 'U';
               $owner_name = isset($fetch_user['name']) ? htmlspecialchars($fetch_user['name'], ENT_QUOTES) : 'Unknown';
               $prop_date = htmlspecialchars($fetch_property['date'] ?? '', ENT_QUOTES);
               $prop_price = htmlspecialchars($fetch_property['price'] ?? '', ENT_QUOTES);
               $prop_name = htmlspecialchars($fetch_property['property_name'] ?? '', ENT_QUOTES);
               $prop_address = htmlspecialchars($fetch_property['address'] ?? '', ENT_QUOTES);
               $prop_type = htmlspecialchars($fetch_property['type'] ?? '', ENT_QUOTES);
               $prop_offer = htmlspecialchars($fetch_property['offer'] ?? '', ENT_QUOTES);
               $prop_bhk = htmlspecialchars($fetch_property['bhk'] ?? '', ENT_QUOTES);
               $prop_status = htmlspecialchars($fetch_property['status'] ?? '', ENT_QUOTES);
               $prop_furnished = htmlspecialchars($fetch_property['furnished'] ?? '', ENT_QUOTES);
               $prop_carpet = htmlspecialchars($fetch_property['carpet'] ?? '', ENT_QUOTES);
      ?>
      <form action="" method="POST">
         <div class="box">
            <input type="hidden" name="property_id" value="<?= $prop_id; ?>">
            <?php if($user_id !== ''): ?>
               <!-- <?php if($is_saved): ?>
                  <button type="submit" name="save" class="save"><i class="fas fa-heart"></i><span>Saved</span></button>
               <?php else: ?>
                  <button type="submit" name="save" class="save"><i class="far fa-heart"></i><span>Save</span></button>
               <?php endif; ?> -->
            <?php else: ?>
               <!-- if not logged in, prompt to login when trying to save -->
               <a href="user_login.php" class="save"><i class="far fa-heart"></i><span>Save</span></a>
            <?php endif; ?>

            <div class="thumb">
               <p class="total-images"><i class="far fa-image"></i><span><?= $total_images; ?></span></p>
               <img src="uploaded_files/<?= $prop_image; ?>" alt="<?= $prop_name; ?>">
            </div>
            <div class="admin">
               <h3><?= $owner_initial; ?></h3>
               <div>
                  <p><?= $owner_name; ?></p>
                  <span><?= $prop_date; ?></span>
               </div>
            </div>
         </div>

         <div class="box">
            <div class="price"><i class="fas fa-rupee-sign"></i><span><?= $prop_price; ?></span></div>
            <h3 class="name"><?= $prop_name; ?></h3>
            <p class="location"><i class="fas fa-map-marker-alt"></i><span><?= $prop_address; ?></span></p>
            <div class="flex">
               <p><i class="fas fa-house"></i><span><?= $prop_type; ?></span></p>
               <p><i class="fas fa-tag"></i><span><?= $prop_offer; ?></span></p>
               <p><i class="fas fa-bed"></i><span><?= $prop_bhk; ?> BHK</span></p>
               <p><i class="fas fa-trowel"></i><span><?= $prop_status; ?></span></p>
               <p><i class="fas fa-couch"></i><span><?= $prop_furnished; ?></span></p>
               <p><i class="fas fa-maximize"></i><span><?= $prop_carpet; ?> sqft</span></p>
            </div>
            <div class="flex-btn">
               <a href="view_property.php?get_id=<?= $prop_id; ?>" class="btn">View property</a>
               <!-- <input type="submit" value="send enquiry" name="send" class="btn"> -->
            </div>
         </div>
      </form>
      <?php
            } // end while
         } else {
            echo '<p class="empty">no properties added yet! <a href="post_property.php" style="margin-top:1.5rem;" class="btn">add new</a></p>';
         }
      ?>
   </div>

</section>

<!-- listings section ends -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<?php include 'components/footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<?php include 'components/message.php'; ?>

</body>
</html>
