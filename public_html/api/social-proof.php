<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if it's an AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['error' => 'Direct access not allowed']);
    exit;
}

try {
    // Get recent activity data
    $data = [
        'recent_purchases' => getRecentPurchases($pdo),
        'viewer_counts' => getViewerCounts($pdo),
        'stock_levels' => getStockLevels($pdo),
        'active_offers' => getActiveOffers($pdo)
    ];
    
    echo json_encode($data);
    
} catch (Exception $e) {
    log_error('social_proof_api', $e);
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch data']);
}

function getRecentPurchases($pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            o.customer_name as name,
            p.name as product,
            o.created_at,
            SUBSTRING_INDEX(o.shipping_address, ',', -2) as location
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.status IN ('paid', 'processing', 'shipped', 'delivered')
        ORDER BY o.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Anonymize customer names
    foreach ($purchases as &$purchase) {
        $nameParts = explode(' ', $purchase['name']);
        $purchase['name'] = $nameParts[0] . ' ' . substr($nameParts[1] ?? 'K', 0, 1) . '.';
        $purchase['time_ago'] = getTimeAgo($purchase['created_at']);
    }
    
    return $purchases;
}

function getViewerCounts($pdo) {
    // In a real implementation, this would track actual page views
    // For now, return simulated data based on product popularity
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.name,
            COUNT(oi.id) as order_count
        FROM products p
        LEFT JOIN order_items oi ON p.id = oi.product_id
        GROUP BY p.id
        ORDER BY order_count DESC
        LIMIT 5
    ");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $viewerCounts = [];
    foreach ($products as $product) {
        // Simulate viewer count based on popularity
        $baseViewers = 5 + ($product['order_count'] * 2);
        $viewerCounts[$product['id']] = [
            'product' => $product['name'],
            'viewers' => rand(max(1, $baseViewers - 3), $baseViewers + 3)
        ];
    }
    
    return $viewerCounts;
}

function getStockLevels($pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            id,
            name,
            stock
        FROM products
        WHERE stock > 0 AND stock <= 10
        ORDER BY stock ASC
        LIMIT 5
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getActiveOffers($pdo) {
    // Check for any active discount settings or promotions
    $offers = [];
    
    // Flash sale example (you can make this dynamic)
    $flashSaleEnd = date('Y-m-d H:i:s', strtotime('+6 hours'));
    $offers[] = [
        'type' => 'flash',
        'discount' => 20,
        'products' => ['Oud Royale', 'Rose Saffron Attar'],
        'ends_at' => $flashSaleEnd
    ];
    
    return $offers;
}

function getTimeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->d > 0) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    } elseif ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } elseif ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    } else {
        return 'Just now';
    }
}