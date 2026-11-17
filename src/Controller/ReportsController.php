<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\SaleItemRepository;
use App\Repository\SaleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reports')]
#[IsGranted('ROLE_USER')]
class ReportsController extends AbstractController
{
    #[Route('', name: 'app_reports_index', methods: ['GET'])]
    public function index(
        ProductRepository $productRepo,
        SaleRepository $saleRepo,
        SaleItemRepository $saleItemRepo
    ): Response {
        $totalProducts = $productRepo->count([]);
        $totalUnits = $productRepo->getTotalStockUnits();
        $lowStockCount = $productRepo->countLowStock();
        $totalRevenue = $saleRepo->getTotalSalesAmount();

        // Calculate Total Inventory Valuation
        $allProducts = $productRepo->findAll();
        $inventoryValuation = 0.0;
        foreach ($allProducts as $p) {
            $inventoryValuation += ((float)$p->getPrice()) * $p->getQuantity();
        }

        $recentSales = $saleRepo->findRecentSales(10);
        $topSelling = $saleItemRepo->getTopSellingProducts(5);
        $lowStockProducts = $productRepo->findLowStock(10);
        $dailyHistory = $saleRepo->getDailySalesHistory(7);

        return $this->render('reports/index.html.twig', [
            'total_products' => $totalProducts,
            'total_units' => $totalUnits,
            'low_stock_count' => $lowStockCount,
            'total_revenue' => $totalRevenue,
            'inventory_valuation' => $inventoryValuation,
            'recent_sales' => $recentSales,
            'top_selling' => $topSelling,
            'low_stock_products' => $lowStockProducts,
            'daily_history' => $dailyHistory,
        ]);
    }
}
