<?php  
include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

if(isset($_GET['get_id'])){
   $get_id = $_GET['get_id'];
}else{
   $get_id = '';
   header('location:home.php');
}

// Get current user's type (buyer or seller)
$user_type = '';
if($user_id != ''){
   // Check if user exists in sellers table (means they're a seller)
   $select_seller = $conn->prepare("SELECT id FROM `sellers` WHERE id = ?");
   $select_seller->execute([$user_id]);
   
   if($select_seller->rowCount() > 0){
      $user_type = 'seller';
   } else {
      $user_type = 'buyer';
   }
}

include 'components/save_send.php';
include 'components/buy_send.php';
include 'components/inquiry_send.php';
include 'components/algorithms.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>View Property</title>
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      .property-type-badge {
         display: inline-block;
         padding: 5px 15px;
         background: #e74c3c;
         color: white;
         border-radius: 50px;
         font-size: 14px;
         font-weight: bold;
         margin-left: 10px;
      }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="view-property">
   <h1 class="heading">Property Details</h1> 

   <?php
      $select_properties = $conn->prepare("SELECT * FROM `property` WHERE id = ?"); 
      $select_properties->execute([$get_id]);
      if($select_properties->rowCount() > 0){
         while($fetch_property = $select_properties->fetch(PDO::FETCH_ASSOC)){
            $property_id = $fetch_property['id'];
            $property_type = $fetch_property['type']; // home or land

            $select_user = $conn->prepare("SELECT * FROM `sellers` WHERE id = ?");
            $select_user->execute([$fetch_property['user_id']]);
            $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

            $is_saved_property = false;
            try {
               $select_saved = $conn->prepare("SELECT * FROM `saved` WHERE property_id = ? AND user_id = ?");
               $select_saved->execute([$property_id, $user_id]);
               $is_saved_property = $select_saved->rowCount() > 0;
            } catch (PDOException $e) {
               $is_saved_property = false;
            }
   ?>
   <div class="details">
      <div class="swiper images-container">
         <div class="swiper-wrapper">
            <img src="uploaded_files/<?= $fetch_property['image_01']; ?>" alt="" class="swiper-slide">
            <?php if(!empty($fetch_property['image_02'])){ ?>
               <img src="uploaded_files/<?= $fetch_property['image_02']; ?>" alt="" class="swiper-slide">
            <?php } ?>
            <?php if(!empty($fetch_property['image_03'])){ ?>
               <img src="uploaded_files/<?= $fetch_property['image_03']; ?>" alt="" class="swiper-slide">
            <?php } ?>
            <?php if(!empty($fetch_property['image_04'])){ ?>
               <img src="uploaded_files/<?= $fetch_property['image_04']; ?>" alt="" class="swiper-slide">
            <?php } ?>
            <?php if(!empty($fetch_property['image_05'])){ ?>
               <img src="uploaded_files/<?= $fetch_property['image_05']; ?>" alt="" class="swiper-slide">
            <?php } ?>
         </div>
         <div class="swiper-pagination"></div>
      </div>

      <h3 class="name">
         <?= $fetch_property['property_name']; ?>
         <span class="property-type-badge">
            <?= ucfirst($property_type); ?>
         </span>
      </h3>
      <p class="location"><i class="fas fa-map-marker-alt"></i><span><?= $fetch_property['address']; ?></span></p>
      
      <div class="info">
         <p><i class="fa-solid fa-tag"></i><span>Rs. <?= number_format($fetch_property['price']); ?></span></p>
         <?php if($fetch_user){ ?>
            <p><i class="fas fa-user"></i><span><?= htmlspecialchars($fetch_user['name']); ?></span></p>
            <p><i class="fas fa-phone"></i><a href="tel:<?= htmlspecialchars($fetch_user['number']); ?>"><?= htmlspecialchars($fetch_user['number']); ?></a></p>
         <?php } else { ?>
            <p><i class="fas fa-user"></i><span>Unknown Seller</span></p>
         <?php } ?>
         <p><i class="fas fa-house"></i><span><?= ucwords(str_replace('_', ' ', $fetch_property['offer'])); ?></span></p>
         <p><i class="fas fa-calendar"></i><span><?= date('d M Y', strtotime($fetch_property['date'])); ?></span></p>
      </div>

      <!-- ========== DYNAMIC DETAILS BASED ON PROPERTY TYPE ========== -->
      <h3 class="title">Property Details</h3>
      <div class="flex">
         <div class="box">

            <?php if($property_type == 'home'){ ?>
               <!-- Home Specific Fields -->
               <?php if($fetch_property['bhk']){ ?>
                  <p><i class="fas fa-home"></i><span><?= $fetch_property['bhk']; ?> BHK</span></p>
               <?php } ?>
               <?php if($fetch_property['bedroom']){ ?>
                  <p><i class="fas fa-bed"></i><span><?= $fetch_property['bedroom']; ?> Bedroom<?= $fetch_property['bedroom'] > 1 ? 's' : ''; ?></span></p>
               <?php } ?>
               <?php if($fetch_property['bathroom']){ ?>
                  <p><i class="fas fa-bath"></i><span><?= $fetch_property['bathroom']; ?> Bathroom<?= $fetch_property['bathroom'] > 1 ? 's' : ''; ?></span></p>
               <?php } ?>
               <?php if($fetch_property['balcony']){ ?>
                  <p><i class="fas fa-sun"></i><span><?= $fetch_property['balcony']; ?> Balcony<?= $fetch_property['balcony'] > 1 ? 's' : ''; ?></span></p>
               <?php } ?>
               <?php if($fetch_property['carpet']){ ?>
                  <p><i class="fas fa-ruler-combined"></i><span><?= $fetch_property['carpet']; ?> sqft (Carpet)</span></p>
               <?php } ?>
               <?php if($fetch_property['age']){ ?>
                  <p><i class="fas fa-clock"></i><span><?= $fetch_property['age']; ?> Years Old</span></p>
               <?php } ?>
               <?php if($fetch_property['furnished']){ ?>
                  <p><i class="fas fa-couch"></i><span><?= ucfirst(str_replace('-', ' ', $fetch_property['furnished'])); ?></span></p>
               <?php } ?>

            <?php } elseif($property_type == 'land'){ ?>
               <!-- Land Specific Fields -->
               <?php if($fetch_property['total_area']){ ?>
                  <p><i class="fas fa-vector-square"></i><span><?= $fetch_property['total_area']; ?> sqft (Total Area)</span></p>
               <?php } ?>
               <?php if($fetch_property['road_access']){ ?>
                  <p><i class="fas fa-road"></i><span>Road: <?= $fetch_property['road_access']; ?></span></p>
               <?php } ?>
               <?php if($fetch_property['facing']){ ?>
                  <p><i class="fas fa-compass"></i><span>Facing: <?= $fetch_property['facing']; ?></span></p>
               <?php } ?>
               <?php if($fetch_property['plot_shape']){ ?>
                  <p><i class="fas fa-shapes"></i><span>Shape: <?= $fetch_property['plot_shape']; ?></span></p>
               <?php } ?>
               <?php if($fetch_property['ana']){ ?>
                  <p><i class="fas fa-chart-area"></i><span><?= $fetch_property['ana']; ?> Ana</span></p>
               <?php } ?>
               <?php if($fetch_property['ownership']){ ?>
                  <p><i class="fas fa-user-tie"></i><span>Ownership: <?= $fetch_property['ownership']; ?></span></p>
               <?php } ?>
               <?php if($fetch_property['registration']){ ?>
                  <p><i class="fas fa-file-contract"></i><span><?= $fetch_property['registration']; ?></span></p>
               <?php } ?>
            <?php } ?>

            <!-- Common Fields -->
            <p><i>Status :</i><span><?= ucfirst(str_replace('_', ' ', $fetch_property['status'])); ?></span></p>
            <?php if($fetch_property['deposite'] && $fetch_property['deposite'] > 0){ ?>
               <p><i>Deposit :</i><span>Rs. <?= number_format($fetch_property['deposite']); ?></span></p>
            <?php } ?>
            <p><i>Loan :</i><span><?= ucfirst(str_replace('_', ' ', $fetch_property['loan'])); ?></span></p>
         </div>

         <div class="box">
            <?php if($property_type == 'home' && $fetch_property['total_floors']){ ?>
               <p><i class="fas fa-building"></i><span>Total Floors: <?= $fetch_property['total_floors']; ?></span></p>
            <?php } ?>
            <?php if($property_type == 'home' && $fetch_property['room_floor']){ ?>
               <p><i class="fas fa-layer-group"></i><span>Floor: <?= $fetch_property['room_floor']; ?><?= $fetch_property['room_floor'] == 0 ? ' (Ground)' : ($fetch_property['room_floor'] == 1 ? 'st' : ($fetch_property['room_floor'] == 2 ? 'nd' : ($fetch_property['room_floor'] == 3 ? 'rd' : 'th'))); ?> Floor</span></p>
            <?php } ?>
         </div>
      </div>

      <!-- Amenities -->
      <h3 class="title">Amenities</h3>
      <div class="flex">
         <div class="box">
            <p><i class="fas fa-<?= $fetch_property['lift'] == 'yes' ? 'check text-success' : 'times text-danger'; ?>"></i><span>Lift Available</span></p>
            <p><i class="fas fa-<?= $fetch_property['security_guard'] == 'yes' ? 'check text-success' : 'times text-danger'; ?>"></i><span>Security Guard</span></p>
            <p><i class="fas fa-<?= $fetch_property['play_ground'] == 'yes' ? 'check text-success' : 'times text-danger'; ?>"></i><span>Play Ground</span></p>
            <p><i class="fas fa-<?= $fetch_property['garden'] == 'yes' ? 'check text-success' : 'times text-danger'; ?>"></i><span>Garden</span></p>
            <p><i class="fas fa-<?= $fetch_property['water_supply'] == 'yes' ? 'check text-success' : 'times text-danger'; ?>"></i><span>Water Supply</span></p>
            <p><i class="fas fa-<?= $fetch_property['power_backup'] == 'yes' ? 'check text-success' : 'times text-danger'; ?>"></i><span>Power Backup</span></p>
         </div>
         <div class="box">
            <p><i class="fas fa-<?= $fetch_property['parking_area'] == 'yes' ? 'check text-success' : 'times text-danger'; ?>"></i><span>Parking</span></p>
            <p><i class="fas fa-<?= $fetch_property['gym'] == 'yes' ? 'check text-success' : 'times text-danger'; ?>"></i><span>Gym</span></p>
            <p><i class="fas fa-<?= $fetch_property['hospital'] == 'yes' ? 'check text-success' : 'times text-danger'; ?>"></i><span>Hospital Nearby</span></p>
            <p><i class="fas fa-<?= $fetch_property['school'] == 'yes' ? 'check text-success' : 'times text-danger'; ?>"></i><span>School Nearby</span></p>
            <p><i class="fas fa-<?= $fetch_property['market_area'] == 'yes' ? 'check text-success' : 'times text-danger'; ?>"></i><span>Market Nearby</span></p>
         </div>
      </div>

      <h3 class="title">Description</h3>
      <p class="description"><?= nl2br($fetch_property['description']); ?></p>

      <!-- ===== Smart Price Estimate (light price-estimation algorithm) ===== -->
      <?php
         $price_estimate = estimate_property_price($conn, $fetch_property);
         if($price_estimate){
            $verdict = $price_estimate['verdict'];
            $verdict_label = [
               'fair' => 'Fairly Priced',
               'high' => 'Priced Above Market',
               'low'  => 'Great Deal — Below Market',
            ][$verdict];
      ?>
      <div class="price-estimate-box">
         <h4><span class="smart-tag"><i class="fas fa-microchip"></i> Smart Price Estimate</span></h4>
         <div class="estimate-value">Rs. <?= number_format($price_estimate['estimated_price']); ?></div>
         <p class="estimate-note">
            Estimated using the average price per sqft of <?= $price_estimate['sample_size']; ?> comparable
            <?= htmlspecialchars($property_type); ?> listing<?= $price_estimate['sample_size'] > 1 ? 's' : ''; ?> nearby.
            Listed price is <?= abs($price_estimate['diff_pct']); ?>% <?= $price_estimate['diff_pct'] >= 0 ? 'above' : 'below'; ?> this estimate.
         </p>
         <span class="price-verdict <?= $verdict; ?>"><i class="fas fa-tag"></i> <?= $verdict_label; ?></span>
      </div>
      <?php } ?>

      <?php if($user_type == 'buyer' && $user_id != ''){ ?>
      <form action="" method="post" class="flex-btn">
         <input type="hidden" name="property_id" value="<?= $property_id; ?>">
         <button type="submit" name="buy_property" class="btn">
            <i class="fa-solid fa-landmark"></i> Buy Now
         </button>
      </form>
      <?php } elseif($user_type != 'seller' && $user_id == ''){ ?>
      <div style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin-top: 20px;">
         <p style="color: #0c5460; margin: 0;"><i class="fas fa-info-circle"></i> <a href="login.php">Login as a buyer</a> to purchase this property.</p>
      </div>
      <?php } ?>
   </div>

   <!-- ===== Similar Properties (content-based recommendation algorithm) ===== -->
   <?php
      $recommended = get_recommended_properties($conn, $fetch_property, 3);
      if(count($recommended) > 0){
   ?>
   <div class="similar-properties">
      <h3 class="title"><span class="smart-tag"><i class="fas fa-wand-magic-sparkles"></i> Recommended For You</span></h3>
      <div class="box-container">
         <?php foreach($recommended as $rec){
            $rec_img = !empty($rec['image_01']) ? htmlspecialchars($rec['image_01'], ENT_QUOTES) : 'placeholder.jpg';
            $score = (int) $rec['similarity_score'];
            $score_class = $score >= 60 ? 'high' : ($score >= 30 ? 'mid' : 'low');
         ?>
         <form action="">
            <div class="box">
               <span class="match-score <?= $score_class; ?>"><i class="fas fa-chart-simple"></i> <?= min(100,$score); ?>% Match</span>
               <div class="thumb">
                  <img src="uploaded_files/<?= $rec_img; ?>" alt="">
               </div>
            </div>
            <div class="box">
               <div class="price"><i class="fas fa-indian-rupee-sign"></i><span><?= htmlspecialchars($rec['price']); ?></span></div>
               <h3 class="name"><?= htmlspecialchars($rec['property_name']); ?></h3>
               <p class="location"><i class="fas fa-map-marker-alt"></i><span><?= htmlspecialchars($rec['address']); ?></span></p>
               <div class="flex-btn">
                  <a href="view_property.php?get_id=<?= $rec['id']; ?>" class="btn">View property</a>
               </div>
            </div>
         </form>
         <?php } ?>
      </div>
   </div>
   <?php } ?>

   <?php
         }
      } else {
         echo '<p class="empty">Property not found! <a href="post_property.php" class="btn">Post New Property</a></p>';
      }
   ?>
</section>

<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
<?php include 'components/message.php'; ?>

<script>
var swiper = new Swiper(".images-container", {
   effect: "coverflow",
   grabCursor: true,
   centeredSlides: true,
   slidesPerView: "auto",
   loop: true,
   coverflowEffect: {
      rotate: 0,
      stretch: 0,
      depth: 200,
      modifier: 3,
      slideShadows: true,
   },
   pagination: { el: ".swiper-pagination" },
});
</script>
</body>
</html>