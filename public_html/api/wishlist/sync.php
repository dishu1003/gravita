<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if it's an AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['error' => 'Direct access not allowed']);
    exit;
}

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

$userId = $_SESSION['user_id'];

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
$operations = $data['operations'] ?? [];
$currentWishlist = $data['currentWishlist'] ?? [];

try {
    // Begin transaction
    $pdo->beginTransaction();
    
    // Process operations
    foreach ($operations as $op) {
        if ($op['action'] === 'add') {
            // Add to wishlist
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO wishlist (user_id, product_id, created_at) 
                VALUES (:user_id, :product_id, NOW())
            ");
            $stmt->execute([
                'user_id' => $userId,
                'product_id' => $op['productId']
            ]);
        } elseif ($op['action'] === 'remove') {
            // Remove from wishlist
            $stmt = $pdo->prepare("
                DELETE FROM wishlist 
                WHERE user_id = :user_id AND product_id = :product_id
            ");
            $stmt->execute([
                'user_id' => $userId,
                'product_id' => $op['productId']
            ]);
        }
    }
    
    // Get updated wishlist from server
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.name,
            p.slug,
            p.price,
            p.image,
            w.created_at as addedAt
        FROM wishlist w
        JOIN products p ON w.product_id = p.id
        WHERE w.user_id = :user_id
        ORDER BY w.created_at DESC
    ");
    $stmt->execute(['user_id' => $userId]);
    $wishlist = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format wishlist items
    $formattedWishlist = array_map(function($item) {
        return [
            'id' => (string)$item['id'],
            'name' => $item['name'],
            'slug' => $item['slug'],
            'price' => (string)$item['price'],
            'image' => $item['image'] ?: 'placeholder.jpg',
            'addedAt' => $item['addedAt']
        ];
    }, $wishlist);
    
    // Commit transaction
    $pdo->commit();
    
    // Log activity
    logUserActivity($pdo, $userId, 'wishlist_sync', [
        'operations' => count($operations),
        'items' => count($formattedWishlist)
    ]);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'wishlist' => $formattedWishlist,
        'synced_at' => date('c')
    ]);
    
} catch (Exception $e) {
    // Rollback transaction
    $pdo->rollback();
    
    log_error('wishlist_sync', $e);
    http_response_code(500);
    echo json_encode(['error' => 'Sync failed']);
}

// Helper function to log user activity
function logUserActivity($pdo, $userId, $action, $meta = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO user_activity_logs (user_id, action, meta, created_at) 
            VALUES (:user_id, :action, :meta, NOW())
        ");
        $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'meta' => $meta ? json_encode($meta) : null
        ]);
    } catch (Exception $e) {
        // Silently fail - don't break main functionality
    }
}