<?php  
include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
    $user_id = $_COOKIE['user_id'];
}else{
    $user_id = '';
    header('location:login.php');
    exit();
}

// Get user type from cookie
$user_type = isset($_COOKIE['user_type']) ? $_COOKIE['user_type'] : '';

// Fetch from correct table
if($user_type == 'buyer'){
    $select_user = $conn->prepare("SELECT * FROM `buyers` WHERE id = ? LIMIT 1");
} else {
    $select_user = $conn->prepare("SELECT * FROM `sellers` WHERE id = ? LIMIT 1");
}
$select_user->execute([$user_id]);
$fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

if(!$fetch_user){
    header('location:login.php');
    exit();
}

if(isset($_POST['submit'])){

    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);

    // Update name
    if(!empty($name)){
        if($user_type == 'buyer'){
            $update_name = $conn->prepare("UPDATE `buyers` SET name = ? WHERE id = ?");
        } else {
            $update_name = $conn->prepare("UPDATE `sellers` SET name = ? WHERE id = ?");
        }
        $update_name->execute([$name, $user_id]);
        $success_msg[] = 'Name updated!';
    }

    // Update email
    if(!empty($email)){
        if($user_type == 'buyer'){
            $verify_email = $conn->prepare("SELECT email FROM `buyers` WHERE email = ?");
        } else {
            $verify_email = $conn->prepare("SELECT email FROM `sellers` WHERE email = ?");
        }
        $verify_email->execute([$email]);
        if($verify_email->rowCount() > 0){
            $warning_msg[] = 'Email already taken!';
        } else {
            if($user_type == 'buyer'){
                $update_email = $conn->prepare("UPDATE `buyers` SET email = ? WHERE id = ?");
            } else {
                $update_email = $conn->prepare("UPDATE `sellers` SET email = ? WHERE id = ?");
            }
            $update_email->execute([$email, $user_id]);
            $success_msg[] = 'Email updated!';
        }
    }

    // Update number
    if(!empty($number)){
        if($user_type == 'buyer'){
            $verify_number = $conn->prepare("SELECT number FROM `buyers` WHERE number = ?");
        } else {
            $verify_number = $conn->prepare("SELECT number FROM `sellers` WHERE number = ?");
        }
        $verify_number->execute([$number]);
        if($verify_number->rowCount() > 0){
            $warning_msg[] = 'Number already taken!';
        } else {
            if($user_type == 'buyer'){
                $update_number = $conn->prepare("UPDATE `buyers` SET number = ? WHERE id = ?");
            } else {
                $update_number = $conn->prepare("UPDATE `sellers` SET number = ? WHERE id = ?");
            }
            $update_number->execute([$number, $user_id]);
            $success_msg[] = 'Number updated!';
        }
    }

    // Update password
    $old_pass = $_POST['old_pass'];
    $new_pass = $_POST['new_pass'];
    $c_pass = $_POST['c_pass'];

    if(!empty($old_pass)){
        if(!password_verify($old_pass, $fetch_user['password'])){
            $warning_msg[] = 'Old password not matched!';
        } elseif($new_pass != $c_pass){
            $warning_msg[] = 'Confirm password not matched!';
        } else {
            if(!empty($new_pass)){
                $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
                if($user_type == 'buyer'){
                    $update_pass = $conn->prepare("UPDATE `buyers` SET password = ? WHERE id = ?");
                } else {
                    $update_pass = $conn->prepare("UPDATE `sellers` SET password = ? WHERE id = ?");
                }
                $update_pass->execute([$hashed_password, $user_id]);
                $success_msg[] = 'Password updated successfully!';
            } else {
                $warning_msg[] = 'Please enter new password!';
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
    <title>Update</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="form-container">
    <form action="" method="post">
        <h3>Update your account!</h3>
        <input type="tel" name="name" maxlength="50" placeholder="<?= $fetch_user['name'] ?? 'Enter your name'; ?>" class="box">
        <input type="email" name="email" maxlength="50" placeholder="<?= $fetch_user['email'] ?? 'Enter your email'; ?>" class="box">
        <input type="number" name="number" min="0" max="9999999999" maxlength="10" placeholder="<?= $fetch_user['number'] ?? 'Enter your number'; ?>" class="box">
        <input type="password" name="old_pass" maxlength="20" placeholder="Enter your old password" class="box">
        <input type="password" name="new_pass" maxlength="20" placeholder="Enter your new password" class="box">
        <input type="password" name="c_pass" maxlength="20" placeholder="Confirm your new password" class="box">
        <input type="submit" value="Update now" name="submit" class="btn">
    </form>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
<?php include 'components/message.php'; ?>
</body>
</html>