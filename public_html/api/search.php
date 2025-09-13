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

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$useAI = isset($_GET['ai']) && $_GET['ai'] === 'true';

if (strlen($query) < 2) {
    echo json_encode(['products' => [], 'count' => 0]);
    exit;
}

try {
    // Build search query with relevance scoring
    $searchQuery = "
        SELECT 
            p.*,
            c.name as category_name,
            c.slug as category_slug,
            -- Relevance scoring
            (
                CASE 
                    WHEN p.name LIKE :exact THEN 100
                    WHEN p.name LIKE :start THEN 80
                    WHEN p.name LIKE :anywhere THEN 60
                    WHEN p.scent_notes LIKE :anywhere THEN 40
                    WHEN p.description LIKE :anywhere THEN 20
                    ELSE 0
                END
            ) as relevance_score
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 
            p.name LIKE :anywhere
            OR p.scent_notes LIKE :anywhere
            OR p.description LIKE :anywhere
            OR c.name LIKE :anywhere
        ORDER BY 
            relevance_score DESC,
            p.is_bestseller DESC,
            p.created_at DESC
        LIMIT 20
    ";
    
    $stmt = $pdo->prepare($searchQuery);
    $stmt->execute([
        'exact' => $query,
        'start' => $query . '%',
        'anywhere' => '%' . $query . '%'
    ]);
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process products for response
    $processedProducts = array_map(function($product) {
        return [
            'id' => $product['id'],
            'name' => $product['name'],
            'slug' => $product['slug'],
            'price' => $product['price'],
            'mrp' => $product['mrp'],
            'image' => $product['image'] ?: 'placeholder.jpg',
            'scent_notes' => $product['scent_notes'],
            'is_bestseller' => (bool)$product['is_bestseller'],
            'category' => $product['category_name'],
            'category_slug' => $product['category_slug']
        ];
    }, $products);
    
    // AI-powered features (simulated for now)
    $suggestions = [];
    if ($useAI) {
        // In a real implementation, this would use ML models
        $suggestions = generateAISuggestions($query, $products);
    }
    
    // Log search query for analytics
    logSearchQuery($query, count($products));
    
    // Return results
    echo json_encode([
        'products' => $processedProducts,
        'count' => count($products),
        'query' => $query,
        'suggestions' => $suggestions
    ]);
    
} catch (Exception $e) {
    log_error('search_api', $e);
    http_response_code(500);
    echo json_encode(['error' => 'Search failed']);
}

// Helper function to generate AI suggestions
function generateAISuggestions($query, $products) {
    $suggestions = [];
    
    // Extract common scent notes from results
    $scentNotes = [];
    foreach ($products as $product) {
        if ($product['scent_notes']) {
            $notes = array_map('trim', explode(',', $product['scent_notes']));
            $scentNotes = array_merge($scentNotes, $notes);
        }
    }
    $scentNotes = array_unique($scentNotes);
    
    // Generate contextual suggestions
    if (!empty($scentNotes)) {
        $suggestions[] = [
            'type' => 'scent',
            'text' => 'Explore ' . implode(' & ', array_slice($scentNotes, 0, 2)) . ' fragrances'
        ];
    }
    
    // Category suggestions
    $categories = array_unique(array_column($products, 'category_name'));
    if (!empty($categories)) {
        $suggestions[] = [
            'type' => 'category',
            'text' => 'Browse ' . implode(' and ', $categories)
        ];
    }
    
    // Related searches
    $relatedTerms = generateRelatedTerms($query);
    foreach ($relatedTerms as $term) {
        $suggestions[] = [
            'type' => 'related',
            'text' => $term
        ];
    }
    
    return array_slice($suggestions, 0, 3);
}

// Generate related search terms
function generateRelatedTerms($query) {
    // Simple related terms based on common perfume searches
    $termMap = [
        'oud' => ['Arabian Oud', 'Oud Wood', 'Royal Oud'],
        'rose' => ['Rose Absolute', 'Damascus Rose', 'Rose Garden'],
        'musk' => ['White Musk', 'Egyptian Musk', 'Musk Blend'],
        'floral' => ['Floral Bouquet', 'Garden Flowers', 'Spring Blossoms'],
        'woody' => ['Woody Notes', 'Sandalwood', 'Cedar Wood'],
        'fresh' => ['Fresh Citrus', 'Ocean Breeze', 'Morning Dew']
    ];
    
    $related = [];
    $queryLower = strtolower($query);
    
    foreach ($termMap as $key => $terms) {
        if (stripos($queryLower, $key) !== false) {
            $related = array_merge($related, $terms);
        }
    }
    
    return array_slice($related, 0, 2);
}

// Log search queries for analytics
function logSearchQuery($query, $resultCount) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO search_logs (query, result_count, created_at) 
            VALUES (:query, :count, NOW())
        ");
        $stmt->execute([
            'query' => $query,
            'count' => $resultCount
        ]);
    } catch (Exception $e) {
        // Silently fail - don't break search if logging fails
    }
}