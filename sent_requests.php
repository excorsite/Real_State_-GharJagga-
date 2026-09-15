<?php  

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
   header('location:login.php');
}

// Delete sent request
if(isset($_POST['delete'])){
   $delete_id = $_POST['request_id'];
   $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);

   // Verify that this request was sent by the current user
   $verify_delete = $conn->prepare("SELECT * FROM `requests` WHERE id = ? AND sender = ?");
   $verify_delete->execute([$delete_id, $user_id]);

   if($verify_delete->rowCount() > 0){
      $delete_request = $conn->prepare("DELETE FROM `requests` WHERE id = ?");
      $delete_request->execute([$delete_id]);
      $success_msg[] = 'Inquiry withdrawn successfully!';
   }else{
      $warning_msg[] = 'Inquiry not found or already deleted!';
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Sent Inquiries</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="requests">

   <h1 class="heading">Inquiries Sent</h1>

   <div class="box-container">

   <?php
      $select_requests = $conn->prepare("SELECT * FROM `requests` WHERE sender = ? ORDER BY date DESC");
      $select_requests->execute([$user_id]);
      
      if($select_requests->rowCount() > 0){
         while($fetch_request = $select_requests->fetch(PDO::FETCH_ASSOC)){

            // Get seller info
            $select_receiver = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
            $select_receiver->execute([$fetch_request['receiver']]);
            $fetch_receiver = $select_receiver->fetch(PDO::FETCH_ASSOC);

            // Get property info
            $select_property = $conn->prepare("SELECT * FROM `property` WHERE id = ?");
            $select_property->execute([$fetch_request['property_id']]);
            $fetch_property = $select_property->fetch(PDO::FETCH_ASSOC);
   ?>
   <div class="box">
      <div class="inquiry-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e0e0e0;">
         <p class="inquiry-date" style="margin: 0; font-size: 1.4rem; color: var(--light-color);"><i class="fas fa-calendar" style="color: var(--main-color); margin-right: 0.5rem;"></i> <span><?= date('d M Y', strtotime($fetch_request['date'])); ?></span></p>
         <p class="inquiry-status" style="margin: 0; font-size: 1.4rem; color: #27ae60;"><i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i> <span>Sent</span></p>
      </div>

      <h3 class="inquiry-property" style="font-size: 1.9rem; color: var(--black); margin: 1rem 0; padding: 0.5rem 0;">
         <i class="fas fa-home" style="color: var(--main-color); margin-right: 0.7rem;"></i> 
         <?= htmlspecialchars($fetch_property['property_name'] ?? 'Property Deleted'); ?>
      </h3>

      <?php if($fetch_property): ?>
         <p><i class="fas fa-map-marker-alt" style="color: var(--main-color); margin-right: 0.7rem;"></i> <span><?= htmlspecialchars($fetch_property['address']); ?></span></p>
         <p><i class="fas fa-tag" style="color: var(--main-color); margin-right: 0.7rem;"></i> <span>Rs. <?= number_format($fetch_property['price']); ?></span></p>
      <?php endif; ?>

      <div class="seller-info" style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e0e0e0;">
         <h4 style="font-size: 1.6rem; color: var(--black); margin-bottom: 0.8rem; text-transform: capitalize;">Seller Details:</h4>
         <?php if($fetch_receiver): ?>
            <p><i class="fas fa-user" style="color: var(--main-color); margin-right: 0.7rem;"></i> <span><?= htmlspecialchars($fetch_receiver['name']); ?></span></p>
            <p><i class="fas fa-phone" style="color: var(--main-color); margin-right: 0.7rem;"></i> <a href="tel:<?= htmlspecialchars($fetch_receiver['number']); ?>" style="color: var(--main-color); text-decoration: none;"><?= htmlspecialchars($fetch_receiver['number']); ?></a></p>
            <p><i class="fas fa-envelope" style="color: var(--main-color); margin-right: 0.7rem;"></i> <a href="mailto:<?= htmlspecialchars($fetch_receiver['email']); ?>" style="color: var(--main-color); text-decoration: none;"><?= htmlspecialchars($fetch_receiver['email']); ?></a></p>
         <?php else: ?>
            <p><i class="fas fa-user" style="color: var(--main-color); margin-right: 0.7rem;"></i> <span style="color: #e74c3c;">Seller Account Deleted</span></p>
         <?php endif; ?>
      </div>

      <form action="" method="POST" class="action-buttons" style="display: flex; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap;">
         <input type="hidden" name="request_id" value="<?= $fetch_request['id']; ?>">
         <button type="submit" name="delete" class="btn" style="flex: 1; background-color: #e74c3c; min-width: 150px;" onclick="return confirm('Withdraw this inquiry?');">
            <i class="fas fa-trash" style="margin-right: 0.5rem;"></i> Withdraw
         </button>
         <?php if($fetch_property): ?>
            <a href="view_property.php?get_id=<?= htmlspecialchars($fetch_property['id']); ?>" class="btn" style="flex: 1; min-width: 150px;">
               <i class="fas fa-eye" style="margin-right: 0.5rem;"></i> View Property
            </a>
         <?php endif; ?>
      </form>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty"><i class="fas fa-inbox"></i> You haven\'t sent any inquiries yet!</p>';
      }
   ?>

   </div>

</section>

<!-- sweet alert js file cdn link  -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<?php include 'components/footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<?php include 'components/message.php'; ?>

</body>
</html>
