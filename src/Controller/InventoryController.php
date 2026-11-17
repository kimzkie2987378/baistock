<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\StockMovement;
use App\Repository\ProductRepository;
use App\Repository\StockMovementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/inventory')]
#[IsGranted('ROLE_USER')]
class InventoryController extends AbstractController
{
    #[Route('', name: 'app_inventory_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository, StockMovementRepository $movementRepository): Response
    {
        $products = $productRepository->findBy([], ['quantity' => 'ASC']);
        $totalUnits = $productRepository->getTotalStockUnits();
        $lowStockCount = $productRepository->countLowStock();
        $outOfStockCount = count(array_filter($products, fn($p) => $p->isOutOfStock()));
        $recentMovements = $movementRepository->findRecent(10);

        return $this->render('inventory/index.html.twig', [
            'products' => $products,
            'total_units' => $totalUnits,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'recent_movements' => $recentMovements,
        ]);
    }

    #[Route('/stock-in', name: 'app_inventory_stock_in', methods: ['GET', 'POST'])]
    public function stockIn(Request $request, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $productId = (int) $request->request->get('product_id');
            $quantity = (int) $request->request->get('quantity', 0);
            $reference = trim($request->request->get('reference', 'Direct Stock-In'));

            $product = $productRepository->find($productId);
            if (!$product) {
                $this->addFlash('error', 'Selected product was not found.');
            } elseif ($quantity <= 0) {
                $this->addFlash('error', 'Quantity must be greater than zero.');
            } else {
                $prevQty = $product->getQuantity();
                $newQty = $prevQty + $quantity;
                $product->setQuantity($newQty);

                $movement = new StockMovement();
                $movement->setProduct($product)
                    ->setMovementType('stock_in')
                    ->setQuantity($quantity)
                    ->setRemarks($reference)
                    ->setUser($this->getUser());

                $em->persist($movement);
                $em->flush();

                $this->addFlash('success', "Stock-In successful! Added $quantity units to {$product->getName()} (New stock: $newQty).");
                return $this->redirectToRoute('app_inventory_index');
            }
        }

        $preSelectedId = $request->query->get('product_id');
        $products = $productRepository->findBy([], ['productName' => 'ASC']);

        return $this->render('inventory/stock_in.html.twig', [
            'products' => $products,
            'selected_product_id' => $preSelectedId,
        ]);
    }

    #[Route('/adjust/{id}', name: 'app_inventory_adjust', methods: ['GET', 'POST'])]
    public function adjust(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $newQuantity = (int) $request->request->get('new_quantity', 0);
            $reason = trim($request->request->get('reason', 'Physical count adjustment'));

            if ($newQuantity < 0) {
                $this->addFlash('error', 'Quantity cannot be negative.');
            } else {
                $prevQty = $product->getQuantity();
                $diff = $newQuantity - $prevQty;
                $product->setQuantity($newQuantity);

                $movement = new StockMovement();
                $movement->setProduct($product)
                    ->setMovementType('adjustment')
                    ->setQuantity(abs($diff))
                    ->setRemarks("Adjustment: $reason (" . ($diff >= 0 ? "+$diff" : "$diff") . ")")
                    ->setUser($this->getUser());

                $em->persist($movement);
                $em->flush();

                $this->addFlash('success', "Stock adjusted for {$product->getName()}. Updated from $prevQty to $newQuantity.");
                return $this->redirectToRoute('app_inventory_index');
            }
        }

        return $this->render('inventory/adjust.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/history', name: 'app_inventory_history', methods: ['GET'])]
    public function history(StockMovementRepository $repo): Response
    {
        $movements = $repo->findBy([], ['createdAt' => 'DESC'], 50);

        return $this->render('inventory/history.html.twig', [
            'movements' => $movements,
        ]);
    }
}
