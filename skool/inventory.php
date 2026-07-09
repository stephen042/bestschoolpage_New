<?php
/**
 * ============================================================================
 * INVENTORY MANAGEMENT - COMPLETE MODERN SYSTEM
 * ============================================================================
 * Description: Full inventory management with categories, suppliers,
 *              QR codes, stock movement, checkout/approval workflow
 * Version: 3.0 (PHP 8.x Compatible)
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Inventory Management";
$FileName = 'inventory.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$create_by_userid = (int)($_SESSION['userid'] ?? 0);
$create_by_usertype = (string)($_SESSION['usertype'] ?? '');
$currentUserId = (int)($_SESSION['userid'] ?? 0);

// ============================================================================
// GET FILTERS
// ============================================================================
$selectedCategory = $_GET['category'] ?? '';
$selectedLocation = $_GET['location'] ?? '';
$selectedStatus = $_GET['status'] ?? '';
$searchTerm = $_GET['search'] ?? '';

// ============================================================================
// HANDLE POST - ADD INVENTORY ITEM
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    try {
        $itemDescription = trim($_POST['item_description'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $supplierId = (int)($_POST['supplier_id'] ?? 0);
        $location = trim($_POST['location'] ?? '');
        $unit = trim($_POST['unit'] ?? '');
        $quantity = (int)($_POST['quantity'] ?? 0);
        $unitCost = (float)($_POST['unit_cost'] ?? 0);
        $minStockLevel = (int)($_POST['min_stock_level'] ?? 0);
        $maxStockLevel = (int)($_POST['max_stock_level'] ?? 0);
        $purchaseDate = $_POST['purchase_date'] ?? null;
        $brand = trim($_POST['brand'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $serialNumber = trim($_POST['serial_number'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        
        if (empty($itemDescription)) {
            throw new Exception("Item description is required");
        }
        if ($quantity < 0) {
            throw new Exception("Valid quantity is required");
        }
        if ($unitCost < 0) {
            throw new Exception("Valid unit cost is required");
        }
        
        // Generate asset number
        $lastId = (int)$db->getVal("SELECT MAX(id) FROM school_inventory") + 1;
        $assetNumber = 'AST-' . date('Y') . '-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);
        
        // Generate random ID
        $randomId = randomFix(15) . $lastId;
        
        // Insert item
        $aryData = array(
            'item_description' => $itemDescription,
            'category_id' => $categoryId,
            'supplier_id' => $supplierId,
            'location' => $location,
            'unit' => $unit,
            'quantity' => $quantity,
            'quantity_available' => $quantity,
            'quantity_picked' => 0,
            'recorded_quantity' => $quantity,
            'unit_cost' => $unitCost,
            'asset_number' => $assetNumber,
            'purchase_date' => $purchaseDate,
            'min_stock_level' => $minStockLevel,
            'max_stock_level' => $maxStockLevel,
            'brand' => $brand,
            'model' => $model,
            'serial_number' => $serialNumber,
            'condition_status' => 'new',
            'approval_status' => 'approved',
            'notes' => $notes,
            'userid' => $currentUserId,
            'usertype' => $create_by_usertype,
            'create_by_usertype' => $create_by_usertype,
            'create_by_userid' => $create_by_userid,
            'randomid' => $randomId,
            'create_at' => date("Y-m-d H:i:s"),
            'update_at' => date("Y-m-d H:i:s")
        );
        
        $itemId = $db->insertAry("school_inventory", $aryData);
        
        if (!$itemId) {
            throw new Exception("Failed to save item");
        }
        
        // Record initial stock movement
        $movementData = array(
            'item_id' => $itemId,
            'movement_type' => 'in',
            'quantity' => $quantity,
            'previous_quantity' => 0,
            'new_quantity' => $quantity,
            'reason' => 'Initial stock entry',
            'requested_by' => $currentUserId,
            'approved_by' => $currentUserId,
            'create_by_userid' => $create_by_userid,
            'randomid' => randomFix(15) . $lastId
        );
        $db->insertAry("inventory_movement", $movementData);
        
        $_SESSION['success'] = "Item added successfully! Asset #: " . $assetNumber;
        redirect($FileName);
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
}

// ============================================================================
// HANDLE POST - UPDATE ITEM
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_item'])) {
    try {
        $itemId = (int)($_POST['item_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);
        $unitCost = (float)($_POST['unit_cost'] ?? 0);
        
        // Get current item
        $currentItem = $db->getRow("SELECT * FROM school_inventory WHERE id = ?", [$itemId]);
        if (empty($currentItem)) {
            throw new Exception("Item not found");
        }
        
        $oldQuantity = (int)$currentItem['quantity'];
        $quantityDiff = $quantity - $oldQuantity;
        
        $aryData = array(
            'item_description' => trim($_POST['item_description'] ?? ''),
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'supplier_id' => (int)($_POST['supplier_id'] ?? 0),
            'location' => trim($_POST['location'] ?? ''),
            'unit' => trim($_POST['unit'] ?? ''),
            'quantity' => $quantity,
            'quantity_available' => $quantity - (int)$currentItem['quantity_picked'],
            'recorded_quantity' => $quantity,
            'unit_cost' => $unitCost,
            'min_stock_level' => (int)($_POST['min_stock_level'] ?? 0),
            'max_stock_level' => (int)($_POST['max_stock_level'] ?? 0),
            'purchase_date' => $_POST['purchase_date'] ?? null,
            'brand' => trim($_POST['brand'] ?? ''),
            'model' => trim($_POST['model'] ?? ''),
            'serial_number' => trim($_POST['serial_number'] ?? ''),
            'condition_status' => $_POST['condition_status'] ?? 'good',
            'notes' => trim($_POST['notes'] ?? ''),
            'update_at' => date("Y-m-d H:i:s")
        );
        
        $flgIn = $db->updateAry("school_inventory", $aryData, "where id = '" . $itemId . "'");
        
        if ($flgIn && $quantityDiff != 0) {
            // Record movement
            $movementData = array(
                'item_id' => $itemId,
                'movement_type' => ($quantityDiff > 0) ? 'in' : 'out',
                'quantity' => abs($quantityDiff),
                'previous_quantity' => $oldQuantity,
                'new_quantity' => $quantity,
                'reason' => 'Stock adjustment',
                'requested_by' => $currentUserId,
                'approved_by' => $currentUserId,
                'create_by_userid' => $create_by_userid,
                'randomid' => randomFix(15) . $itemId
            );
            $db->insertAry("inventory_movement", $movementData);
        }
        
        $_SESSION['success'] = "Item updated successfully!";
        redirect($FileName);
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
}

// ============================================================================
// HANDLE POST - CHECKOUT REQUEST
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_request'])) {
    try {
        $itemId = (int)($_POST['item_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);
        $purpose = trim($_POST['purpose'] ?? '');
        $requestedBy = (int)($_POST['requested_by'] ?? 0);
        $expectedReturnDate = $_POST['expected_return_date'] ?? null;
        
        if ($quantity <= 0) {
            throw new Exception("Valid quantity is required");
        }
        
        // Check if enough stock available
        $item = $db->getRow("SELECT * FROM school_inventory WHERE id = ?", [$itemId]);
        if (empty($item)) {
            throw new Exception("Item not found");
        }
        if ((int)$item['quantity_available'] < $quantity) {
            throw new Exception("Not enough stock available. Available: " . $item['quantity_available']);
        }
        
        $lastId = (int)$db->getVal("SELECT MAX(id) FROM inventory_checkout") + 1;
        $randomId = randomFix(15) . $lastId;
        
        $checkoutData = array(
            'item_id' => $itemId,
            'requested_by' => $requestedBy,
            'quantity' => $quantity,
            'purpose' => $purpose,
            'expected_return_date' => $expectedReturnDate,
            'status' => 'pending',
            'create_by_userid' => $create_by_userid,
            'randomid' => $randomId
        );
        
        $checkoutId = $db->insertAry("inventory_checkout", $checkoutData);
        
        if (!$checkoutId) {
            throw new Exception("Failed to submit request");
        }
        
        $_SESSION['success'] = "Checkout request submitted for approval!";
        redirect($FileName . '?action=checkouts');
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
}

// ============================================================================
// HANDLE POST - APPROVE CHECKOUT
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_checkout'])) {
    try {
        $checkoutId = (int)($_POST['checkout_id'] ?? 0);
        
        $checkout = $db->getRow("SELECT * FROM inventory_checkout WHERE id = ?", [$checkoutId]);
        if (empty($checkout)) {
            throw new Exception("Request not found");
        }
        
        // Check stock again
        $item = $db->getRow("SELECT * FROM school_inventory WHERE id = ?", [$checkout['item_id']]);
        if ((int)$item['quantity_available'] < (int)$checkout['quantity']) {
            throw new Exception("Insufficient stock. Available: " . $item['quantity_available']);
        }
        
        // Update checkout status
        $updateData = array(
            'status' => 'approved',
            'approved_by' => $currentUserId,
            'approved_date' => date("Y-m-d H:i:s")
        );
        $db->updateAry("inventory_checkout", $updateData, "where id = '" . $checkoutId . "'");
        
        // Update inventory quantity
        $newAvailable = (int)$item['quantity_available'] - (int)$checkout['quantity'];
        $newPicked = (int)$item['quantity_picked'] + (int)$checkout['quantity'];
        
        $inventoryData = array(
            'quantity_available' => $newAvailable,
            'quantity_picked' => $newPicked
        );
        $db->updateAry("school_inventory", $inventoryData, "where id = '" . $item['id'] . "'");
        
        // Record movement
        $movementData = array(
            'item_id' => $item['id'],
            'movement_type' => 'out',
            'quantity' => $checkout['quantity'],
            'previous_quantity' => (int)$item['quantity_available'] + (int)$checkout['quantity'],
            'new_quantity' => $newAvailable,
            'reason' => 'Checkout approved: ' . $checkout['purpose'],
            'requested_by' => $checkout['requested_by'],
            'approved_by' => $currentUserId,
            'create_by_userid' => $create_by_userid,
            'randomid' => randomFix(15)
        );
        $db->insertAry("inventory_movement", $movementData);
        
        $_SESSION['success'] = "Checkout approved and item issued!";
        redirect($FileName . '?action=checkouts');
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
}

// ============================================================================
// HANDLE POST - RETURN ITEM
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_item'])) {
    try {
        $checkoutId = (int)($_POST['checkout_id'] ?? 0);
        $conditionOnReturn = trim($_POST['condition_on_return'] ?? 'good');
        
        $checkout = $db->getRow("SELECT * FROM inventory_checkout WHERE id = ?", [$checkoutId]);
        if (empty($checkout)) {
            throw new Exception("Request not found");
        }
        
        // Update checkout status
        $updateData = array(
            'status' => 'returned',
            'returned_by' => $currentUserId,
            'returned_date' => date("Y-m-d H:i:s"),
            'condition_on_return' => $conditionOnReturn
        );
        $db->updateAry("inventory_checkout", $updateData, "where id = '" . $checkoutId . "'");
        
        // Update inventory quantity
        $item = $db->getRow("SELECT * FROM school_inventory WHERE id = ?", [$checkout['item_id']]);
        $newAvailable = (int)$item['quantity_available'] + (int)$checkout['quantity'];
        $newPicked = (int)$item['quantity_picked'] - (int)$checkout['quantity'];
        
        $inventoryData = array(
            'quantity_available' => $newAvailable,
            'quantity_picked' => $newPicked
        );
        $db->updateAry("school_inventory", $inventoryData, "where id = '" . $item['id'] . "'");
        
        // Record movement
        $movementData = array(
            'item_id' => $item['id'],
            'movement_type' => 'returned',
            'quantity' => $checkout['quantity'],
            'previous_quantity' => (int)$item['quantity_available'] - (int)$checkout['quantity'],
            'new_quantity' => $newAvailable,
            'reason' => 'Item returned',
            'requested_by' => $checkout['requested_by'],
            'approved_by' => $currentUserId,
            'create_by_userid' => $create_by_userid,
            'randomid' => randomFix(15)
        );
        $db->insertAry("inventory_movement", $movementData);
        
        $_SESSION['success'] = "Item returned successfully!";
        redirect($FileName . '?action=checkouts');
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
}

// ============================================================================
// HANDLE POST - GENERATE QR CODE
// ============================================================================
if (isset($_GET['action']) && $_GET['action'] == 'qr' && isset($_GET['id'])) {
    $itemId = (int)$_GET['id'];
    $item = $db->getRow("SELECT * FROM school_inventory WHERE id = ?", [$itemId]);
    if (!empty($item)) {
        $qrData = json_encode([
            'id' => $item['id'],
            'asset_number' => $item['asset_number'],
            'description' => $item['item_description'],
            'location' => $item['location']
        ]);
        // Generate QR code using API
        $qrImage = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrData);
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <?php include('inc.meta.php'); ?>
            <title>QR Code - <?= htmlspecialchars($item['asset_number']) ?></title>
            <style>
                body { background: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: 'Segoe UI', Arial, sans-serif; }
                .qr-container { text-align: center; padding: 30px; border: 2px dashed #ddd; border-radius: 16px; background: #f8f9fa; }
                .qr-container h3 { color: #1B3058; margin-bottom: 10px; }
                .qr-container img { max-width: 250px; margin: 15px 0; }
                .qr-container .details { font-size: 12px; color: #666; }
                .btn { display: inline-block; padding: 10px 24px; background: #1B3058; color: #fff; text-decoration: none; border-radius: 8px; margin-top: 10px; }
                .btn:hover { background: #f21151; }
            </style>
        </head>
        <body>
            <div class="qr-container">
                <h3>📱 Scan QR Code</h3>
                <img src="<?= $qrImage ?>" alt="QR Code">
                <div class="details">
                    <strong>Asset #:</strong> <?= htmlspecialchars($item['asset_number']) ?><br>
                    <strong>Item:</strong> <?= htmlspecialchars($item['item_description']) ?><br>
                    <strong>Location:</strong> <?= htmlspecialchars($item['location']) ?>
                </div>
                <br>
                <a href="javascript:window.print()" class="btn"><i class="fa fa-print"></i> Print QR Code</a>
                <a href="<?= $FileName ?>" class="btn" style="background:#6c757d;">Back</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// ============================================================================
// GET DATA FOR DROPDOWNS
// ============================================================================

// Get categories
$categories = $db->getRows(
    "SELECT * FROM inventory_categories 
     WHERE create_by_userid = ? AND is_active = 1
     ORDER BY name ASC",
    [$create_by_userid]
);

// Get suppliers
$suppliers = $db->getRows(
    "SELECT * FROM inventory_suppliers 
     WHERE create_by_userid = ? 
     ORDER BY name ASC",
    [$create_by_userid]
);

// Get staff for checkout
$staffList = $db->getRows(
    "SELECT * FROM staff_manage 
     WHERE create_by_userid = ? 
     ORDER BY first_name ASC",
    [$create_by_userid]
);

// Get locations
$locations = $db->getRows(
    "SELECT DISTINCT location FROM school_inventory 
     WHERE create_by_userid = ? AND location != ''
     ORDER BY location ASC",
    [$create_by_userid]
);

// ============================================================================
// BUILD INVENTORY QUERY
// ============================================================================
$inventoryQuery = "SELECT i.*, 
                    c.name as category_name,
                    s.name as supplier_name
                   FROM school_inventory i
                   LEFT JOIN inventory_categories c ON i.category_id = c.id
                   LEFT JOIN inventory_suppliers s ON i.supplier_id = s.id
                   WHERE i.create_by_userid = ?";
$params = [$create_by_userid];

if (!empty($selectedCategory)) {
    $inventoryQuery .= " AND i.category_id = ?";
    $params[] = (int)$selectedCategory;
}
if (!empty($selectedLocation)) {
    $inventoryQuery .= " AND i.location = ?";
    $params[] = $selectedLocation;
}
if (!empty($searchTerm)) {
    $inventoryQuery .= " AND (i.item_description LIKE ? OR i.asset_number LIKE ? OR i.brand LIKE ? OR i.serial_number LIKE ?)";
    $searchWildcard = '%' . $searchTerm . '%';
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
}

$inventoryQuery .= " ORDER BY i.id DESC";
$inventoryItems = db_get_rows($inventoryQuery, $params);

// ============================================================================
// GET INVENTORY SUMMARY
// ============================================================================
$summaryQuery = "SELECT 
                    COUNT(*) as total_items,
                    SUM(quantity) as total_quantity,
                    SUM(quantity * unit_cost) as total_value,
                    COUNT(DISTINCT category_id) as total_categories,
                    SUM(CASE WHEN quantity_available <= min_stock_level AND min_stock_level > 0 THEN 1 ELSE 0 END) as low_stock_count,
                    SUM(quantity_picked) as total_picked
                FROM school_inventory
                WHERE create_by_userid = ?";
$summary = db_get_row($summaryQuery, [$create_by_userid]);

// ============================================================================
// GET CHECKOUT REQUESTS
// ============================================================================
$checkoutRequests = db_get_rows(
    "SELECT co.*, 
            i.item_description, 
            i.asset_number,
            s.first_name as requester_first,
            s.last_name as requester_last,
            s.staff_id as requester_staff_id,
            app.first_name as approver_first,
            app.last_name as approver_last
     FROM inventory_checkout co
     LEFT JOIN school_inventory i ON co.item_id = i.id
     LEFT JOIN staff_manage s ON co.requested_by = s.id
     LEFT JOIN staff_manage app ON co.approved_by = app.id
     WHERE co.create_by_userid = ?
     ORDER BY co.created_at DESC",
    [$create_by_userid]
);

// ============================================================================
// GET STOCK MOVEMENTS
// ============================================================================
$movements = db_get_rows(
    "SELECT m.*, 
            i.item_description,
            i.asset_number,
            s.first_name as staff_first,
            s.last_name as staff_last
     FROM inventory_movement m
     LEFT JOIN school_inventory i ON m.item_id = i.id
     LEFT JOIN staff_manage s ON m.requested_by = s.id
     WHERE m.create_by_userid = ?
     ORDER BY m.movement_date DESC
     LIMIT 50",
    [$create_by_userid]
);

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">⏳ Pending</span>',
        'approved' => '<span class="badge badge-info">✅ Approved</span>',
        'rejected' => '<span class="badge badge-danger">❌ Rejected</span>',
        'issued' => '<span class="badge badge-success">📦 Issued</span>',
        'returned' => '<span class="badge badge-secondary">↩️ Returned</span>',
        'overdue' => '<span class="badge badge-danger">⚠️ Overdue</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}

function getConditionBadge($condition) {
    $badges = [
        'new' => '<span class="badge badge-success">🆕 New</span>',
        'good' => '<span class="badge badge-info">✅ Good</span>',
        'used' => '<span class="badge badge-warning">📦 Used</span>',
        'damaged' => '<span class="badge badge-danger">⚠️ Damaged</span>',
        'repair' => '<span class="badge badge-warning">🔧 Repair</span>'
    ];
    return $badges[$condition] ?? '<span class="badge badge-secondary">Unknown</span>';
}

function getMovementTypeBadge($type) {
    $badges = [
        'in' => '<span class="badge badge-success">📥 In</span>',
        'out' => '<span class="badge badge-danger">📤 Out</span>',
        'returned' => '<span class="badge badge-info">↩️ Returned</span>',
        'damaged' => '<span class="badge badge-warning">💔 Damaged</span>',
        'disposed' => '<span class="badge badge-secondary">🗑️ Disposed</span>'
    ];
    return $badges[$type] ?? '<span class="badge badge-secondary">Unknown</span>';
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <title><?= htmlspecialchars($PageTitle) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        
        .page-header { margin-bottom: 25px; }
        .page-header h2 { color: #1B3058; font-size: 28px; margin: 0; }
        .page-header p { color: #666; margin-top: 5px; }
        
        .card { background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 25px; overflow: hidden; }
        .card-header { padding: 16px 24px; background: linear-gradient(135deg, #1B3058, #2a4780); color: #fff; font-weight: 600; font-size: 16px; }
        .card-body { padding: 24px; }
        
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 18px; margin-bottom: 25px; }
        .summary-card { background: #fff; border-radius: 14px; padding: 18px 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); border-left: 4px solid #1B3058; transition: transform 0.2s; }
        .summary-card:hover { transform: translateY(-3px); }
        .summary-card .icon { font-size: 22px; margin-bottom: 4px; display: block; }
        .summary-card .label { font-size: 11px; color: #888; text-transform: uppercase; font-weight: 600; }
        .summary-card .value { font-size: 22px; font-weight: 700; color: #1B3058; margin-top: 2px; }
        .summary-card.success { border-color: #28a745; }
        .summary-card.danger { border-color: #dc3545; }
        .summary-card.warning { border-color: #ffc107; }
        .summary-card.info { border-color: #17a2b8; }
        
        .filter-row { display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end; margin-bottom: 20px; }
        .filter-group { flex: 1; min-width: 150px; }
        .filter-group label { display: block; font-size: 12px; font-weight: 700; color: #888; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.5px; }
        .filter-select, .filter-input { width: 100%; padding: 10px 14px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 14px; background: #fff; transition: all 0.2s; }
        .filter-select:focus, .filter-input:focus { border-color: #1B3058; outline: none; }
        
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; }
        .btn-primary { background: #1B3058; color: #fff; }
        .btn-primary:hover { background: #f21151; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: #fff; }
        .btn-success:hover { background: #218838; transform: translateY(-2px); }
        .btn-danger { background: #dc3545; color: #fff; }
        .btn-danger:hover { background: #c82333; transform: translateY(-2px); }
        .btn-warning { background: #ffc107; color: #333; }
        .btn-warning:hover { background: #e0a800; transform: translateY(-2px); }
        .btn-info { background: #17a2b8; color: #fff; }
        .btn-info:hover { background: #138496; transform: translateY(-2px); }
        .btn-sm { padding: 5px 12px; font-size: 12px; }
        
        .table-wrapper { overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .table th { background: #f8f9fa; color: #1B3058; padding: 10px 14px; text-align: left; font-weight: 600; border-bottom: 2px solid #e0e0e0; }
        .table td { padding: 8px 14px; border-bottom: 1px solid #eee; vertical-align: middle; }
        .table tr:hover { background: #f8f9ff; }
        
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-secondary { background: #e2e3e5; color: #383d41; }
        
        .nav-tabs { display: flex; list-style: none; margin: 0; padding: 0; background: #f8f9fa; border-bottom: 2px solid #e0e0e0; flex-wrap: wrap; }
        .nav-tabs li a { display: block; padding: 12px 24px; color: #555; text-decoration: none; font-weight: 600; border-bottom: 3px solid transparent; transition: all 0.3s; }
        .nav-tabs li a:hover { color: #1B3058; background: #f0f0f0; }
        .nav-tabs li.active a { color: #1B3058; border-bottom-color: #1B3058; background: #fff; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 4px; }
        .form-control { width: 100%; padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; transition: all 0.2s; }
        .form-control:focus { border-color: #1B3058; outline: none; }
        .form-row { display: flex; flex-wrap: wrap; gap: 15px; }
        .form-group-inline { flex: 1; min-width: 150px; }
        
        .text-center { text-align: center; }
        .text-muted { color: #999; }
        .mt-20 { margin-top: 20px; }
        
        .qr-scanner-btn { position: relative; overflow: hidden; }
        .qr-scanner-btn input[type="file"] { position: absolute; left: 0; top: 0; opacity: 0; width: 100%; height: 100%; cursor: pointer; }
        
        .action-buttons { display: flex; gap: 5px; flex-wrap: wrap; }
        
        @media (max-width: 768px) {
            .filter-row { flex-direction: column; }
            .filter-group { min-width: 100%; }
            .form-row { flex-direction: column; }
            .form-group-inline { min-width: 100%; }
            .summary-grid { grid-template-columns: 1fr 1fr; }
            .nav-tabs { flex-direction: column; }
        }
        @media (max-width: 480px) {
            .summary-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    
    <div class="content-page">
        <div class="content">
            <div class="container">
                
                <!-- Page Header -->
                <div class="page-header">
                    <h2><i class="fa fa-cubes"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                    <p>Complete inventory management with categories, suppliers, QR codes, and checkout workflow</p>
                </div>
                
                <?= msg($stat) ?>
                
                <!-- Tabs -->
                <div class="card">
                    <ul class="nav-tabs">
                        <li class="<?= (!isset($_GET['action']) || $_GET['action'] == '') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>"><i class="fa fa-list"></i> Dashboard</a>
                        </li>
                        <li class="<?= (isset($_GET['action']) && $_GET['action'] == 'add') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>?action=add"><i class="fa fa-plus"></i> Add Item</a>
                        </li>
                        <li class="<?= (isset($_GET['action']) && $_GET['action'] == 'checkouts') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>?action=checkouts"><i class="fa fa-hand-o-right"></i> Checkouts</a>
                        </li>
                        <li class="<?= (isset($_GET['action']) && $_GET['action'] == 'movements') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>?action=movements"><i class="fa fa-exchange"></i> Movements</a>
                        </li>
                    </ul>
                    
                    <div class="card-body">
                        
                        <!-- ============================================================ -->
                        <!-- TAB: DASHBOARD -->
                        <!-- ============================================================ -->
                        <?php if (!isset($_GET['action']) || $_GET['action'] == ''): ?>
                        
                        <!-- Summary Cards -->
                        <div class="summary-grid">
                            <div class="summary-card">
                                <span class="icon"><i class="fa fa-cubes"></i></span>
                                <div class="label">Total Items</div>
                                <div class="value"><?= number_format($summary['total_items'] ?? 0) ?></div>
                            </div>
                            <div class="summary-card info">
                                <span class="icon"><i class="fa fa-tags"></i></span>
                                <div class="label">Categories</div>
                                <div class="value"><?= number_format($summary['total_categories'] ?? 0) ?></div>
                            </div>
                            <div class="summary-card warning">
                                <span class="icon"><i class="fa fa-exclamation-triangle"></i></span>
                                <div class="label">Low Stock Items</div>
                                <div class="value"><?= number_format($summary['low_stock_count'] ?? 0) ?></div>
                            </div>
                            <div class="summary-card success">
                                <span class="icon"><i class="fa fa-money"></i></span>
                                <div class="label">Total Value</div>
                                <div class="value">₦<?= number_format($summary['total_value'] ?? 0, 2) ?></div>
                            </div>
                        </div>
                        
                        <!-- Filters -->
                        <div class="filter-row">
                            <div class="filter-group">
                                <label>Search</label>
                                <input type="text" class="filter-input" id="searchInput" placeholder="🔍 Search by name, asset #, brand..." onkeyup="applyFilters()">
                            </div>
                            <div class="filter-group">
                                <label>Category</label>
                                <select class="filter-select" id="categoryFilter" onchange="applyFilters()">
                                    <option value="">-- All --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Location</label>
                                <select class="filter-select" id="locationFilter" onchange="applyFilters()">
                                    <option value="">-- All --</option>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?= htmlspecialchars($loc['location']) ?>"><?= htmlspecialchars($loc['location']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group" style="flex: 0 0 auto; display: flex; gap: 8px; align-items: flex-end;">
                                <button class="btn btn-primary" onclick="applyFilters()"><i class="fa fa-filter"></i> Filter</button>
                                <a href="<?= $FileName ?>" class="btn btn-danger"><i class="fa fa-refresh"></i></a>
                                <button class="btn btn-info qr-scanner-btn" onclick="scanQR()">
                                    <i class="fa fa-qrcode"></i> Scan QR
                                </button>
                            </div>
                        </div>
                        
                        <!-- QR Scanner Modal -->
                        <div id="qrModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center;">
                            <div style="background:#fff; padding:30px; border-radius:14px; max-width:500px; width:90%; text-align:center;">
                                <h4 style="margin-bottom:15px;">📱 Scan QR Code</h4>
                                <div id="qr-reader" style="width:100%; max-width:400px; margin:0 auto;"></div>
                                <div id="qr-result" style="margin-top:15px; display:none;">
                                    <p style="color:#28a745; font-weight:600;">✅ Item Found!</p>
                                    <div id="qr-item-details"></div>
                                    <div style="margin-top:15px; display:flex; gap:10px; justify-content:center;">
                                        <a href="#" id="qrCheckoutBtn" class="btn btn-warning">📤 Checkout</a>
                                        <a href="#" id="qrViewBtn" class="btn btn-primary">👁️ View</a>
                                        <button onclick="closeQRScanner()" class="btn btn-danger">Close</button>
                                    </div>
                                </div>
                                <button onclick="closeQRScanner()" class="btn btn-danger" style="margin-top:15px;">Close Scanner</button>
                            </div>
                        </div>
                        
                        <!-- Inventory Table -->
                        <div class="table-wrapper">
                            <table class="table" id="inventoryTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Asset #</th>
                                        <th>Item Description</th>
                                        <th>Category</th>
                                        <th>QTY</th>
                                        <th>Available</th>
                                        <th>Unit Cost</th>
                                        <th>Total Value</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($inventoryItems)): ?>
                                        <?php $i = 0; foreach ($inventoryItems as $item): $i++; 
                                            $available = (int)$item['quantity_available'];
                                            $minStock = (int)$item['min_stock_level'];
                                            $isLowStock = ($minStock > 0 && $available <= $minStock);
                                            $totalValue = (float)$item['quantity'] * (float)$item['unit_cost'];
                                            $condition = $item['condition_status'] ?? 'good';
                                        ?>
                                        <tr class="<?= $isLowStock ? 'low-stock' : '' ?>">
                                            <td><?= $i ?></td>
                                            <td><strong><?= htmlspecialchars($item['asset_number'] ?? 'N/A') ?></strong></td>
                                            <td><?= htmlspecialchars($item['item_description']) ?>
                                                <?php if (!empty($item['brand'])): ?>
                                                    <br><small style="color:#888;"><?= htmlspecialchars($item['brand']) ?> <?= htmlspecialchars($item['model'] ?? '') ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($item['category_name'] ?? 'N/A') ?></td>
                                            <td><?= number_format($item['quantity']) ?></td>
                                            <td style="font-weight:700; color:<?= $isLowStock ? '#dc3545' : '#28a745' ?>;">
                                                <?= number_format($available) ?>
                                                <?php if ($isLowStock): ?>
                                                    <span class="badge badge-danger">Low Stock</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>₦<?= number_format((float)$item['unit_cost'], 2) ?></td>
                                            <td>₦<?= number_format($totalValue, 2) ?></td>
                                            <td><?= getConditionBadge($condition) ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="<?= $FileName ?>?action=qr&id=<?= $item['id'] ?>" target="_blank" class="btn btn-info btn-sm" title="QR Code">
                                                        <i class="fa fa-qrcode"></i>
                                                    </a>
                                                    <button class="btn btn-warning btn-sm" onclick="showCheckoutForm(<?= $item['id'] ?>, '<?= htmlspecialchars($item['item_description']) ?>', <?= $available ?>)" title="Checkout">
                                                        <i class="fa fa-hand-o-right"></i>
                                                    </button>
                                                    <button class="btn btn-primary btn-sm" onclick="editItem(<?= $item['id'] ?>)" title="Edit">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="10" class="text-center text-muted" style="padding:30px;">
                                                <i class="fa fa-inbox" style="font-size:40px; display:block; margin-bottom:10px;"></i>
                                                No inventory items found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Checkout Modal -->
                        <div id="checkoutModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
                            <div style="background:#fff; padding:30px; border-radius:14px; max-width:500px; width:90%;">
                                <h4>📤 Checkout Item</h4>
                                <form method="POST" action="">
                                    <input type="hidden" name="item_id" id="checkoutItemId">
                                    <div class="form-group">
                                        <label>Item</label>
                                        <input type="text" class="form-control" id="checkoutItemName" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Available Stock</label>
                                        <input type="text" class="form-control" id="checkoutAvailable" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Quantity *</label>
                                        <input type="number" class="form-control" name="quantity" required min="1" id="checkoutQuantity">
                                    </div>
                                    <div class="form-group">
                                        <label>Requested By *</label>
                                        <select class="form-control" name="requested_by" required>
                                            <option value="">-- Select Staff --</option>
                                            <?php foreach ($staffList as $staff): ?>
                                                <option value="<?= $staff['id'] ?>"><?= htmlspecialchars(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? '')) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Purpose *</label>
                                        <textarea class="form-control" name="purpose" rows="2" required placeholder="Why is this item needed?"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Expected Return Date</label>
                                        <input type="date" class="form-control" name="expected_return_date">
                                    </div>
                                    <div style="display:flex; gap:10px; margin-top:15px;">
                                        <button type="submit" name="checkout_request" class="btn btn-success">Submit Request</button>
                                        <button type="button" class="btn btn-danger" onclick="closeCheckoutForm()">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <script>
                        function applyFilters() {
                            const search = document.getElementById('searchInput').value;
                            const category = document.getElementById('categoryFilter').value;
                            const location = document.getElementById('locationFilter').value;
                            let url = '<?= $FileName ?>';
                            let params = [];
                            if (search) params.push('search=' + encodeURIComponent(search));
                            if (category) params.push('category=' + category);
                            if (location) params.push('location=' + encodeURIComponent(location));
                            if (params.length) url += '?' + params.join('&');
                            window.location.href = url;
                        }
                        
                        function showCheckoutForm(itemId, itemName, available) {
                            document.getElementById('checkoutItemId').value = itemId;
                            document.getElementById('checkoutItemName').value = itemName;
                            document.getElementById('checkoutAvailable').value = available + ' available';
                            document.getElementById('checkoutQuantity').max = available;
                            document.getElementById('checkoutModal').style.display = 'flex';
                        }
                        
                        function closeCheckoutForm() {
                            document.getElementById('checkoutModal').style.display = 'none';
                        }
                        
                        function editItem(itemId) {
                            window.location.href = '<?= $FileName ?>?action=edit&id=' + itemId;
                        }
                        
                        function scanQR() {
                            document.getElementById('qrModal').style.display = 'flex';
                            // Load QR scanner library
                            const script = document.createElement('script');
                            script.src = 'https://unpkg.com/html5-qrcode';
                            script.onload = function() {
                                const html5QrCode = new Html5Qrcode("qr-reader");
                                html5QrCode.start(
                                    { facingMode: "environment" },
                                    { fps: 10, qrbox: { width: 250, height: 250 } },
                                    onScanSuccess
                                );
                            };
                            document.head.appendChild(script);
                        }
                        
                        function onScanSuccess(decodedText) {
                            try {
                                const data = JSON.parse(decodedText);
                                document.getElementById('qr-result').style.display = 'block';
                                document.getElementById('qr-item-details').innerHTML = `
                                    <strong>Asset #:</strong> ${data.asset_number}<br>
                                    <strong>Item:</strong> ${data.description}<br>
                                    <strong>Location:</strong> ${data.location}
                                `;
                                document.getElementById('qrCheckoutBtn').href = '<?= $FileName ?>?action=checkout&id=' + data.id;
                                document.getElementById('qrViewBtn').href = '<?= $FileName ?>?action=view&id=' + data.id;
                            } catch(e) {
                                alert('Invalid QR code. Please scan a valid inventory QR code.');
                            }
                        }
                        
                        function closeQRScanner() {
                            document.getElementById('qrModal').style.display = 'none';
                            document.getElementById('qr-result').style.display = 'none';
                        }
                        </script>
                        
                        <!-- ============================================================ -->
                        <!-- TAB: ADD ITEM -->
                        <!-- ============================================================ -->
                        <?php elseif (isset($_GET['action']) && $_GET['action'] == 'add'): ?>
                        
                        <h4><i class="fa fa-plus"></i> Add Inventory Item</h4>
                        
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="form-row">
                                <div class="form-group-inline">
                                    <label>Item Description *</label>
                                    <input type="text" class="form-control" name="item_description" required placeholder="Enter item name">
                                </div>
                                <div class="form-group-inline">
                                    <label>Category</label>
                                    <select class="form-control" name="category_id">
                                        <option value="0">-- Select --</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group-inline">
                                    <label>Supplier</label>
                                    <select class="form-control" name="supplier_id">
                                        <option value="0">-- Select --</option>
                                        <?php foreach ($suppliers as $sup): ?>
                                            <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group-inline">
                                    <label>Location *</label>
                                    <input type="text" class="form-control" name="location" required placeholder="e.g., Store Room A">
                                </div>
                                <div class="form-group-inline">
                                    <label>Unit</label>
                                    <input type="text" class="form-control" name="unit" placeholder="e.g., Pcs, Box, Kg">
                                </div>
                                <div class="form-group-inline">
                                    <label>Quantity *</label>
                                    <input type="number" class="form-control" name="quantity" required min="0">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group-inline">
                                    <label>Unit Cost (₦) *</label>
                                    <input type="number" class="form-control" name="unit_cost" required step="0.01" min="0">
                                </div>
                                <div class="form-group-inline">
                                    <label>Min Stock Level</label>
                                    <input type="number" class="form-control" name="min_stock_level" value="0" min="0">
                                </div>
                                <div class="form-group-inline">
                                    <label>Max Stock Level</label>
                                    <input type="number" class="form-control" name="max_stock_level" value="0" min="0">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group-inline">
                                    <label>Brand</label>
                                    <input type="text" class="form-control" name="brand" placeholder="Brand name">
                                </div>
                                <div class="form-group-inline">
                                    <label>Model</label>
                                    <input type="text" class="form-control" name="model" placeholder="Model number">
                                </div>
                                <div class="form-group-inline">
                                    <label>Serial Number</label>
                                    <input type="text" class="form-control" name="serial_number" placeholder="Serial #">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group-inline">
                                    <label>Purchase Date</label>
                                    <input type="date" class="form-control" name="purchase_date">
                                </div>
                                <div class="form-group-inline">
                                    <label>Notes</label>
                                    <input type="text" class="form-control" name="notes" placeholder="Additional notes">
                                </div>
                            </div>
                            
                            <div style="margin-top: 20px;">
                                <button type="submit" name="add_item" class="btn btn-success">
                                    <i class="fa fa-save"></i> Save Item
                                </button>
                                <a href="<?= $FileName ?>" class="btn btn-danger">
                                    <i class="fa fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                        
                        <!-- ============================================================ -->
                        <!-- TAB: CHECKOUTS -->
                        <!-- ============================================================ -->
                        <?php elseif (isset($_GET['action']) && $_GET['action'] == 'checkouts'): ?>
                        
                        <h4><i class="fa fa-hand-o-right"></i> Checkout Requests</h4>
                        
                        <?php
                        $pendingCount = 0;
                        foreach ($checkoutRequests as $co) {
                            if ($co['status'] == 'pending') $pendingCount++;
                        }
                        ?>
                        <div style="margin-bottom:15px;">
                            <span class="badge badge-warning">⏳ Pending: <?= $pendingCount ?></span>
                            <span class="badge badge-success">✅ Approved: <?= count(array_filter($checkoutRequests, function($c) { return $c['status'] == 'approved'; })) ?></span>
                            <span class="badge badge-info">↩️ Returned: <?= count(array_filter($checkoutRequests, function($c) { return $c['status'] == 'returned'; })) ?></span>
                        </div>
                        
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item</th>
                                        <th>Requested By</th>
                                        <th>QTY</th>
                                        <th>Purpose</th>
                                        <th>Expected Return</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($checkoutRequests)): ?>
                                        <?php $i = 0; foreach ($checkoutRequests as $co): $i++; ?>
                                        <tr>
                                            <td><?= $i ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($co['item_description'] ?? 'N/A') ?></strong>
                                                <br><small style="color:#888;"><?= htmlspecialchars($co['asset_number'] ?? '') ?></small>
                                            </td>
                                            <td><?= htmlspecialchars(($co['requester_first'] ?? '') . ' ' . ($co['requester_last'] ?? '')) ?></td>
                                            <td><?= $co['quantity'] ?></td>
                                            <td><?= htmlspecialchars(substr($co['purpose'] ?? '', 0, 30)) ?>...</td>
                                            <td><?= $co['expected_return_date'] ? date('d M Y', strtotime($co['expected_return_date'])) : 'N/A' ?></td>
                                            <td><?= getStatusBadge($co['status']) ?></td>
                                            <td>
                                                <?php if ($co['status'] == 'pending'): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="checkout_id" value="<?= $co['id'] ?>">
                                                        <button type="submit" name="approve_checkout" class="btn btn-success btn-sm">
                                                            <i class="fa fa-check"></i> Approve
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if ($co['status'] == 'approved'): ?>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Return this item?')">
                                                        <input type="hidden" name="checkout_id" value="<?= $co['id'] ?>">
                                                        <input type="hidden" name="condition_on_return" value="good">
                                                        <button type="submit" name="return_item" class="btn btn-info btn-sm">
                                                            <i class="fa fa-undo"></i> Return
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted" style="padding:30px;">
                                                No checkout requests found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- ============================================================ -->
                        <!-- TAB: MOVEMENTS -->
                        <!-- ============================================================ -->
                        <?php elseif (isset($_GET['action']) && $_GET['action'] == 'movements'): ?>
                        
                        <h4><i class="fa fa-exchange"></i> Stock Movement History</h4>
                        
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Item</th>
                                        <th>Type</th>
                                        <th>QTY</th>
                                        <th>Previous</th>
                                        <th>New</th>
                                        <th>Reason</th>
                                        <th>By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($movements)): ?>
                                        <?php $i = 0; foreach ($movements as $mov): $i++; ?>
                                        <tr>
                                            <td><?= $i ?></td>
                                            <td><?= date('d M Y H:i', strtotime($mov['movement_date'])) ?></td>
                                            <td>
                                                <?= htmlspecialchars($mov['item_description'] ?? 'N/A') ?>
                                                <br><small style="color:#888;"><?= htmlspecialchars($mov['asset_number'] ?? '') ?></small>
                                            </td>
                                            <td><?= getMovementTypeBadge($mov['movement_type']) ?></td>
                                            <td><strong><?= $mov['quantity'] ?></strong></td>
                                            <td><?= $mov['previous_quantity'] ?></td>
                                            <td><?= $mov['new_quantity'] ?></td>
                                            <td><?= htmlspecialchars(substr($mov['reason'] ?? '', 0, 30)) ?>...</td>
                                            <td><?= htmlspecialchars(($mov['staff_first'] ?? '') . ' ' . ($mov['staff_last'] ?? '')) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted" style="padding:30px;">
                                                No stock movements recorded
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php endif; ?>
                        
                    </div>
                </div>
                
            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>

<?php include('inc.js.php'); ?>
</body>
</html>