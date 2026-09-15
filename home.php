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
<title>Home</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<div class="home modern-hero">
<section class="hero-shell">
   <div class="hero-copy">
      <span class="hero-kicker"><i class="fas fa-shield-halved"></i> Verified property marketplace in Nepal</span>
      <h1>Find a place that feels like <em>yours.</em></h1>
      <p>Discover quality homes and land, compare important details, save favourites, and connect directly with property owners.</p>
      <div class="hero-actions">
         <a href="listings.php" class="btn hero-primary">Explore properties</a>
         <?php if ($user_type === 'seller'): ?>
            <a href="post_property.php" class="hero-secondary"><i class="fas fa-plus"></i> List a property</a>
         <?php else: ?>
            <a href="register.php" class="hero-secondary"><i class="fas fa-user-plus"></i> Create free account</a>
         <?php endif; ?>
      </div>
      <div class="hero-trust">
         <div><strong>Direct</strong><span>buyer–seller contact</span></div>
         <div><strong>Fresh</strong><span>property listings</span></div>
         <div><strong>Simple</strong><span>search and enquiry</span></div>
      </div>
   </div>

   <div class="hero-search-card">
      <div class="search-card-heading">
         <span>Property search</span>
         <i class="fas fa-sliders"></i>
      </div>
      <h2>Where would you like to live?</h2>
      <form action="search.php" method="post" class="modern-search-form">
         <label class="search-field search-field-wide">
            <span><i class="fas fa-location-dot"></i> Location</span>
            <select name="type" required>
               <option value="Basundhara-Kathmandu">Basundhara, Kathmandu</option>
               <option value="Lolang-Kathmandu">Lolang, Kathmandu</option>
               <option value="Banasthali-Kathmandu">Banasthali, Kathmandu</option>
               <option value="Baneshwor-Kathmandu">Baneshwor, Kathmandu</option>
               <option value="Manamaiju-Kathmandu">Manamaiju, Kathmandu</option>
               <option value="Swayambhu-Kathmandu">Swayambhu, Kathmandu</option>
            </select>
         </label>
         <div class="search-grid">
            <label class="search-field"><span><i class="fas fa-building"></i> Property</span><select name="h_type" required><option value="home">Home</option><option value="land">Land</option></select></label>
            <label class="search-field"><span><i class="fas fa-tags"></i> Offer</span><select name="h_offer" required><option value="sale">Sale</option><option value="resale">Resale</option></select></label>
            <label class="search-field"><span><i class="fas fa-coins"></i> Min budget</span><select name="h_min" required><option value="10000000">NPR 1 Cr</option><option value="15000000">NPR 1.5 Cr</option><option value="20000000">NPR 2 Cr</option></select></label>
            <label class="search-field"><span><i class="fas fa-wallet"></i> Max budget</span><select name="h_max" required><option value="30000000">NPR 3 Cr</option><option value="40000000">NPR 4 Cr</option><option value="50000000">NPR 5 Cr</option></select></label>
         </div>
         <button type="submit" name="h_search" class="btn search-submit"><i class="fas fa-magnifying-glass"></i> Search properties</button>
      </form>
      <p class="search-note"><i class="fas fa-circle-check"></i> Browse approved listings and contact sellers directly.</p>
   </div>
</section>
</div>

<section class="market-strip">
   <div class="market-strip-inner">
      <div><i class="fas fa-house-circle-check"></i><span><strong>Trusted listings</strong>Clear property details</span></div>
      <div><i class="fas fa-comments"></i><span><strong>Easy enquiries</strong>Connect with sellers</span></div>
      <div><i class="fas fa-heart"></i><span><strong>Save favourites</strong>Shortlist properties</span></div>
      <div><i class="fas fa-chart-line"></i><span><strong>Smart discovery</strong>Relevant recommendations</span></div>
   </div>
</section>

<section class="services">
<span class="eyebrow">Everything in one place</span>
<h1 class="heading">A better way to buy and sell property</h1>
<div class="box-container">
<div class="box">
<img src="images/icon-1.png" alt="">
<h3>Buy Home</h3>
<p>Your Property, Our Priority. Discover your dream home with us. We offer a wide range of properties, from cozy apartments to luxurious villas, all vetted for quality and value.</p>
</p>
</div>
<div class="box">
<img src="images/icon-3.png" alt="">
<h3>Sell Home</h3>
<p>Selling your property has never been easier. List your home, land, or commercial space on our platform 
and reach thousands of genuine buyers instantly.</p>
</div>
<div class="box">
<img src="images/icon-2.png" alt="">
<h3>Sell your land with us</h3>
<p>Turn your land into opportunity by listing it on our trusted platform. We connect you with serious buyers
looking for residential, commercial, and investment properties.</p>
</div>
<div class="box">
<img src="images/icon-6.png" alt="">
<h3>24/7 service</h3>
<p>We are always available to support your real estate journey. Whether you want to buy, sell, or rent property,
our team and platform are accessible 24/7.</p>
</div>
<div class="box">
<img src="images/icon-4.png" alt="">
<h3>Verified information</h3>
<p>Review useful property specifications, images, location details, ownership information, and seller contact options before making a decision.</p>
</div>
</div>
</section>

<section class="listings algo-section">
<span class="eyebrow">Updated daily</span>
<h1 class="heading">Latest listings</h1>
<div class="box-container">
<?php
$total_images = 0;
$select_properties = $conn->prepare("SELECT * FROM `property` WHERE approved = 1 ORDER BY date DESC LIMIT 6");
$select_properties->execute();
if($select_properties->rowCount() > 0){
    while($fetch_property = $select_properties->fetch(PDO::FETCH_ASSOC)){

        // ---- light price-estimation algorithm: flag good-value listings ----
        $price_check = estimate_property_price($conn, $fetch_property);
        $is_best_value = ($price_check && $price_check['verdict'] === 'low');

        // ✅ Query from sellers instead of users
        $select_user = $conn->prepare("SELECT * FROM `sellers` WHERE id = ?");
        $select_user->execute([$fetch_property['user_id']]);
        $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

        $image_coutn_02 = !empty($fetch_property['image_02']) ? 1 : 0;
        $image_coutn_03 = !empty($fetch_property['image_03']) ? 1 : 0;
        $image_coutn_04 = !empty($fetch_property['image_04']) ? 1 : 0;
        $image_coutn_05 = !empty($fetch_property['image_05']) ? 1 : 0;
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
<?php if($is_saved){ ?>
<button type="submit" name="save" class="save"><i class="fas fa-heart"></i><span>saved</span></button>
<?php } ?>
<?php if($is_best_value){ ?>
<span class="match-score high"><i class="fas fa-bolt"></i> Best Value</span>
<?php } ?>
<div class="thumb">
<p class="total-images"><i class="far fa-image"></i><span><?= $total_images; ?></span></p> 
<img src="uploaded_files/<?= $fetch_property['image_01']; ?>" alt="">
</div>
<div class="admin">
<?php if($fetch_user){ ?>
<h3><?= substr($fetch_user['name'], 0, 1); ?></h3>
<div>
<p><?= $fetch_user['name']; ?></p>
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
<div class="price"><i class="fas fa-indian-rupee-sign"></i><span><?= $fetch_property['price']; ?></span></div>
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
</div>
</div>
</form>
<?php
    }
}else{
    echo '<p class="empty">no properties added yet! <a href="post_property.php" style="margin-top:1.5rem;" class="btn">add new</a></p>';
}
?>
</div>
<div style="margin-top: 2rem; text-align:center;">
<a href="listings.php" class="inline-btn">view all</a>
</div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
<?php include 'components/message.php'; ?>
<script>
let range = document.querySelector("#range");
range.oninput = () =>{
    document.querySelector('#output').innerHTML = range.value;
}
</script>
</body>
</html>