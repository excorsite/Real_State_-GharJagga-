<?php
include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
    $user_id = $_COOKIE['user_id'];
}else{
    $user_id = '';
}

if(isset($_POST['submit'])){
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_STRING);
    $pass = $_POST['pass'];

    // Check buyers table first
    $select_buyer = $conn->prepare("SELECT * FROM `buyers` WHERE email = ? LIMIT 1");
    $select_buyer->execute([$email]);
    $buyer = $select_buyer->fetch(PDO::FETCH_ASSOC);

    // Check sellers table
    $select_seller = $conn->prepare("SELECT * FROM `sellers` WHERE email = ? LIMIT 1");
    $select_seller->execute([$email]);
    $seller = $select_seller->fetch(PDO::FETCH_ASSOC);

    if($buyer && password_verify($pass, $buyer['password'])){
        // Login as buyer
        setcookie('user_id', $buyer['id'], time() + 60*60*24*30, '/');
        setcookie('user_type', 'buyer', time() + 60*60*24*30, '/');
        header('location:home.php');
        exit();
    } elseif($seller && password_verify($pass, $seller['password'])){
        // Login as seller
        setcookie('user_id', $seller['id'], time() + 60*60*24*30, '/');
        setcookie('user_type', 'seller', time() + 60*60*24*30, '/');
        header('location:home.php');
        exit();
    } else {
        $warning_msg[] = 'Incorrect email or password!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="form-container">
    <form action="" method="post">
        <h3>Welcome back!</h3>
        <input type="email" name="email" required maxlength="50" placeholder="Enter your email" class="box">
        <input type="password" name="pass" required maxlength="20" placeholder="Enter your password" class="box">
        <p>Don't have an account? <a href="register.php">Register new</a></p>
        <input type="submit" value="Login now" name="submit" class="btn">
    </form>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
<?php include 'components/message.php'; ?>
</body>
</html>