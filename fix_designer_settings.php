<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3309;dbname=phpmyadmin', 'root', 'root');

// 1. Enable relation_lines = true by default for all users
$val = json_encode([
    'angular_direct' => 'angular',
    'snap_to_grid' => 'off',
    'relation_lines' => 'true'
]);

$stmt = $pdo->prepare("REPLACE INTO pma__designer_settings (username, settings_data) VALUES (?, ?)");
$stmt->execute(['baistock_user', $val]);
$stmt->execute(['root', $val]);
$stmt->execute(['', $val]);

echo "Updated pma__designer_settings:\n";
$rows = $pdo->query("SELECT * FROM pma__designer_settings")->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);

// 2. Insert clean page and coordinates into pma__pdf_pages and pma__table_coords
// First create a default page if not exists
$pdo->exec("DELETE FROM pma__pdf_pages WHERE db_name = 'baistock_db'");
$pdo->exec("INSERT INTO pma__pdf_pages (db_name, page_descr) VALUES ('baistock_db', 'baistock_schema')");
$pageId = (int)$pdo->lastInsertId();
echo "Created pma__pdf_pages with id: $pageId\n";

$coords = [
    'categories' => [50, 80],
    'products' => [300, 80],
    'stock_movements' => [560, 80],
    'sales' => [960, 80],
    'users' => [1220, 80],
    'settings' => [50, 480],
    'deliveries' => [300, 480],
    'delivery_items' => [560, 480],
    'sale_items' => [960, 440],
    'notifications' => [1220, 480],
];

// Insert for page 0 (default designer view) and for the newly created page
$pdo->exec("DELETE FROM pma__table_coords WHERE db_name = 'baistock_db'");

$stmtCoord = $pdo->prepare("INSERT INTO pma__table_coords (db_name, table_name, pdf_page_number, x, y) VALUES ('baistock_db', ?, ?, ?, ?)");

foreach ([0, $pageId] as $pNum) {
    foreach ($coords as $tbl => [$x, $y]) {
        $stmtCoord->execute([$tbl, $pNum, $x, $y]);
    }
}

echo "Inserted table_coords count: " . $pdo->query("SELECT COUNT(*) FROM pma__table_coords WHERE db_name = 'baistock_db'")->fetchColumn() . "\n";
echo "SUCCESS!\n";
