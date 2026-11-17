<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Sale;
use App\Repository\ProductRepository;
use App\Repository\SaleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(
        ProductRepository $productRepository,
        SaleRepository $saleRepository
    ): Response {
        $totalProducts = $productRepository->count([]);
        $totalStockUnits = $productRepository->getTotalStockUnits();
        $lowStockCount = $productRepository->countLowStock();
        $totalSales = $saleRepository->getTotalSalesAmount();
        $todaySales = $saleRepository->getTodaySalesAmount();
        $thisWeekSales = $saleRepository->getThisWeekSalesAmount();

        $lowStockProducts = $productRepository->findLowStock(5);
        $recentSales = $saleRepository->findRecentSales(5);

        // Current real dynamic datasets for Sales Overview chart
        $dailyHistory = $saleRepository->getDailySalesHistory(7);
        $weeklyHistory = $saleRepository->getWeeklySalesHistory(4);
        $monthlyHistory = $saleRepository->getMonthlySalesHistory(6);

        $dailyLabels = array_column($dailyHistory, 'label');
        $dailyData = array_column($dailyHistory, 'total');

        $weeklyLabels = array_column($weeklyHistory, 'label');
        $weeklyData = array_column($weeklyHistory, 'total');

        $monthlyLabels = array_column($monthlyHistory, 'label');
        $monthlyData = array_column($monthlyHistory, 'total');

        return $this->render('dashboard/index.html.twig', [
            'total_products' => $totalProducts,
            'total_stock_units' => $totalStockUnits,
            'low_stock_count' => $lowStockCount,
            'total_sales' => $totalSales,
            'today_sales' => $todaySales,
            'this_week_sales' => $thisWeekSales,
            'low_stock_products' => $lowStockProducts,
            'recent_sales' => $recentSales,
            'chart_data' => [
                'daily' => [
                    'labels' => $dailyLabels,
                    'data' => $dailyData,
                ],
                'weekly' => [
                    'labels' => $weeklyLabels,
                    'data' => $weeklyData,
                ],
                'monthly' => [
                    'labels' => $monthlyLabels,
                    'data' => $monthlyData,
                ],
            ],
        ]);
    }
}
