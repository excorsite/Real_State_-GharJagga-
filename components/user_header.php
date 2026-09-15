<?php
if (!isset($user_id)) {
    $user_id = '';
}

$user_type = '';
$user_name = '';

if ($user_id != '') {
    // ✅ Use cookie to know which table to query
    $user_type = isset($_COOKIE['user_type']) ? $_COOKIE['user_type'] : '';

    if ($user_type == 'buyer') {
        $select_user = $conn->prepare("SELECT name FROM `buyers` WHERE id = ? LIMIT 1");
    } else {
        $select_user = $conn->prepare("SELECT name FROM `sellers` WHERE id = ? LIMIT 1");
    }

    $select_user->execute([$user_id]);
    if ($select_user->rowCount() > 0) {
        $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);
        $user_name = htmlspecialchars($fetch_user['name']);
    }
}
?>

<header class="header">

   <nav class="navbar nav-1">
      <section class="flex">
         <a href="home.php" class="logo"><i class="fas fa-house-chimney-window"></i>GharJagga</a>

         <ul>
            <?php if ($user_type == 'seller'): ?>
               <!-- Only sellers see "Post property" in top bar -->
               <li><a href="post_property.php">Post property<i class="fas fa-paper-plane"></i></a></li>
            <?php endif; ?>
         </ul>
      </section>
   </nav>

   <nav class="navbar nav-2">
      <section class="flex">
         <div id="menu-btn" class="fas fa-bars"></div>

         <div class="menu">
            <ul>

               <!-- Property Manager / My Activity Dropdown -->
               <li><a href="#">
                  <?php if ($user_type == 'seller'): ?>
                     Property Manager<i class="fas fa-angle-down"></i>
                  <?php elseif ($user_type == 'buyer'): ?>
                     My Activity<i class="fas fa-angle-down"></i>
                  <?php else: ?>
                     Explore<i class="fas fa-angle-down"></i>
                  <?php endif; ?>
               </a>
                  <ul>
                     <?php if ($user_type == 'seller'): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="post_property.php">Post Property</a></li>
                        <li><a href="my_listings.php">My Listings</a></li>
                        <li><a href="seller_inbox.php"><i class="fas fa-inbox"></i> Messages</a></li>
                        <!-- <li><a href="requests.php">View Requests</a></li> -->

                     <?php elseif ($user_type == 'buyer'): ?>
                        <li><a href="buyer_dashboard.php">Dashboard</a></li>
                        <!-- <li><a href="saved.php">Saved Properties</a></li> -->
                        <li><a href="view_purchases.php">My Purchases</a></li>
                        <!-- <li><a href="sent_requests.php">Inquiries Sent</a></li> -->

                     <?php else: // Guest ?>
                        <li><a href="listings.php">All Listings</a></li>
                        <li><a href="search.php">Search Properties</a></li>
                     <?php endif; ?>
                  </ul>
               </li>

               <!-- Options Dropdown (common but slightly different) -->
               <li><a href="#">Options<i class="fas fa-angle-down"></i></a>
                  <ul>
                     <!-- <li><a href="search.php">Latest Listings</a></li> -->
                     <li><a href="listings.php">All Listings</a></li>
                     <?php if ($user_type == 'buyer'): ?>
                        <!-- <li><a href="saved.php">Saved Properties</a></li> -->
                     <?php endif; ?>
                  </ul>
               </li>

               <!-- Help Dropdown (same for all) -->
               <li><a href="#">Help<i class="fas fa-angle-down"></i></a>
                  <ul>
                     <li><a href="about.php">About Us</a></li>
                     <li><a href="contact.php">Contact Us</a></li>
                     <li><a href="contact.php#faq">FAQ</a></li>
                  </ul>
               </li>

            </ul>
         </div>

         <!-- Account Dropdown -->
         <ul>
            <li><a href="#">
               <?php if ($user_id != ''): ?>
                  Hello <?= $user_name; ?> <i class="fas fa-angle-down"></i>
               <?php else: ?>
                  Account <i class="fas fa-angle-down"></i>
               <?php endif; ?>
            </a>
               <ul>
                  <?php if ($user_id == ''): ?>
                     <li><a href="login.php">Login Now</a></li>
                     <li><a href="register.php">Register New</a></li>
                  <?php else: ?>
                     <li><a href="update.php">Update Profile</a></li>
                     <li><a href="components/user_logout.php" onclick="return confirm('Logout from this website?');">Logout</a></li>
                  <?php endif; ?>
               </ul>
            </li>
         </ul>

      </section>
   </nav>

</header>

<!-- header section ends -->