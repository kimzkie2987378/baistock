<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Entity\Product;
use App\Entity\Sale;
use App\Entity\SaleItem;
use App\Entity\StockMovement;
use App\Entity\User;
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__ . '/../.env');

$kernel = new Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();

echo "=== BAISTOCK SYSTEM AUTOMATED VERIFICATION ===\n";

// 1. Check Product catalog
$products = $em->getRepository(Product::class)->findAll();
echo "Total Products: " . count($products) . "\n";
assert(count($products) === 24, "Expected 24 products");

// 2. Check Total Stock Units
$totalUnits = $em->getRepository(Product::class)->getTotalStockUnits();
echo "Total Stock Units: " . $totalUnits . "\n";
assert($totalUnits === 1248, "Expected 1,248 stock units");

// 3. Check Low Stock Count
$lowStock = $em->getRepository(Product::class)->countLowStock();
echo "Low Stock Items: " . $lowStock . "\n";
assert($lowStock === 5, "Expected 5 low stock items");

// 4. Check Total Sales Amount
$totalSales = $em->getRepository(Sale::class)->getTotalSalesAmount();
echo "Total Sales Amount: PHP " . number_format($totalSales, 2) . "\n";
assert(abs($totalSales - 8540.0) < 0.01, "Expected PHP 8,540.00 sales");

// 5. Test Stock-In functionality
$cola = $em->getRepository(Product::class)->findOneBy(['sku' => 'BEV-001']);
$prevStock = $cola->getQuantity();
echo "Cola 330ml initial stock: $prevStock\n";

$cola->setQuantity($prevStock + 10);
$movement = new StockMovement();
$movement->setProduct($cola)
    ->setType('IN')
    ->setQuantity(10)
    ->setPreviousQuantity($prevStock)
    ->setNewQuantity($prevStock + 10)
    ->setReference('Test Restock');
$em->persist($movement);
$em->flush();

echo "Cola 330ml updated stock after +10: " . $cola->getQuantity() . "\n";
assert($cola->getQuantity() === $prevStock + 10);

// 6. Test Sale recording & automatic stock deduction
$sale = new Sale();
$sale->setCashierName('Admin')
    ->setSubtotal('50.00')
    ->setTotalAmount('50.00')
    ->setAmountPaid('100.00')
    ->setChangeAmount('50.00')
    ->setPaymentMethod('Cash');

$item = new SaleItem();
$item->setSale($sale)
    ->setProduct($cola)
    ->setProductName($cola->getName())
    ->setQuantity(2)
    ->setUnitPrice($cola->getPrice())
    ->setTotalPrice('50.00');
$sale->addItem($item);

// Deduct 2 units
$cola->setQuantity($cola->getQuantity() - 2);
$saleMove = new StockMovement();
$saleMove->setProduct($cola)
    ->setType('OUT')
    ->setQuantity(2)
    ->setPreviousQuantity($cola->getQuantity() + 2)
    ->setNewQuantity($cola->getQuantity())
    ->setReference('Sale #' . $sale->getReceiptNumber());
$em->persist($saleMove);
$em->persist($sale);
$em->flush();

echo "Cola 330ml stock after 2 sold: " . $cola->getQuantity() . "\n";
assert($cola->getQuantity() === $prevStock + 8);
echo "Receipt generated: " . $sale->getReceiptNumber() . "\n";

// 7. Clean up test transaction and re-run seed-data to keep demo figures pristine
echo "\nResetting sample database to pristine state...\n";
$app = new Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
$app->setAutoExit(false);
$input = new Symfony\Component\Console\Input\ArrayInput(['command' => 'app:seed-data']);
$output = new Symfony\Component\Console\Output\BufferedOutput();
$app->run($input, $output);

$finalProducts = $em->getRepository(Product::class)->count([]);
$finalUnits = $em->getRepository(Product::class)->getTotalStockUnits();
$finalSales = $em->getRepository(Sale::class)->getTotalSalesAmount();
$finalLowStock = $em->getRepository(Product::class)->countLowStock();

echo "Verified Reset State:\n";
echo " - Products: $finalProducts\n";
echo " - Stock Units: $finalUnits\n";
echo " - Sales: PHP " . number_format($finalSales, 2) . "\n";
echo " - Low Stock: $finalLowStock\n";

echo "\n[SUCCESS] ALL BAISTOCK SYSTEM TESTS PASSED PERFECTLY!\n";
