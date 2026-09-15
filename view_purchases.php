<?php  
include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = '';
    header('location:login.php');
    exit();
}

// Fetch from buyers table
$select_user = $conn->prepare("SELECT * FROM `buyers` WHERE id = ?");
$select_user->execute([$user_id]);
$buyer_info = $select_user->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Purchases</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .purchase-section { padding: 2rem 0; }
        .purchase-card { background: white; border: 1px solid #ddd; border-radius: 10px; overflow: hidden; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .purchase-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.15); }
        .purchase-card-header { display: grid; grid-template-columns: 250px 1fr; gap: 2rem; padding: 2rem; }
        .purchase-image { width: 100%; height: 200px; border-radius: 8px; overflow: hidden; }
        .purchase-image img { width: 100%; height: 100%; object-fit: cover; }
        .purchase-info { display: flex; flex-direction: column; justify-content: space-between; }
        .purchase-info h3 { margin: 0 0 10px 0; color: #333; font-size: 1.5rem; }
        .purchase-info p { margin: 8px 0; color: #666; font-size: 14px; }
        .purchase-price { font-size: 1.8rem; color: #667eea; font-weight: bold; margin: 10px 0; }
        .purchase-badge { display: inline-block; background: #27ae60; color: white; padding: 5px 15px; border-radius: 50px; font-size: 12px; font-weight: bold; margin-top: 10px; }
        .purchase-details { background: #f8f9fa; padding: 2rem; border-top: 1px solid #ddd; }
        .details-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; }
        .detail-item { padding: 1rem; background: white; border-radius: 8px; border-left: 4px solid #667eea; }
        .detail-item label { font-weight: bold; color: #667eea; font-size: 12px; text-transform: uppercase; }
        .detail-item value { display: block; margin-top: 5px; color: #333; font-size: 14px; }
        .purchase-actions { display: flex; gap: 10px; margin-top: 20px; }
        .btn-small { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-size: 14px; transition: all 0.3s ease; }
        .btn-view { background: #667eea; color: white; }
        .btn-view:hover { background: #5568d3; }
        .btn-contact { background: #17a2b8; color: white; }
        .btn-contact:hover { background: #138496; }
        .summary-section { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; border-radius: 10px; margin-bottom: 2rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; }
        .summary-item h4 { margin: 0 0 10px 0; opacity: 0.9; font-size: 14px; }
        .summary-item .value { font-size: 2rem; font-weight: bold; }
        .empty-state { text-align: center; padding: 4rem 2rem; background: #f8f9fa; border-radius: 10px; margin: 2rem 0; }
        .empty-state i { font-size: 4rem; color: #ddd; margin-bottom: 1rem; }
        .empty-state p { color: #999; margin-bottom: 2rem; }
        @media (max-width: 768px) {
            .purchase-card-header { grid-template-columns: 1fr; }
            .details-grid { grid-template-columns: 1fr; }
            .summary-section { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="contact" style="padding: 2rem;">
<h1 class="heading">My Purchases</h1>

<?php
try {
    // Join with sellers instead of users
    $select_purchases = $conn->prepare("
        SELECT 
            p.id AS purchase_id,
            p.created_at,
            prop.*,
            s.name AS seller_name,
            s.id AS seller_user_id,
            s.number AS seller_number,
            s.email AS seller_email
        FROM purchases p 
        JOIN property prop ON p.property_id = prop.id 
        JOIN sellers s ON prop.user_id = s.id
        WHERE p.buyer_id = ? 
          AND p.status = 'completed'
        ORDER BY p.created_at DESC
    ");
    $select_purchases->execute([$user_id]);

    if($select_purchases->rowCount() > 0){
        $total_spent = 0;
        $purchases = [];
        while($fetch = $select_purchases->fetch(PDO::FETCH_ASSOC)){
            $purchases[] = $fetch;
            $total_spent += (int)$fetch['price'];
        }

        echo '<div class="summary-section">
            <div class="summary-item">
                <h4>Total Purchases</h4>
                <div class="value">' . count($purchases) . '</div>
            </div>
            <div class="summary-item">
                <h4>Total Investment</h4>
                <div class="value">Rs. ' . number_format($total_spent) . '</div>
            </div>
            <div class="summary-item">
                <h4>Average Price</h4>
                <div class="value">Rs. ' . number_format($total_spent / count($purchases)) . '</div>
            </div>
        </div>';

        foreach($purchases as $property){
            $property_type = $property['type'];
            $seller_name = $property['seller_name'];
            $seller_id = $property['seller_user_id'];
            $purchase_date = $property['created_at'];
?>
<div class="purchase-card">
    <div class="purchase-card-header">
        <div class="purchase-image">
            <img src="uploaded_files/<?= htmlspecialchars($property['image_01']); ?>" alt="<?= htmlspecialchars($property['property_name']); ?>">
        </div>
        <div class="purchase-info">
            <div>
                <h3><?= htmlspecialchars($property['property_name']); ?></h3>
                <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($property['address']); ?></p>
                <div class="purchase-price"><i class="fas fa-rupee-sign"></i> <?= number_format($property['price']); ?></div>
                <span class="purchase-badge">PURCHASED</span>
            </div>
            <div>
                <p><strong>Seller:</strong> <?= htmlspecialchars($seller_name); ?></p>
                <p><strong>Purchased:</strong> <?= date('d M Y, h:i A', strtotime($purchase_date)); ?></p>
                <p><strong>Property Type:</strong> <span style="text-transform: capitalize;"><?= htmlspecialchars($property_type); ?></span></p>
            </div>
        </div>
    </div>
    <div class="purchase-details">
        <h4 style="margin-top: 0;">Property Details</h4>
        <div class="details-grid">
            <?php if($property_type == 'home'){ ?>
                <?php if(!empty($property['bhk'])){ ?>
                    <div class="detail-item"><label><i class="fas fa-home"></i> BHK</label><value><?= $property['bhk']; ?> BHK</value></div>
                <?php } ?>
                <?php if(!empty($property['bedroom'])){ ?>
                    <div class="detail-item"><label><i class="fas fa-bed"></i> Bedrooms</label><value><?= $property['bedroom']; ?></value></div>
                <?php } ?>
                <?php if(!empty($property['bathroom'])){ ?>
                    <div class="detail-item"><label><i class="fas fa-bath"></i> Bathrooms</label><value><?= $property['bathroom']; ?></value></div>
                <?php } ?>
                <?php if(!empty($property['carpet'])){ ?>
                    <div class="detail-item"><label><i class="fas fa-ruler-combined"></i> Carpet Area</label><value><?= $property['carpet']; ?> sqft</value></div>
                <?php } ?>
                <?php if(!empty($property['furnished'])){ ?>
                    <div class="detail-item"><label><i class="fas fa-couch"></i> Furnished</label><value><?= htmlspecialchars($property['furnished']); ?></value></div>
                <?php } ?>
                <?php if(!empty($property['age'])){ ?>
                    <div class="detail-item"><label><i class="fas fa-clock"></i> Age</label><value><?= $property['age']; ?> Years</value></div>
                <?php } ?>
            <?php } elseif($property_type == 'land'){ ?>
                <?php if(!empty($property['total_area'])){ ?>
                    <div class="detail-item"><label><i class="fas fa-vector-square"></i> Total Area</label><value><?= $property['total_area']; ?> sqft</value></div>
                <?php } ?>
                <?php if(!empty($property['ana'])){ ?>
                    <div class="detail-item"><label><i class="fas fa-chart-area"></i> Ana</label><value><?= $property['ana']; ?> Ana</value></div>
                <?php } ?>
                <?php if(!empty($property['facing'])){ ?>
                    <div class="detail-item"><label><i class="fas fa-compass"></i> Facing</label><value><?= htmlspecialchars($property['facing']); ?></value></div>
                <?php } ?>
                <?php if(!empty($property['road_access'])){ ?>
                    <div class="detail-item"><label><i class="fas fa-road"></i> Road Access</label><value><?= htmlspecialchars($property['road_access']); ?></value></div>
                <?php } ?>
                <?php if(!empty($property['ownership'])){ ?>
                    <div class="detail-item"><label><i class="fas fa-user-tie"></i> Ownership</label><value><?= htmlspecialchars($property['ownership']); ?></value></div>
                <?php } ?>
            <?php } ?>
            <div class="detail-item"><label><i class="fas fa-trowel"></i> Status</label><value><?= htmlspecialchars($property['status']); ?></value></div>
        </div>
        <div class="purchase-actions">
            <a href="view_property.php?get_id=<?= $property['id']; ?>" class="btn-small btn-view">
                <i class="fas fa-eye"></i> View Full Details
            </a>
            <a href="contact_seller.php?seller_id=<?= $seller_id; ?>&property_id=<?= $property['id']; ?>" class="btn-small btn-contact">
                <i class="fas fa-phone"></i> Contact Seller
            </a>
        </div>
    </div>
</div>
<?php
        }
    } else {
?>
<div class="empty-state">
    <i class="fas fa-shopping-bag"></i>
    <p>You haven't purchased any properties yet!</p>
    <a href="listings.php" class="btn">Browse Properties</a>
</div>
<?php
    }
} catch(PDOException $e) {
    echo '<div class="empty-state"><p style="color: red;">Error loading purchases. Please try again later.</p></div>';
    error_log("Purchase query failed: " . $e->getMessage());
}
?>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
<?php include 'components/message.php'; ?>
</body>
</html>