<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'components/connect.php';

if (isset($_COOKIE['user_id'])) {
   $user_id = $_COOKIE['user_id'];
} else {
   $user_id = '';
}

$warning_msg = [];
$error_msg = [];
$success_msg = [];

if (isset($_POST['submit'])) {

   $id = create_unique_id();
   $name = trim($_POST['name']);
   $email = trim($_POST['email']);
   $number = trim($_POST['number']);
   $pass = $_POST['pass'];
   $c_pass = $_POST['c_pass'];
   $type = $_POST['type'] ?? '';

   if (empty($name) || empty($email) || empty($number) || empty($pass) || empty($c_pass) || empty($type)) {
      $warning_msg[] = 'Please fill all fields!';
   } elseif ($pass !== $c_pass) {
      $warning_msg[] = 'Confirm password does not match!';
   } elseif (strlen($number) != 10 || !ctype_digit($number)) {
      $warning_msg[] = 'Please enter a valid 10-digit phone number!';
   } else {

      // Check email in buyers or sellers table
      if ($type === 'buyer') {
         $check = $conn->prepare("SELECT * FROM `buyers` WHERE email = ?");
      } else {
         $check = $conn->prepare("SELECT * FROM `sellers` WHERE email = ?");
      }
      $check->execute([$email]);

      if ($check->rowCount() > 0) {
         $warning_msg[] = 'Email already taken!';
      } else {
         $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

         // Insert into buyers or sellers only — no user_id needed
         if ($type === 'buyer') {
            $insert = $conn->prepare("INSERT INTO `buyers` (id, name, number, email, password, created_at) 
                                      VALUES (?, ?, ?, ?, ?, NOW())");
         } else {
            $insert = $conn->prepare("INSERT INTO `sellers` (id, name, number, email, password, created_at) 
                                      VALUES (?, ?, ?, ?, ?, NOW())");
         }

         $insert_success = $insert->execute([$id, $name, $number, $email, $hashed_password]);

         if ($insert_success) {
            // Verify from correct table
            if ($type === 'buyer') {
               $verify = $conn->prepare("SELECT * FROM `buyers` WHERE email = ? LIMIT 1");
            } else {
               $verify = $conn->prepare("SELECT * FROM `sellers` WHERE email = ? LIMIT 1");
            }
            $verify->execute([$email]);
            $user = $verify->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($pass, $user['password'])) {
               //Store both user_id and user_type in cookies
               setcookie('user_id', $user['id'], time() + 60 * 60 * 24 * 30, '/');
               setcookie('user_type', $type, time() + 60 * 60 * 24 * 30, '/');
               $success_msg[] = 'Registered successfully! Welcome!';
            } else {
               $error_msg[] = 'Login failed after registration. Please try logging in manually.';
            }
         } else {
            $error_msg[] = 'Registration failed. Please try again.';
         }
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="form-container">
   <form action="" method="post">
      <h3>Create an account!</h3>

      <input type="text" name="name" required maxlength="50" placeholder="Enter your name" class="box" value="<?= htmlspecialchars($name ?? '') ?>">

      <input type="email" name="email" required maxlength="50" placeholder="Enter your email" class="box" value="<?= htmlspecialchars($email ?? '') ?>">

      <input type="text" name="number" required maxlength="10" placeholder="Enter your number (10 digits)" class="box" pattern="[0-9]{10}" title="10-digit phone number" value="<?= htmlspecialchars($number ?? '') ?>">

      <select name="type" required class="box">
         <option value="" disabled <?= empty($type) ? 'selected' : '' ?>>Select Account Type</option>
         <option value="buyer" <?= ($type ?? '') === 'buyer' ? 'selected' : '' ?>>Buyer</option>
         <option value="seller" <?= ($type ?? '') === 'seller' ? 'selected' : '' ?>>Seller</option>
      </select>

      <input type="password" name="pass" required maxlength="20" placeholder="Enter your password" class="box">

      <input type="password" name="c_pass" required maxlength="20" placeholder="Confirm your password" class="box">

      <p>Already have an account? <a href="login.php">Login now</a></p>

      <input type="submit" value="Register now" name="submit" class="btn">
   </form>
</section>

<?php include 'components/footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="js/script.js"></script>

<?php include 'components/message.php'; ?>

<?php
if (!empty($warning_msg)) {
   foreach ($warning_msg as $msg) {
      echo "<script>sweetAlert('Oops...', '$msg', 'warning');</script>";
   }
}
if (!empty($error_msg)) {
   foreach ($error_msg as $msg) {
      echo "<script>sweetAlert('Error!', '$msg', 'error');</script>";
   }
}
if (!empty($success_msg)) {
   foreach ($success_msg as $msg) {
      echo "<script>sweetAlert('Success!', '$msg', 'success');</script>";
   }
}
?>

</body>
</html>