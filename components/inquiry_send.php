<?php

if(isset($_POST['send'])){

   // Validate logged-in user
   if(!isset($_COOKIE['user_id']) || $_COOKIE['user_id'] == ''){
      $warning_msg[] = 'Please login first to send an inquiry!';
   } else {

      $sender_id = $_COOKIE['user_id'];
      $property_id = $_POST['property_id'] ?? '';
      $property_id = filter_var($property_id, FILTER_SANITIZE_STRING);

      // Check if property exists and get seller info
      $check_property = $conn->prepare("SELECT * FROM property WHERE id = ? LIMIT 1");
      $check_property->execute([$property_id]);

      if($check_property->rowCount() == 0){
         $warning_msg[] = 'Property not found!';
      } else {

         $property = $check_property->fetch(PDO::FETCH_ASSOC);
         $receiver_id = $property['user_id'];

         // Prevent sending inquiry to own property
         if($receiver_id == $sender_id){
            $warning_msg[] = 'You cannot send an inquiry for your own property!';
         } else {

            // Check if inquiry already exists for this property and user
            $check_inquiry = $conn->prepare("SELECT * FROM requests WHERE sender = ? AND property_id = ? AND receiver = ? LIMIT 1");
            $check_inquiry->execute([$sender_id, $property_id, $receiver_id]);

            if($check_inquiry->rowCount() > 0){
               $warning_msg[] = 'You have already sent an inquiry for this property!';
            } else {

               // Generate inquiry ID
               $inquiry_id = create_unique_id();

               // Insert inquiry
               $insert_inquiry = $conn->prepare("
                  INSERT INTO requests (id, property_id, sender, receiver, date)
                  VALUES (?, ?, ?, ?, NOW())
               ");

               $insert_success = $insert_inquiry->execute([
                  $inquiry_id,
                  $property_id,
                  $sender_id,
                  $receiver_id
               ]);

               if($insert_success){
                  $success_msg[] = 'Inquiry sent successfully! The seller will contact you soon.';
               } else {
                  $error_msg[] = 'Failed to send inquiry. Please try again!';
               }

            }

         }

      }

   }

}

?>
