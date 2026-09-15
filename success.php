<?php
session_start();
include 'components/connect.php';

// get message & purchase id saved earlier
$success = isset($_SESSION['purchase_success']) ? $_SESSION['purchase_success'] : null;
$purchase_id = isset($_SESSION['purchase_id']) ? $_SESSION['purchase_id'] : null;

// clear session notifications
unset($_SESSION['purchase_success']);
unset($_SESSION['purchase_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Purchase Complete</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
</head>
<body>
<?php if($success): ?>
<script>
   swal({
      title: "Success!",
      text: <?= json_encode($success); ?>,
      icon: "success",
      button: "OK"
   }).then(function(){
      // redirect to the bought properties page — change filename if yours is different
      window.location.href = "my_bought_properties.php";
   });
</script>
<?php else: ?>
<script>
   // if no session message, send to list
   window.location.href = "listings.php";
</script>
<?php endif; ?>
</body>
</html>
