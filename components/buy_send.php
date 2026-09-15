<?php
if(isset($_POST['buy_property'])){

   // Validate logged-in user
   if(!isset($_COOKIE['user_id']) || $_COOKIE['user_id'] == ''){
      header('location:login.php');
      exit();
   }

   $buyer_id = $_COOKIE['user_id'];
   
   // Check if user is a seller (cannot buy if in sellers table)
   $check_seller = $conn->prepare("SELECT id FROM `sellers` WHERE id = ?");
   $check_seller->execute([$buyer_id]);
   
   if($check_seller->rowCount() > 0){
      $warning_msg[] = 'Only buyers can purchase properties! Sellers cannot buy properties.';
      return;
   }
   
   $property_id = $_POST['property_id'];

   // Check if property exists
   $check_property = $conn->prepare("SELECT * FROM property WHERE id = ? LIMIT 1");
   $check_property->execute([$property_id]);

   if($check_property->rowCount() == 0){
      $warning_msg[] = 'Property not found!';
      return;
   }

   $property = $check_property->fetch(PDO::FETCH_ASSOC);
   $seller_id = $property['user_id'];

   // Prevent buying own property
   if($seller_id == $buyer_id){
      $warning_msg[] = 'You cannot buy your own property!';
      return;
   }

   // Check duplicate purchase
   $check_purchase = $conn->prepare("SELECT * FROM purchases WHERE buyer_id = ? AND property_id = ? LIMIT 1");
   $check_purchase->execute([$buyer_id, $property_id]);

   if($check_purchase->rowCount() > 0){
      $warning_msg[] = 'You have already purchased this property!';
      return;
   }

   // Generate purchase ID
   function create_purchase_id(){
      return uniqid('pur_');
   }

   $purchase_id = create_purchase_id();

   // Insert purchase
   $insert_purchase = $conn->prepare("
      INSERT INTO purchases (id, buyer_id, seller_id, property_id, status)
      VALUES (?, ?, ?, ?, ?)
   ");

   $insert_success = $insert_purchase->execute([
      $purchase_id,
      $buyer_id,
      $seller_id,
      $property_id,
      'completed'
   ]);

   if($insert_success){
      $success_msg[] = 'Property purchased successfully!';
      header('location:view_purchases.php');
      exit();
   } else {
      $error_msg[] = 'Failed to complete purchase!';
   }
}
?>
