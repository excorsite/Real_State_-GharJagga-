<?php  

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

include 'components/save_send.php';
include 'components/algorithms.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Search Page</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- search filter section starts  -->

<!-- <section class="filters" style="padding-bottom: 0;">

   <form action="" method="post">
      <div id="close-filter"><i class="fas fa-times"></i></div>
      <h3>Filter your search</h3>
         
         <div class="flex">
            <div class="box">
               <p>Enter location</p>
               
                <select name="type" class="input" required>
               <option value="Basundhara-Kathmandu">Basundhara-Kathmandu</option>
               <option value="Lolang-Kathmandu">Lolang-Kathmandu</option>
               <option value="Banasthali-Kathmandu">Banasthali-Kathmandu</option>
               <option value="Baneshwor-Kathmandu">Baneshwor-Kathmandu</option>
               <option value="Manamaiju-Kathmandu">Manamaiju-Kathmandu</option>
               <option value="Swayambhu-Kathmandu">Swayambhu-Kathmandu</option>
             </select>
            </div>

            <div class="box">
               <p>Offer type</p>
               <select name="offer" class="input" required>
                  <option value="sale">Sale</option>
                  <option value="resale">Resale</option>
              
               </select>
            </div>
            <div class="box">
               <p>Property type</p>
               <select name="type" class="input" required>
                  <option value="land">Land</option>
                  <option value="home">Home</option>
                  
               </select>
            </div>
            <div class="box">
               <p>How many BHK</p>
               <select name="bhk" class="input" required>
                  <option value="1">1 BHK</option>
                  <option value="2">2 BHK</option>
                  <option value="3">3 BHK</option>
                  <option value="4">4 BHK</option>
                  
               </select>
            </div>
            <div class="box">
               <p>Minimum budget</p>
               <select name="min" class="input" required>
               
                  <option value="10000000">1 Cr</option>
                  <option value="15000000">1.5 Cr</option>
                  <option value="20000000">2 Cr</option>
                  
               </select>
            </div>
            <div class="box">
               <p>Maximum budget</p>
               <select name="max" class="input" required>
                  
                  <option value="30000000">3 Cr</option>
                  <option value="40000000">4 Cr</option>
                  <option value="50000000">5 Cr</option>
                  
               </select>
            </div>
            <div class="box">
               <p>Status</p>
               <select name="status" class="input" required>
                  <option value="ready to move">Ready to move</option>
                  <option value="under construction">Under construction</option>
               </select>
            </div>
            <div class="box">
               <p>Furnished</p>
               <select name="furnished" class="input" required>
                  <option value="unfurnished">Unfurnished</option>
                  <option value="furnished">Furnished</option>
                  <option value="semi-furnished">Semi-furnished</option>
               </select>
            </div>
         </div>
         <input type="submit" value="search property" name="filter_search" class="btn">
   </form>

</section> -->

<!-- search filter section ends -->

<div id="filter-btn" class="fas fa-filter"></div>

<?php

// ---- results are gathered into $result_rows, then ranked by the
// ---- search relevance ranking algorithm (see components/algorithms.php)
$result_rows = [];
$search_criteria = [];
$is_ranked_search = false;

if(isset($_POST['h_search'])){

   $h_location = $_POST['h_location'] ?? '';
   $h_location = filter_var($h_location, FILTER_SANITIZE_STRING);

   $h_type = $_POST['h_type'] ?? '';
   $h_type = filter_var($h_type, FILTER_SANITIZE_STRING);

   $h_offer = $_POST['h_offer'] ?? '';
   $h_offer = filter_var($h_offer, FILTER_SANITIZE_STRING);

   $h_min = $_POST['h_min'] ?? 0;
   $h_min = filter_var($h_min, FILTER_SANITIZE_STRING);

   $h_max = $_POST['h_max'] ?? 999999999;
   $h_max = filter_var($h_max, FILTER_SANITIZE_STRING);

   $select_properties = $conn->prepare("SELECT * FROM `property` WHERE approved = 1 AND address LIKE '%{$h_location}%' AND type LIKE '%{$h_type}%' AND offer LIKE '%{$h_offer}%' AND price BETWEEN $h_min AND $h_max ORDER BY date DESC");
   $select_properties->execute();
   $result_rows = $select_properties->fetchAll(PDO::FETCH_ASSOC);

   $search_criteria = ['location' => $h_location, 'type' => $h_type, 'offer' => $h_offer, 'min' => $h_min, 'max' => $h_max];
   $is_ranked_search = true;

}elseif(isset($_POST['filter_search'])){

   $location = $_POST['location'];
   $location = filter_var($location, FILTER_SANITIZE_STRING);
   $type = $_POST['type'];
   $type = filter_var($type, FILTER_SANITIZE_STRING);
   $offer = $_POST['offer'];
   $offer = filter_var($offer, FILTER_SANITIZE_STRING);
   $bhk = $_POST['bhk'];
   $bhk = filter_var($bhk, FILTER_SANITIZE_STRING);
   $min = $_POST['min'];
   $min = filter_var($min, FILTER_SANITIZE_STRING);
   $max = $_POST['max'];
   $max = filter_var($max, FILTER_SANITIZE_STRING);
   $status = $_POST['status'];
   $status = filter_var($status, FILTER_SANITIZE_STRING);
   $furnished = $_POST['furnished'];
   $furnished = filter_var($furnished, FILTER_SANITIZE_STRING);

   $select_properties = $conn->prepare("SELECT * FROM `property` WHERE approved = 1 AND address LIKE '%{$location}%' AND type LIKE '%{$type}%' AND offer LIKE '%{$offer}%' AND bhk LIKE '%{$bhk}%' AND status LIKE '%{$status}%' AND furnished LIKE '%{$furnished}%' AND price BETWEEN $min AND $max ORDER BY date DESC");
   $select_properties->execute();
   $result_rows = $select_properties->fetchAll(PDO::FETCH_ASSOC);

   $search_criteria = ['location' => $location, 'type' => $type, 'offer' => $offer, 'bhk' => $bhk, 'min' => $min, 'max' => $max, 'status' => $status, 'furnished' => $furnished];
   $is_ranked_search = true;

}else{
   $select_properties = $conn->prepare("SELECT * FROM `property` WHERE approved = 1 ORDER BY date DESC LIMIT 6");
   $select_properties->execute();
   $result_rows = $select_properties->fetchAll(PDO::FETCH_ASSOC);
}

// apply the relevance-ranking algorithm on genuine searches
if($is_ranked_search && count($result_rows) > 0){
   $result_rows = rank_search_results($result_rows, $search_criteria);
}

?>

<!-- listings section starts  -->

<section class="listings">

   <?php 
      if(isset($_POST['h_search']) or isset($_POST['filter_search'])){
         echo '<h1 class="heading">search results</h1>';
      }else{
         echo '<h1 class="heading">latest listings</h1>';
      }
   ?>

   <?php if($is_ranked_search && count($result_rows) > 0){ ?>
   <p style="text-align:center;font-size:1.4rem;color:var(--light-color);margin-bottom:2rem;">
      <span class="smart-tag"><i class="fas fa-sort-amount-down"></i> Sorted by Smart Match</span>
      &nbsp; results ranked by how closely they match your search criteria
   </p>
   <?php } ?>

   <div class="box-container">
      <?php
         $total_images = 0;
         if(count($result_rows) > 0){
            foreach($result_rows as $fetch_property){
            $select_user = $conn->prepare("SELECT * FROM `sellers` WHERE id = ?");
            $select_user->execute([$fetch_property['user_id']]);
            $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

            if(!empty($fetch_property['image_02'])){
               $image_coutn_02 = 1;
            }else{
               $image_coutn_02 = 0;
            }
            if(!empty($fetch_property['image_03'])){
               $image_coutn_03 = 1;
            }else{
               $image_coutn_03 = 0;
            }
            if(!empty($fetch_property['image_04'])){
               $image_coutn_04 = 1;
            }else{
               $image_coutn_04 = 0;
            }
            if(!empty($fetch_property['image_05'])){
               $image_coutn_05 = 1;
            }else{
               $image_coutn_05 = 0;
            }

            $total_images = (1 + $image_coutn_02 + $image_coutn_03 + $image_coutn_04 + $image_coutn_05);

            $is_saved = false;
            try {
               $select_saved = $conn->prepare("SELECT * FROM `saved` WHERE property_id = ? and user_id = ?");
               $select_saved->execute([$fetch_property['id'], $user_id]);
               $is_saved = $select_saved->rowCount() > 0;
            } catch (PDOException $e) {
               $is_saved = false;
            }

      ?>
      <form action="" method="POST">
         <div class="box">
            <input type="hidden" name="property_id" value="<?= $fetch_property['id']; ?>">
            <?php
               if($is_saved){
            ?>
            <button type="submit" name="save" class="save"><i class="fas fa-heart"></i><span>saved</span></button>
            <?php
               }else{ 
            ?>
            <button type="submit" name="save" class="save"><i class="far fa-heart"></i><span>save</span></button>
            <?php
               }
            ?>
            <?php if($is_ranked_search && isset($fetch_property['match_score'])){
               $ms = (int) $fetch_property['match_score'];
               $ms_class = $ms >= 60 ? 'high' : ($ms >= 30 ? 'mid' : 'low');
            ?>
            <span class="match-score <?= $ms_class; ?>"><i class="fas fa-chart-simple"></i> <?= $ms; ?>% Match</span>
            <?php } ?>
            <div class="thumb">
               <p class="total-images"><i class="far fa-image"></i><span><?= $total_images; ?></span></p> 
               <img src="uploaded_files/<?= $fetch_property['image_01']; ?>" alt="">
            </div>
            <div class="admin">
               <?php if($fetch_user){ ?>
                  <h3><?= htmlspecialchars(substr($fetch_user['name'], 0, 1)); ?></h3>
                  <div>
                     <p><?= htmlspecialchars($fetch_user['name']); ?></p>
                     <span><?= $fetch_property['date']; ?></span>
                  </div>
               <?php } else { ?>
                  <h3>U</h3>
                  <div>
                     <p>Unknown Seller</p>
                     <span><?= $fetch_property['date']; ?></span>
                  </div>
               <?php } ?>
            </div>
         </div>
         <div class="box">
            <div class="price"><i class="fas fa-Nepali-rupee-sign"></i><span><?= $fetch_property['price']; ?></span></div>
            <h3 class="name"><?= $fetch_property['property_name']; ?></h3>
            <p class="location"><i class="fas fa-map-marker-alt"></i><span><?= $fetch_property['address']; ?></span></p>
            <div class="flex">
               <p><i class="fas fa-house"></i><span><?= $fetch_property['type']; ?></span></p>
               <p><i class="fas fa-tag"></i><span><?= $fetch_property['offer']; ?></span></p>
               <p><i class="fas fa-bed"></i><span><?= $fetch_property['bhk']; ?> BHK</span></p>
               <p><i class="fas fa-trowel"></i><span><?= $fetch_property['status']; ?></span></p>
               <p><i class="fas fa-couch"></i><span><?= $fetch_property['furnished']; ?></span></p>
               <p><i class="fas fa-maximize"></i><span><?= $fetch_property['carpet']; ?>sqft</span></p>
            </div>
            <div class="flex-btn">
               <a href="view_property.php?get_id=<?= $fetch_property['id']; ?>" class="btn">view property</a>
               <input type="submit" value="send enquiry" name="send" class="btn">
            </div>
         </div>
      </form>
      <?php
         }
      }else{
         echo '<p class="empty">no results found!</p>';
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

<script>

document.querySelector('#filter-btn').onclick = () =>{
   document.querySelector('.filters').classList.add('active');
}

document.querySelector('#close-filter').onclick = () =>{
   document.querySelector('.filters').classList.remove('active');
}

</script>

</body>
</html>