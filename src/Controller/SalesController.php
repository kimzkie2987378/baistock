<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Sale;
use App\Entity\SaleItem;
use App\Entity\StockMovement;
use App\Repository\ProductRepository;
use App\Repository\SaleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/sales')]
#[IsGranted('ROLE_USER')]
class SalesController extends AbstractController
{
    #[Route('', name: 'app_sales_index', methods: ['GET'])]
    public function index(Request $request, SaleRepository $saleRepository): Response
    {
        $sales = $saleRepository->findBy([], ['createdAt' => 'DESC'], 50);
        $totalSales = $saleRepository->getTotalSalesAmount();

        return $this->render('sales/index.html.twig', [
            'sales' => $sales,
            'total_sales' => $totalSales,
        ]);
    }

    #[Route('/record', name: 'app_sales_record', methods: ['GET', 'POST'])]
    public function record(Request $request, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $itemsJson = $request->request->get('items_json', '[]');
            $paymentMethod = $request->request->get('payment_method', 'Cash');
            $amountPaid = (float) $request->request->get('amount_paid', 0);

            $cartItems = json_decode($itemsJson, true) ?: [];

            if (empty($cartItems)) {
                $this->addFlash('error', 'Cart is empty. Please add at least one product.');
                return $this->redirectToRoute('app_sales_record');
            }

            $sale = new Sale();
            $sale->setUser($this->getUser());

            $grandTotal = 0.0;
            $errors = [];

            // Validate inventory before deducting
            foreach ($cartItems as $itemData) {
                $product = $productRepository->find((int)$itemData['id']);
                $qty = (int)$itemData['qty'];

                if (!$product) {
                    $errors[] = "Product not found: " . ($itemData['name'] ?? 'ID ' . $itemData['id']);
                    continue;
                }

                if ($product->getQuantity() < $qty) {
                    $errors[] = "Insufficient stock for '{$product->getName()}'. Available: {$product->getQuantity()}, requested: $qty.";
                    continue;
                }
            }

            if (!empty($errors)) {
                $this->addFlash('error', implode('<br>', $errors));
                return $this->redirectToRoute('app_sales_record');
            }

            // Process deduction and create sale items
            foreach ($cartItems as $itemData) {
                $product = $productRepository->find((int)$itemData['id']);
                $qty = (int)$itemData['qty'];
                $price = (float)$product->getPrice();
                $lineTotal = $price * $qty;
                $grandTotal += $lineTotal;

                // Create Sale Item
                $saleItem = new SaleItem();
                $saleItem->setSale($sale)
                    ->setProduct($product)
                    ->setUnitPrice($price)
                    ->setQuantity($qty)
                    ->setSubtotal($lineTotal);
                $sale->addItem($saleItem);

                // Deduct stock from inventory
                $prevStock = $product->getQuantity();
                $newStock = $prevStock - $qty;
                $product->setQuantity($newStock);

                // Audit trail
                $movement = new StockMovement();
                $movement->setProduct($product)
                    ->setMovementType('sale')
                    ->setQuantity($qty)
                    ->setRemarks("Sale #" . $sale->getReceiptNumber())
                    ->setSale($sale)
                    ->setUser($this->getUser());
                $em->persist($movement);
            }

            $sale->setTotalAmount($grandTotal);
            
            if ($amountPaid < $grandTotal) {
                $amountPaid = $grandTotal;
            }
            $sale->setAmountReceived($amountPaid);
            $sale->setChangeAmount($amountPaid - $grandTotal);

            $em->persist($sale);
            $em->flush();

            $this->addFlash('success', "Sale completed successfully! Receipt #" . $sale->getReceiptNumber());
            return $this->redirectToRoute('app_receipts_show', ['id' => $sale->getId()]);
        }

        $products = $productRepository->findBy([], ['productName' => 'ASC']);
        $categories = $productRepository->getCategories();

        return $this->render('sales/record.html.twig', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
