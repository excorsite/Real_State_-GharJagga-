<?php
include 'components/connect.php';

// Check if user is logged in and is a seller
if(!isset($_COOKIE['user_id']) || $_COOKIE['user_id'] == ''){
    header('Location: login.php');
    exit();
}

$seller_id = $_COOKIE['user_id'];

// Verify user is a seller
$check_seller = $conn->prepare("SELECT id FROM sellers WHERE id = ?");
$check_seller->execute([$seller_id]);

if($check_seller->rowCount() == 0){
    header('Location: dashboard.php');
    exit();
}

// Handle message deletion
if(isset($_POST['delete_message'])){
    $delete_index = isset($_POST['delete_index']) ? (int)$_POST['delete_index'] : -1;
    
    $messages_file = 'seller_messages_' . $seller_id . '.json';
    if(file_exists($messages_file)){
        $all_messages = json_decode(file_get_contents($messages_file), true) ?? [];
        
        if(isset($all_messages[$delete_index])){
            unset($all_messages[$delete_index]);
            $all_messages = array_values($all_messages); // re-index
            file_put_contents($messages_file, json_encode($all_messages, JSON_PRETTY_PRINT));
            $success_msg[] = 'Message deleted!';
        }
    }
}
?>
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>My Inbox</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      .inbox-container {
         max-width: 1200px;
         margin: 40px auto;
         padding: 20px;
      }
      .message-item {
         background: white;
         border: 1px solid #ddd;
         border-radius: 8px;
         padding: 20px;
         margin-bottom: 20px;
         box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      }
      .message-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 15px;
      }
      .message-from {
         font-weight: bold;
         color: #333;
      }
      .message-date {
         color: #999;
         font-size: 14px;
      }
      .message-content {
         color: #555;
         line-height: 1.6;
         margin-bottom: 15px;
      }
      .message-actions {
         display: flex;
         gap: 10px;
      }
      .message-actions button {
         padding: 8px 15px;
         border: none;
         border-radius: 5px;
         cursor: pointer;
         font-size: 14px;
      }
      .delete-btn {
         background: #e74c3c;
         color: white;
      }
      .delete-btn:hover {
         background: #c0392b;
      }
      .reply-btn {
         background: #3498db;
         color: white;
      }
      .reply-btn:hover {
         background: #2980b9;
      }
      .empty {
         text-align: center;
         padding: 40px;
         color: #999;
      }
      .contact-info {
         background: #f9f9f9;
         padding: 10px;
         border-radius: 5px;
         margin-bottom: 10px;
         font-size: 14px;
      }
      .contact-info p {
         margin: 5px 0;
      }
   </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="inbox-container">
   
   <h1 style="margin-bottom: 30px;">
      <i class="fas fa-inbox"></i> My Messages
   </h1>

   <?php
      if(isset($success_msg)){
         foreach($success_msg as $msg){
            echo '<p style="background: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px;">'.$msg.'</p>';
         }
      }
   ?>

   <?php
      $messages_file = 'seller_messages_' . $seller_id . '.json';
      $all_messages = [];
      
      if(file_exists($messages_file)){
         $all_messages = json_decode(file_get_contents($messages_file), true) ?? [];
      }

      // Reverse to show latest first
      $all_messages = array_reverse($all_messages, true);

      if(count($all_messages) > 0){
         $index = 0;
         foreach($all_messages as $fetch_msg){
   ?>
   <div class="message-item">
      <div class="message-header">
         <div class="message-from">
            <i class="fas fa-user-circle"></i> 
            From: <?= htmlspecialchars($fetch_msg['buyer_name']); ?>
         </div>
         <div class="message-date">
            <i class="fas fa-calendar"></i> 
            <?= date('d M Y, h:i A', strtotime($fetch_msg['timestamp'])); ?>
         </div>
      </div>

      <div class="contact-info">
         <p><i class="fas fa-envelope"></i> Email: <a href="mailto:<?= htmlspecialchars($fetch_msg['buyer_email']); ?>"><?= htmlspecialchars($fetch_msg['buyer_email']); ?></a></p>
         <p><i class="fas fa-phone"></i> Phone: <a href="tel:<?= htmlspecialchars($fetch_msg['buyer_number']); ?>"><?= htmlspecialchars($fetch_msg['buyer_number']); ?></a></p>
         <p><i class="fas fa-home"></i> Property ID: <?= htmlspecialchars($fetch_msg['property_id']); ?></p>
      </div>

      <div class="message-content">
         <strong>Subject:</strong> <?= htmlspecialchars($fetch_msg['subject']); ?><br><br>
         <?= nl2br(htmlspecialchars($fetch_msg['message'])); ?>
      </div>

      <div class="message-actions">
         <a href="mailto:<?= htmlspecialchars($fetch_msg['buyer_email']); ?>" class="reply-btn">
            <i class="fas fa-reply"></i> Reply via Email
         </a>
         <form action="" method="POST" style="display:inline;">
            <input type="hidden" name="delete_index" value="<?= $index; ?>">
            <button type="submit" name="delete_message" class="delete-btn" onclick="return confirm('Delete this message?');">
               <i class="fas fa-trash"></i> Delete
            </button>
         </form>
      </div>
   </div>
   <?php
            $index++;
         }
      } else {
         echo '<p class="empty"><i class="fas fa-inbox"></i><br><br>No messages yet!</p>';
      }
   ?>

</section>

<?php include 'components/footer.php'; ?>

</body>
</html>
