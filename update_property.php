<?php  
include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
   header('location:login.php');
}

if(isset($_GET['get_id'])){
   $get_id = $_GET['get_id'];
}else{
   $get_id = '';
   header('location:home.php');
}

// ==================== UPDATE PROPERTY ====================
if(isset($_POST['update'])){
   $update_id = $_POST['property_id'];
   $update_id = filter_var($update_id, FILTER_SANITIZE_STRING);

   $property_name = filter_var($_POST['property_name'], FILTER_SANITIZE_STRING);
   $price         = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
   $deposite      = filter_var($_POST['deposite'], FILTER_SANITIZE_STRING);
   $address       = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
   $offer         = filter_var($_POST['offer'], FILTER_SANITIZE_STRING);
   $type          = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
   $status        = filter_var($_POST['status'], FILTER_SANITIZE_STRING);
   $loan          = filter_var($_POST['loan'], FILTER_SANITIZE_STRING);
   $description   = filter_var($_POST['description'], FILTER_SANITIZE_STRING);

   // Home fields (nullable)
   $furnished     = $_POST['furnished'] ?? null;
   $bhk           = $_POST['bhk'] ?? null;
   $bedroom       = $_POST['bedroom'] ?? null;
   $bathroom      = $_POST['bathroom'] ?? null;
   $balcony       = $_POST['balcony'] ?? null;
   $carpet        = $_POST['carpet'] ?? null;
   $age           = $_POST['age'] ?? null;
   $total_floors  = $_POST['total_floors'] ?? null;
   $room_floor    = $_POST['room_floor'] ?? null;

   // Land fields (nullable)
   $total_area    = $_POST['total_area'] ?? null;
   $road_access   = $_POST['road_access'] ?? null;
   $facing        = $_POST['facing'] ?? null;
   $plot_shape    = $_POST['plot_shape'] ?? null;
   $ana           = $_POST['ana'] ?? null;
   $ownership     = $_POST['ownership'] ?? 'Individual';
   $registration  = $_POST['registration'] ?? 'Ready for immediate transfer';

   // Amenities
   $lift = isset($_POST['lift']) ? 'yes' : 'no';
   $security_guard = isset($_POST['security_guard']) ? 'yes' : 'no';
   $play_ground = isset($_POST['play_ground']) ? 'yes' : 'no';
   $garden = isset($_POST['garden']) ? 'yes' : 'no';
   $water_supply = isset($_POST['water_supply']) ? 'yes' : 'no';
   $power_backup = isset($_POST['power_backup']) ? 'yes' : 'no';
   $parking_area = isset($_POST['parking_area']) ? 'yes' : 'no';
   $gym = isset($_POST['gym']) ? 'yes' : 'no';
   $shopping_mall = isset($_POST['shopping_mall']) ? 'yes' : 'no';
   $hospital = isset($_POST['hospital']) ? 'yes' : 'no';
   $school = isset($_POST['school']) ? 'yes' : 'no';
   $market_area = isset($_POST['market_area']) ? 'yes' : 'no';

   // Update main property details
   $update_listing = $conn->prepare("UPDATE `property` SET 
      property_name=?, address=?, price=?, type=?, offer=?, status=?, 
      furnished=?, bhk=?, deposite=?, bedroom=?, bathroom=?, balcony=?, carpet=?, age=?, total_floors=?, room_floor=?,
      total_area=?, road_access=?, facing=?, plot_shape=?, ana=?, ownership=?, registration=?,
      loan=?, lift=?, security_guard=?, play_ground=?, garden=?, water_supply=?, power_backup=?, 
      parking_area=?, gym=?, shopping_mall=?, hospital=?, school=?, market_area=?, description=?
      WHERE id = ?");

   $update_listing->execute([
      $property_name, $address, $price, $type, $offer, $status,
      $furnished, $bhk, $deposite, $bedroom, $bathroom, $balcony, $carpet, $age, $total_floors, $room_floor,
      $total_area, $road_access, $facing, $plot_shape, $ana, $ownership, $registration,
      $loan, $lift, $security_guard, $play_ground, $garden, $water_supply, $power_backup,
      $parking_area, $gym, $shopping_mall, $hospital, $school, $market_area, $description, $update_id
   ]);

   $success_msg[] = 'Property updated successfully!';
}

// Image update & delete logic (unchanged, just cleaned up)
$image_fields = ['01', '02', '03', '04', '05'];
foreach($image_fields as $num){
   if(isset($_POST["delete_image_$num"])){
      $old = $_POST["old_image_$num"] ?? '';
      $update = $conn->prepare("UPDATE `property` SET image_$num = ? WHERE id = ?");
      $update->execute(['', $get_id]);
      if($old != '') unlink('uploaded_files/'.$old);
      $success_msg[] = "Image $num deleted!";
   }

   if(!empty($_FILES["image_$num"]['name'])){
      $file = $_FILES["image_$num"];
      if($file['size'] > 2000000){
         $warning_msg[] = "Image $num too large!";
      }else{
         $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
         $new_name = create_unique_id().'.'.$ext;
         move_uploaded_file($file['tmp_name'], 'uploaded_files/'.$new_name);
         $conn->prepare("UPDATE `property` SET image_$num = ? WHERE id = ?")->execute([$new_name, $get_id]);
         if($_POST["old_image_$num"] ?? '' != ''){
            unlink('uploaded_files/'.$_POST["old_image_$num"]);
         }
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Property</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      .hidden { display: none !important; }
   </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="property-form">

<?php
$select_property = $conn->prepare("SELECT * FROM `property` WHERE id = ?");
$select_property->execute([$get_id]);
if($select_property->rowCount() > 0){
   $fetch_property = $select_property->fetch(PDO::FETCH_ASSOC);
   $current_type = $fetch_property['type']; // home or land
?>

<form action="" method="POST" enctype="multipart/form-data">
   <input type="hidden" name="property_id" value="<?= $fetch_property['id']; ?>">
   <?php foreach(['01','02','03','04','05'] as $n): ?>
      <input type="hidden" name="old_image_<?= $n; ?>" value="<?= $fetch_property["image_$n"]; ?>">
   <?php endforeach; ?>

   <h3>Update Property</h3>

   <div class="flex">
      <div class="box">
         <p>Property Name <span>*</span></p>
         <input type="text" name="property_name" required class="input" value="<?= $fetch_property['property_name']; ?>">
      </div>
      <div class="box">
         <p>Price <span>*</span></p>
         <input type="number" name="price" required class="input" value="<?= $fetch_property['price']; ?>">
      </div>
      <div class="box">
         <p>Deposit</p>
         <input type="number" name="deposite" class="input" value="<?= $fetch_property['deposite']; ?>">
      </div>
      <div class="box">
         <p>Address <span>*</span></p>
         <input type="text" name="address" required class="input" value="<?= $fetch_property['address']; ?>">
      </div>
      <div class="box">
         <p>Offer Type <span>*</span></p>
         <select name="offer" required class="input">
            <option value="sale" <?= $fetch_property['offer']=='sale'?'selected':''; ?>>Sale</option>
            <option value="resale" <?= $fetch_property['offer']=='resale'?'selected':''; ?>>Resale</option>
            
         </select>
      </div>
      <div class="box">
         <p>Property Type <span>*</span></p>
         <select name="type" id="property_type" required class="input">
            <option value="home" <?= $current_type=='home'?'selected':''; ?>>Home</option>
            <option value="land" <?= $current_type=='land'?'selected':''; ?>>Land</option>
         </select>
      </div>
      <div class="box">
         <p>Status <span>*</span></p>
         <select name="status" required class="input">
            <option value="ready to move" <?= $fetch_property['status']=='ready to move'?'selected':''; ?>>Ready to Move</option>
            <option value="under construction" <?= $fetch_property['status']=='under construction'?'selected':''; ?>>Under Construction</option>
         </select>
      </div>
   </div>

   <!-- HOME FIELDS -->
   <div id="home_fields" class="flex">
      <div class="box"><p>Furnished</p><select name="furnished" class="input"><option value="unfurnished">Unfurnished</option><option value="semi-furnished" <?= $fetch_property['furnished']=='semi-furnished'?'selected':''; ?>>Semi-furnished</option><option value="furnished" <?= $fetch_property['furnished']=='furnished'?'selected':''; ?>>Furnished</option></select></div>
      <div class="box"><p>BHK</p><select name="bhk" class="input"><option value="1">1</option><option value="2" <?= $fetch_property['bhk']=='2'?'selected':''; ?>>2</option><option value="3" <?= $fetch_property['bhk']=='3'?'selected':''; ?>>3</option><option value="4" <?= $fetch_property['bhk']=='4'?'selected':''; ?>>4</option><option value="5">5+</option></select></div>
      <div class="box"><p>Bedrooms</p><input type="number" name="bedroom" class="input" value="<?= $fetch_property['bedroom']; ?>"></div>
      <div class="box"><p>Bathrooms</p><input type="number" name="bathroom" class="input" value="<?= $fetch_property['bathroom']; ?>"></div>
      <div class="box"><p>Balconies</p><input type="number" name="balcony" class="input" value="<?= $fetch_property['balcony']; ?>"></div>
      <div class="box"><p>Carpet Area (sqft)</p><input type="number" name="carpet" class="input" value="<?= $fetch_property['carpet']; ?>"></div>
      <div class="box"><p>Age (years)</p><input type="number" name="age" class="input" value="<?= $fetch_property['age']; ?>"></div>
       
   </div>

   <!-- LAND FIELDS -->
   <div id="land_fields" class="flex">
      <div class="box"><p>Total Area (sqft) <span>*</span></p><input type="number" maxlength="20" name="total_area" class="input" value="<?= $fetch_property['total_area']; ?>"></div>
      <div class="box"><p>Road Access</p><input type="text" name="road_access" class="input" value="<?= $fetch_property['road_access']; ?>" placeholder="e.g. 20 ft pitched road"></div>
      <div class="box"><p>Facing</p><select name="facing" class="input">
         <?php $facings = ['East','West','North','South','North-East','North-West','South-East','South-West']; foreach($facings as $f): ?>
            <option value="<?= $f; ?>" <?= $fetch_property['facing']==$f?'selected':''; ?>><?= $f; ?></option>
         <?php endforeach; ?>
      </select></div>
      <div class="box"><p>Plot Shape</p><select name="plot_shape" class="input">
         <option value="Rectangular" <?= $fetch_property['plot_shape']=='Rectangular'?'selected':''; ?>>Rectangular</option>
         <option value="Square" <?= $fetch_property['plot_shape']=='Square'?'selected':''; ?>>Square</option>
         <option value="Irregular" <?= $fetch_property['plot_shape']=='Irregular'?'selected':''; ?>>Irregular</option>
      </select></div>
      <div class="box"><p>Ana</p><input type="number"maxlength="20" step="0.1" name="ana" class="input" value="<?= $fetch_property['ana']; ?>"></div>
      <div class="box"><p>Ownership</p><select name="ownership" class="input"><option>Individual</option><option <?= $fetch_property['ownership']=='Joint'?'selected':''; ?>>Joint</option></select></div>
       
   </div>

   <div class="box">
      <p>Loan Available?</p>
      <select name="loan" class="input">
         <option value="available" <?= $fetch_property['loan']=='available'?'selected':''; ?>>Available</option>
         <option value="not available" <?= $fetch_property['loan']=='not available'?'selected':''; ?>>Not Available</option>
      </select>
   </div>

   <div class="box">
      <p>Description <span>*</span></p>
      <textarea name="description" class="input" required><?= $fetch_property['description']; ?></textarea>
   </div>

   <!-- Amenities -->
   <div class="checkbox">
      <div class="box">
         <p><input type="checkbox" name="lift" value="yes" <?= $fetch_property['lift']=='yes'?'checked':''; ?>> Lift</p>
         <p><input type="checkbox" name="security_guard" value="yes" <?= $fetch_property['security_guard']=='yes'?'checked':''; ?>> Security Guard</p>
         <p><input type="checkbox" name="play_ground" value="yes" <?= $fetch_property['play_ground']=='yes'?'checked':''; ?>> Play Ground</p>
         <p><input type="checkbox" name="garden" value="yes" <?= $fetch_property['garden']=='yes'?'checked':''; ?>> Garden</p>
         <p><input type="checkbox" name="water_supply" value="yes" <?= $fetch_property['water_supply']=='yes'?'checked':''; ?>> Water Supply</p>
         <p><input type="checkbox" name="power_backup" value="yes" <?= $fetch_property['power_backup']=='yes'?'checked':''; ?>> Power Backup</p>
      </div>
      <div class="box">
         <p><input type="checkbox" name="parking_area" value="yes" <?= $fetch_property['parking_area']=='yes'?'checked':''; ?>> Parking</p>
         <p><input type="checkbox" name="gym" value="yes" <?= $fetch_property['gym']=='yes'?'checked':''; ?>> Gym</p>
         <p><input type="checkbox" name="hospital" value="yes" <?= $fetch_property['hospital']=='yes'?'checked':''; ?>> Hospital Nearby</p>
         <p><input type="checkbox" name="school" value="yes" <?= $fetch_property['school']=='yes'?'checked':''; ?>> School Nearby</p>
         <p><input type="checkbox" name="market_area" value="yes" <?= $fetch_property['market_area']=='yes'?'checked':''; ?>> Market Nearby</p>
      </div>
   </div>

   <!-- Images -->
   <div class="box">
      <img src="uploaded_files/<?= $fetch_property['image_01']; ?>" class="image"><br>
      <p>Update Main Image</p>
      <input type="file" name="image_01" accept="image/*" class="input">
   </div>
   <div class="flex">
      <?php for($i=2; $i<=5; $i++): ?>
      <div class="box">
         <?php if(!empty($fetch_property["image_0$i"])): ?>
            <img src="uploaded_files/<?= $fetch_property["image_0$i"]; ?>" class="image"><br>
            <input type="submit" name="delete_image_0<?= $i; ?>" value="Delete Image <?= $i; ?>" class="inline-btn" onclick="return confirm('Delete this image?');">
         <?php endif; ?>
         <p>Image <?= $i; ?></p>
         <input type="file" name="image_0<?= $i; ?>" accept="image/*">
      </div>
      <?php endfor; ?>
   </div>

   <input type="submit" value="Update Property" name="update" class="btn">
</form>

<?php } else {
   echo '<p class="empty">Property not found!</p>';
} ?>

</section>

<script>
// Toggle Home vs Land fields
function toggleFields() {
   const type = document.getElementById('property_type').value;
   document.getElementById('home_fields').classList.toggle('hidden', type !== 'home');
   document.getElementById('land_fields').classList.toggle('hidden', type !== 'land');
}

// Run on load and on change
document.getElementById('property_type').addEventListener('change', toggleFields);
window.onload = toggleFields;
</script>

<?php include 'components/footer.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="js/script.js"></script>
<?php include 'components/message.php'; ?>

</body>
</html>