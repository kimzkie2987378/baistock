<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Delivery;
use App\Entity\Product;
use App\Entity\Sale;
use App\Entity\SaleItem;
use App\Entity\Setting;
use App\Entity\StockMovement;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed-data',
    description: 'Seed initial sample data matching BaiStock specifications and design'
)]
class SeedDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Seeding BaiStock sample data into database...</info>');

        // 1. Seed Users
        $userRepo = $this->em->getRepository(User::class);
        $admin = $userRepo->findOneBy(['username' => 'admin']);
        if (!$admin) {
            $admin = new User();
            $admin->setUsername('admin')
                ->setEmail('admin@baistock.ph')
                ->setFullName('Admin Manager')
                ->setRole('admin')
                ->setStatus('active')
                ->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
            $this->em->persist($admin);
        } else {
            $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'))
                  ->setRole('admin');
        }

        $owner = $userRepo->findOneBy(['username' => 'owner']);
        if (!$owner) {
            $owner = new User();
            $owner->setUsername('owner')
                ->setEmail('owner@baistock.ph')
                ->setFullName('Store Owner')
                ->setRole('owner')
                ->setStatus('active')
                ->setPassword($this->passwordHasher->hashPassword($owner, 'owner123'));
            $this->em->persist($owner);
        } else {
            $owner->setPassword($this->passwordHasher->hashPassword($owner, 'owner123'))
                  ->setRole('owner');
        }

        $staff = $userRepo->findOneBy(['username' => 'staff']);
        if (!$staff) {
            $staff = new User();
            $staff->setUsername('staff')
                ->setEmail('staff@baistock.ph')
                ->setFullName('Maria Santos')
                ->setRole('staff')
                ->setStatus('active')
                ->setPassword($this->passwordHasher->hashPassword($staff, 'staff123'));
            $this->em->persist($staff);
        } else {
            $staff->setPassword($this->passwordHasher->hashPassword($staff, 'staff123'))
                  ->setRole('staff');
        }

        $this->em->flush();

        // 2. Settings
        $settingRepo = $this->em->getRepository(Setting::class);
        $settingsData = [
            'store_name' => 'BaiStock Neighborhood Store',
            'store_address' => 'Corner Rizal & Luna St., Cebu City',
            'store_contact' => '+63 917 123 4567',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'default_min_stock' => '10',
            'receipt_footer' => 'Salamat sa pagpalit! Mabuhay!',
        ];

        foreach ($settingsData as $k => $v) {
            $s = $settingRepo->findOneBy(['settingKey' => $k]);
            if (!$s) {
                $s = new Setting();
                $s->setSettingKey($k);
            }
            $s->setSettingValue($v);
            $this->em->persist($s);
        }
        $this->em->flush();

        // 3. Categories
        $catRepo = $this->em->getRepository(Category::class);
        $categoryNames = [
            'Snacks' => 'Chips, crackers, biscuits, and packed snacks',
            'Fruits' => 'Fresh and seasonal fruits',
            'Canned Goods' => 'Canned fish, meat, and processed food',
            'Vegetables' => 'Fresh produce and cooking vegetables',
            'Groceries' => 'Beverages, pantry essentials, rice, noodles, and general grocery items',
        ];

        $categories = [];
        foreach ($categoryNames as $name => $desc) {
            $cat = $catRepo->findOneBy(['categoryName' => $name]);
            if (!$cat) {
                $cat = new Category();
                $cat->setCategoryName($name);
                $cat->setDescription($desc);
                $this->em->persist($cat);
            }
            $categories[$name] = $cat;
        }
        $this->em->flush();

        // 4. Products Catalog
        $productRepo = $this->em->getRepository(Product::class);
        $productsCatalog = [
            // The 5 key low stock items
            ['name' => 'Cola 330ml', 'category' => 'Groceries', 'price' => 25.00, 'qty' => 3, 'min' => 10, 'unit' => 'can'],
            ['name' => 'Chips (Lays)', 'category' => 'Snacks', 'price' => 35.00, 'qty' => 5, 'min' => 10, 'unit' => 'pack'],
            ['name' => 'Red Apples (Per Pc)', 'category' => 'Fruits', 'price' => 20.00, 'qty' => 7, 'min' => 15, 'unit' => 'pc'],
            ['name' => 'Fresh Tomatoes 500g', 'category' => 'Vegetables', 'price' => 40.00, 'qty' => 4, 'min' => 10, 'unit' => 'pack'],
            ['name' => 'Instant Noodles', 'category' => 'Groceries', 'price' => 35.00, 'qty' => 6, 'min' => 10, 'unit' => 'pouch'],

            // Products to reach 24 products and 1,248 total units
            ['name' => 'Sprite 330ml', 'category' => 'Groceries', 'price' => 25.00, 'qty' => 48, 'min' => 10, 'unit' => 'can'],
            ['name' => 'Royal Tru-Orange 330ml', 'category' => 'Groceries', 'price' => 25.00, 'qty' => 52, 'min' => 10, 'unit' => 'can'],
            ['name' => 'Bottled Water 500ml', 'category' => 'Groceries', 'price' => 20.00, 'qty' => 36, 'min' => 10, 'unit' => 'bottle'],
            ['name' => 'Piattos Cheese 85g', 'category' => 'Snacks', 'price' => 38.00, 'qty' => 64, 'min' => 12, 'unit' => 'pack'],
            ['name' => 'Chippy Barbecue 110g', 'category' => 'Snacks', 'price' => 32.00, 'qty' => 70, 'min' => 15, 'unit' => 'pack'],
            ['name' => 'SkyFlakes Crackers (10s)', 'category' => 'Snacks', 'price' => 65.00, 'qty' => 45, 'min' => 10, 'unit' => 'pack'],
            ['name' => 'Cavendish Bananas (1kg)', 'category' => 'Fruits', 'price' => 85.00, 'qty' => 22, 'min' => 8, 'unit' => 'kg'],
            ['name' => 'Yellow Mango (1kg)', 'category' => 'Fruits', 'price' => 140.00, 'qty' => 35, 'min' => 10, 'unit' => 'kg'],
            ['name' => 'Corned Beef 150g', 'category' => 'Canned Goods', 'price' => 55.00, 'qty' => 85, 'min' => 15, 'unit' => 'can'],
            ['name' => 'Canned Tuna Flakes in Oil', 'category' => 'Canned Goods', 'price' => 42.00, 'qty' => 95, 'min' => 15, 'unit' => 'can'],
            ['name' => 'Sardines in Tomato Sauce', 'category' => 'Canned Goods', 'price' => 26.00, 'qty' => 110, 'min' => 20, 'unit' => 'can'],
            ['name' => 'Sinandomeng Rice 5kg', 'category' => 'Groceries', 'price' => 265.00, 'qty' => 28, 'min' => 5, 'unit' => 'sack'],
            ['name' => 'Cooking Oil 1 Liter', 'category' => 'Groceries', 'price' => 88.00, 'qty' => 42, 'min' => 10, 'unit' => 'bottle'],
            ['name' => 'Red Onions 500g', 'category' => 'Vegetables', 'price' => 60.00, 'qty' => 60, 'min' => 15, 'unit' => 'pouch'],
            ['name' => 'Native Garlic 500g', 'category' => 'Vegetables', 'price' => 75.00, 'qty' => 50, 'min' => 12, 'unit' => 'pack'],
            ['name' => 'Soy Sauce 340ml', 'category' => 'Groceries', 'price' => 24.00, 'qty' => 75, 'min' => 15, 'unit' => 'bottle'],
            ['name' => 'Vinegar 340ml', 'category' => 'Groceries', 'price' => 22.00, 'qty' => 78, 'min' => 15, 'unit' => 'bottle'],
            ['name' => 'Potatoes 1kg', 'category' => 'Vegetables', 'price' => 90.00, 'qty' => 92, 'min' => 20, 'unit' => 'kg'],
            ['name' => 'Carrots 1kg', 'category' => 'Vegetables', 'price' => 85.00, 'qty' => 136, 'min' => 20, 'unit' => 'kg'],
        ];

        $productEntities = [];
        foreach ($productsCatalog as $item) {
            $prod = $productRepo->findOneBy(['productName' => $item['name']]);
            if (!$prod) {
                $prod = new Product();
                $prod->setProductName($item['name']);
            }
            if (isset($categories[$item['category']])) {
                $prod->setCategory($categories[$item['category']]);
            }
            $prod->setPrice($item['price'])
                ->setQuantity($item['qty'])
                ->setMinimumStock($item['min'])
                ->setUnit($item['unit']);
            $this->em->persist($prod);
            $productEntities[$item['name']] = $prod;
        }

        $this->em->flush();

        // 5. Seed Deliveries
        $deliveryRepo = $this->em->getRepository(Delivery::class);
        $deliveries = [
            ['tracking' => 'DEL-2026-089', 'supplier' => 'San Miguel Beverages Inc.', 'days' => 1, 'status' => 'Pending', 'cost' => 12500.00, 'notes' => 'Cola & Sprite restock batch'],
            ['tracking' => 'DEL-2026-088', 'supplier' => 'Universal Robina Corp.', 'days' => -1, 'status' => 'In Transit', 'cost' => 8400.00, 'notes' => 'Snacks and chips assortment'],
            ['tracking' => 'DEL-2026-087', 'supplier' => 'Monde Nissin Corp.', 'days' => -3, 'status' => 'Delivered', 'cost' => 9650.00, 'notes' => 'Instant noodles and bakery items'],
            ['tracking' => 'DEL-2026-086', 'supplier' => 'Nestle Philippines', 'days' => -7, 'status' => 'Delivered', 'cost' => 15200.00, 'notes' => 'Coffee & Dairy supplies'],
        ];

        foreach ($deliveries as $d) {
            $del = $deliveryRepo->findOneBy(['trackingNumber' => $d['tracking']]);
            if (!$del) {
                $del = new Delivery();
                $del->setTrackingNumber($d['tracking'])
                    ->setSupplier($d['supplier'])
                    ->setStatus($d['status'])
                    ->setTotalCost($d['cost'])
                    ->setNotes($d['notes'])
                    ->setExpectedDate(new \DateTime(($d['days'] >= 0 ? '+' : '') . $d['days'] . ' days'));
                if ($d['status'] === 'Delivered') {
                    $del->setReceivedDate(new \DateTime($d['days'] . ' days'));
                }
                $this->em->persist($del);
            }
        }
        $this->em->flush();

        // 6. Reset & Seed Sales to match exact ₱8,540.00 and recent transactions
        $this->em->createQuery('DELETE FROM App\Entity\SaleItem si')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Sale s')->execute();

        // The 5 recent transactions from Image 3
        $recentSalesSeed = [
            ['product' => 'Cola 330ml', 'qty' => 2, 'unitPrice' => 25.00, 'total' => 50.00, 'time' => 'today 09:45:00'],
            ['product' => 'Chips (Lays)', 'qty' => 1, 'unitPrice' => 35.00, 'total' => 35.00, 'time' => 'today 09:30:00'],
            ['product' => 'Bottled Water 500ml', 'qty' => 3, 'unitPrice' => 20.00, 'total' => 60.00, 'time' => 'today 09:12:00'],
            ['product' => 'Bread', 'qty' => 1, 'unitPrice' => 40.00, 'total' => 40.00, 'time' => 'today 08:58:00'],
            ['product' => 'Instant Noodles', 'qty' => 2, 'unitPrice' => 35.00, 'total' => 70.00, 'time' => 'today 08:42:00'],
        ];

        foreach ($recentSalesSeed as $idx => $sData) {
            $sale = new Sale();
            $sale->setReceiptNumber('REC-20260929-000' . ($idx + 1))
                ->setUser($admin)
                ->setTotalAmount($sData['total'])
                ->setAmountReceived($sData['total'] + 50.00)
                ->setChangeAmount(50.00)
                ->setCreatedAt(new \DateTime($sData['time']));

            $item = new SaleItem();
            $item->setSale($sale);
            if (isset($productEntities[$sData['product']])) {
                $item->setProduct($productEntities[$sData['product']]);
            }
            $item->setUnitPrice($sData['unitPrice']);
            $item->setQuantity($sData['qty']);
            $item->setSubtotal($sData['total']);
            $sale->addItem($item);

            $this->em->persist($sale);
        }

        // Previous days' sales totaling 8,285.00 so grand total is 8,540.00
        $chartDays = [
            ['-6 days', 1200.00, 'Sinandomeng Rice 5kg', 4, 300.00],
            ['-5 days', 1350.00, 'Corned Beef 150g', 24, 56.25],
            ['-4 days', 1850.00, 'Bath Soap 90g', 37, 50.00],
            ['-3 days', 1185.00, 'Shampoo Sachet 12s', 15, 79.00],
            ['-2 days', 1600.00, 'Cooking Oil 1 Liter', 16, 100.00],
            ['-1 day',  1100.00, 'Ensaymada (Box of 4)', 11, 100.00],
        ];

        foreach ($chartDays as $idx => $cd) {
            $sale = new Sale();
            $sale->setReceiptNumber('REC-2026-PREV-' . ($idx + 1))
                ->setUser($admin)
                ->setTotalAmount($cd[1])
                ->setAmountReceived($cd[1])
                ->setChangeAmount(0)
                ->setCreatedAt(new \DateTime($cd[0] . ' 14:30:00'));

            $item = new SaleItem();
            $item->setSale($sale);
            if (isset($productEntities[$cd[2]])) {
                $item->setProduct($productEntities[$cd[2]]);
            }
            $item->setUnitPrice($cd[4]);
            $item->setQuantity($cd[3]);
            $item->setSubtotal($cd[1]);
            $sale->addItem($item);

            $this->em->persist($sale);
        }

        $this->em->flush();
        $output->writeln('<info>BaiStock database seeded successfully!</info>');

        return Command::SUCCESS;
    }
}
