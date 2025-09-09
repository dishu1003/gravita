<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$base = rtrim(SITE_URL, '/');
$urls = [
    ['loc' => $base . '/', 'priority' => '1.0'],
    ['loc' => $base . '/shop.php', 'priority' => '0.9'],
    ['loc' => $base . '/quiz.php', 'priority' => '0.6'],
    ['loc' => $base . '/contact.php', 'priority' => '0.5']
];

try {
    $stmt = $pdo->query("SELECT slug, updated_at FROM orders WHERE 1=0"); // placeholder
} catch (Throwable $e) { /* ignore */ }

try {
    $p = $pdo->query("SELECT slug, created_at FROM products ORDER BY created_at DESC");
    while ($row = $p->fetch(PDO::FETCH_ASSOC)) {
        $urls[] = ['loc' => $base . '/product.php?slug=' . urlencode($row['slug']), 'priority' => '0.8'];
    }
} catch (Throwable $e) { log_error('sitemap_products', $e); }

$xml = new DOMDocument('1.0', 'UTF-8');
$xml->formatOutput = true;
$urlset = $xml->createElement('urlset');
$urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
foreach ($urls as $u) {
    $url = $xml->createElement('url');
    $loc = $xml->createElement('loc', htmlspecialchars($u['loc']));
    $url->appendChild($loc);
    if (isset($u['priority'])) {
        $url->appendChild($xml->createElement('priority', $u['priority']));
    }
    $urlset->appendChild($url);
}
$xml->appendChild($urlset);
file_put_contents(__DIR__ . '/sitemap.xml', $xml->saveXML());

// Output to browser too
header('Content-Type: application/xml; charset=UTF-8');
echo $xml->saveXML();
