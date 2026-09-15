<?php
session_start();
include '../components/connect.php';

if (!isset($_COOKIE['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Fix session messages
$success_msg = '';
$warning_msg = '';
if(isset($_SESSION['success_msg'])){
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}
if(isset($_SESSION['warning_msg'])){
    $warning_msg = $_SESSION['warning_msg'];
    unset($_SESSION['warning_msg']);
}

// Allowed filters
$allowed_filters = ['all', 'pending', 'approved'];
$filter = $_GET['filter'] ?? 'all';
$filter = in_array($filter, $allowed_filters) ? $filter : 'all';

$search = '';

// Handle search submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_box'])) {
    $search = trim($_POST['search_box']);
    header('Location: ?filter=' . $filter . '&search=' . urlencode($search));
    exit;
}

// Get search from URL
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// Build redirect URL
function get_redirect_url($filter, $search = '') {
    $url = '?filter=' . urlencode($filter);
    if (!empty($search)) {
        $url .= '&search=' . urlencode($search);
    }
    return $url;
}

// Handle Approve / Delete Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Approve property
    if (isset($_POST['property_id'], $_POST['action']) && $_POST['action'] === 'approve') {
        $id = $_POST['property_id'];
        $stmt = $conn->prepare("UPDATE property SET status = 'approved', approved = 1 WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['success_msg'] = "Property approved successfully!";
        } else {
            $_SESSION['warning_msg'] = "Failed to approve property!";
        }
        $filter = $_POST['filter'] ?? 'all';
        $search = $_POST['search'] ?? '';
        header("Location: " . get_redirect_url($filter, $search));
        exit;
    }

    // Delete property
    if (isset($_POST['delete_id'])) {
        $id = $_POST['delete_id'];
        $fetch = $conn->prepare("SELECT image_01, image_02, image_03, image_04, image_05 FROM property WHERE id = ?");
        $fetch->execute([$id]);
        $images = $fetch->fetch(PDO::FETCH_ASSOC);
        if ($images) {
            foreach ($images as $img) {
                if ($img && file_exists("../uploaded_files/$img")) {
                    @unlink("../uploaded_files/$img");
                }
            }
            $delete = $conn->prepare("DELETE FROM property WHERE id = ?");
            if ($delete->execute([$id])) {
                $_SESSION['success_msg'] = "Property deleted permanently!";
            } else {
                $_SESSION['warning_msg'] = "Failed to delete property!";
            }
        }
        $filter = $_POST['filter'] ?? 'all';
        $search = $_POST['search'] ?? '';
        header("Location: " . get_redirect_url($filter, $search));
        exit;
    }
}

// Build dynamic query
$where = [];
$params = [];

if ($filter === 'pending') {
    // Fixed: use approved = 0 for pending
    $where[] = "approved = 0";
} elseif ($filter === 'approved') {
    // Fixed: use approved = 1 for approved
    $where[] = "approved = 1";
}

if (!empty($search)) {
    $where[] = "(property_name LIKE ? OR address LIKE ? OR type LIKE ? OR offer LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_fill(0, 4, $searchTerm);
}

$sql = "SELECT * FROM property";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);

//  Fixed counts
$total_q    = $conn->query("SELECT COUNT(*) FROM property")->fetchColumn();
$pending_q  = $conn->query("SELECT COUNT(*) FROM property WHERE approved = 0")->fetchColumn();
$approved_q = $conn->query("SELECT COUNT(*) FROM property WHERE approved = 1")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Property Listings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    <style>
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 13px; color: #fff; font-weight: bold; display: inline-block; }
        .pending  { background: #e67e22; }
        .approved { background: #27ae60; }
        .btn-sm { padding: 8px 15px; margin: 3px; border: none; color: #fff; cursor: pointer; border-radius: 5px; font-size: 14px; text-decoration: none; display: inline-block; text-align: center; }
        .approve-btn { background: #27ae60; }
        .delete-btn  { background: #c0392b; }
        .view-btn    { background: #3498db; }
        .success-msg, .warning-msg { padding: 15px; margin: 15px 0; border-radius: 5px; text-align: center; color: white; font-weight: bold; transition: opacity 0.5s; }
        .success-msg { background: #27ae60; }
        .warning-msg { background: #e74c3c; }
        .filter-buttons { display: flex; gap: 10px; margin: 20px 0; flex-wrap: wrap; align-items: center; }
        .filter-buttons .btn { padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .filter-buttons .btn.active, .filter-buttons .btn:hover { background: #2980b9; }
        .search-box { padding: 10px 15px; border: 1px solid #ddd; border-radius: 5px; width: 300px; max-width: 100%; }
        .search-btn { padding: 10px 20px; background: #2c3e50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        @media (max-width: 768px) {
            .filter-buttons { flex-direction: column; align-items: stretch; }
            .search-box { width: 100%; }
        }
    </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="listings">
    <h1 class="heading">All Property Listings</h1>

    <?php if ($success_msg): ?>
        <div class="success-msg"><?= htmlspecialchars($success_msg); ?></div>
    <?php endif; ?>
    <?php if ($warning_msg): ?>
        <div class="warning-msg"><?= htmlspecialchars($warning_msg); ?></div>
    <?php endif; ?>

    <div class="filter-buttons">
        <a href="?filter=all<?= $search ? '&search=' . urlencode($search) : '' ?>"
           class="btn <?= $filter === 'all' ? 'active' : '' ?>">All (<?= $total_q ?>)</a>
        <a href="?filter=pending<?= $search ? '&search=' . urlencode($search) : '' ?>"
           class="btn <?= $filter === 'pending' ? 'active' : '' ?>">Pending (<?= $pending_q ?>)</a>
        <a href="?filter=approved<?= $search ? '&search=' . urlencode($search) : '' ?>"
           class="btn <?= $filter === 'approved' ? 'active' : '' ?>">Approved (<?= $approved_q ?>)</a>

        <form method="POST" style="margin-left: auto; display: flex; gap: 5px;">
            <input type="text" name="search_box" class="search-box"
                   placeholder="Search by name, address, type..."
                   value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i> Search
            </button>
        </form>

        <?php if ($search): ?>
            <a href="?filter=<?= $filter ?>" class="btn" style="background: #95a5a6;">Clear Search</a>
        <?php endif; ?>
    </div>

    <div class="box-container">
        <?php if ($stmt->rowCount() > 0): ?>
            <?php while ($p = $stmt->fetch(PDO::FETCH_ASSOC)):
                $img_count = 1;
                for ($i = 2; $i <= 5; $i++) {
                    if (!empty($p["image_0$i"])) $img_count++;
                }
                $main_img = !empty($p['image_01']) ? $p['image_01'] : 'placeholder.jpg';
                // Fixed: check approved column (0 or 1)
                $is_approved = $p['approved'] == 1;
            ?>
            <div class="box">
                <div class="thumb" style="position: relative;">
                    <p class="image-count"><i class="far fa-image"></i> <?= $img_count ?></p>
                    <img src="../uploaded_files/<?= htmlspecialchars($main_img) ?>" alt="Property Image">
                    <div class="status-badge <?= $is_approved ? 'approved' : 'pending' ?>">
                        <?= $is_approved ? 'Approved' : 'Pending' ?>
                    </div>
                </div>

                <h3 class="name"><?= htmlspecialchars($p['property_name']) ?></h3>
                <p class="location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($p['address']) ?></p>
                <p class="price">Rs. <?= number_format($p['price']) ?></p>
                <p class="type"><strong><?= ucfirst($p['type']) ?></strong> • <?= ucfirst($p['offer']) ?></p>

                <div class="flex-btn">
                    <a href="../view_property.php?get_id=<?= $p['id'] ?>" target="_blank" class="btn-sm view-btn">
                        <i class="fas fa-eye"></i> View
                    </a>

                    <?php if (!$is_approved): ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="property_id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn-sm approve-btn"
                                onclick="return confirm('Approve this property?')">
                            <i class="fas fa-check"></i> Approve
                        </button>
                    </form>
                    <?php endif; ?>

                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn-sm delete-btn"
                                onclick="return confirm('Delete this property permanently?')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty">No properties found matching your criteria!</p>
        <?php endif; ?>
    </div>
</section>

<script>
setTimeout(() => {
    document.querySelectorAll('.success-msg, .warning-msg').forEach(el => {
        el.style.opacity = '0';
        setTimeout(() => el.style.display = 'none', 600);
    });
}, 5000);
</script>

</body>
</html>