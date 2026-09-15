<?php
include 'components/connect.php';

if(!isset($_COOKIE['user_id']) || $_COOKIE['user_id'] == ''){
    header('Location: login.php');
    exit();
}

$buyer_id = $_COOKIE['user_id'];
$seller_id = isset($_GET['seller_id']) ? $_GET['seller_id'] : '';
$property_id = isset($_GET['property_id']) ? $_GET['property_id'] : '';

$success = '';
$warning = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Simple validation
    if($subject == '' || $message == ''){
        $warning = "Please fill all fields.";
    } else {
        // Fetch buyer info
        $u = $conn->prepare("SELECT name,email,number FROM buyers WHERE id = ? LIMIT 1");
        $u->execute([$buyer_id]);
        $buyer = $u->fetch(PDO::FETCH_ASSOC);

        // Store in session for seller to view
        if(!isset($_SESSION)){
            session_start();
        }
        
        $message_data = [
            'buyer_id' => $buyer_id,
            'buyer_name' => $buyer['name'] ?? 'Buyer',
            'buyer_email' => $buyer['email'] ?? '',
            'buyer_number' => $buyer['number'] ?? '',
            'seller_id' => $seller_id,
            'property_id' => $property_id,
            'subject' => $subject,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Store message in file-based storage
        $messages_file = 'seller_messages_' . $seller_id . '.json';
        $all_messages = [];
        
        if(file_exists($messages_file)){
            $all_messages = json_decode(file_get_contents($messages_file), true) ?? [];
        }
        
        $all_messages[] = $message_data;
        
        if(file_put_contents($messages_file, json_encode($all_messages, JSON_PRETTY_PRINT))){
            $success = "Message sent to seller successfully!";
        } else {
            $warning = "Failed to send message. Try later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Contact Seller</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<div class="container" style="max-width:700px;margin:40px auto;">
   <h2>Contact Seller</h2>
   <?php if(!empty($success)) echo '<p class="success-msg">'.$success.'</p>'; ?>
   <?php if(!empty($warning)) echo '<p class="warning-msg">'.$warning.'</p>'; ?>

   <form method="POST">
      <label>Subject</label>
      <input type="text" name="subject" required>

      <label>Message</label>
      <textarea name="message" rows="6" required></textarea>

      <button type="submit" class="btn">Send Message</button>
   </form>
</div>

<?php include 'components/footer.php'; ?>
</body>
</html>
