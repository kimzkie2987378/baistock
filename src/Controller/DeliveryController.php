<?php

namespace App\Controller;

use App\Entity\Delivery;
use App\Entity\DeliveryItem;
use App\Entity\StockMovement;
use App\Repository\DeliveryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/delivery')]
#[IsGranted('ROLE_USER')]
class DeliveryController extends AbstractController
{
    #[Route('', name: 'app_delivery_index', methods: ['GET'])]
    public function index(DeliveryRepository $deliveryRepository): Response
    {
        $deliveries = $deliveryRepository->findBy([], ['id' => 'DESC']);

        return $this->render('delivery/index.html.twig', [
            'deliveries' => $deliveries,
        ]);
    }

    #[Route('/new', name: 'app_delivery_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        ProductRepository $productRepository
    ): Response {
        if ($request->isMethod('POST')) {
            $supplier = trim($request->request->get('supplier', ''));
            $tracking = strtoupper(trim($request->request->get('trackingNumber', '')));
            $status = $request->request->get('status', 'Pending');
            $cost = (float) $request->request->get('totalCost', 0);
            $notes = trim($request->request->get('notes', ''));

            if (!$supplier) {
                $this->addFlash('error', 'Supplier name is required.');
                return $this->redirectToRoute('app_delivery_new');
            }

            $delivery = new Delivery();
            if ($tracking) {
                $delivery->setTrackingNumber($tracking);
            }
            $delivery->setSupplier($supplier)
                ->setStatus($status)
                ->setTotalCost($cost)
                ->setNotes($notes);

            // Process Items from form (product_id, quantity, unit_cost)
            $itemProductIds = $request->request->all('item_product_id');
            $itemQuantities = $request->request->all('item_quantity');
            $itemCosts = $request->request->all('item_cost');

            $isDeliveredStatus = in_array(strtolower($status), ['delivered', 'received'], true);
            $stockAddedCount = 0;

            if (is_array($itemProductIds)) {
                $calculatedTotalCost = 0.0;
                foreach ($itemProductIds as $index => $prodId) {
                    $prodId = (int) $prodId;
                    $qty = isset($itemQuantities[$index]) ? max(1, (int)$itemQuantities[$index]) : 1;
                    $unitCost = isset($itemCosts[$index]) ? (float)$itemCosts[$index] : 0.0;
                    $subtotal = $qty * $unitCost;
                    $calculatedTotalCost += $subtotal;

                    $product = $prodId > 0 ? $productRepository->find($prodId) : null;
                    if ($product) {
                        $item = new DeliveryItem();
                        $item->setDelivery($delivery)
                            ->setProduct($product)
                            ->setProductName($product->getName())
                            ->setQuantity($qty)
                            ->setUnitCost($unitCost)
                            ->setSubtotal($subtotal);
                        $em->persist($item);
                        $delivery->addItem($item);

                        // If status is already Delivered/Received, add to inventory immediately!
                        if ($isDeliveredStatus) {
                            $product->setQuantity($product->getQuantity() + $qty);
                            
                            $sm = new StockMovement();
                            $sm->setProduct($product)
                                ->setType('IN')
                                ->setQuantity($qty)
                                ->setRemarks("Delivery #{$delivery->getTrackingNumber()} from {$delivery->getSupplier()}")
                                ->setUser($this->getUser());
                            $em->persist($sm);
                            $stockAddedCount += $qty;
                        }
                    }
                }

                // If user didn't enter custom cost, use calculated items total
                if ($cost <= 0 && $calculatedTotalCost > 0) {
                    $delivery->setTotalCost($calculatedTotalCost);
                }
            }

            if ($isDeliveredStatus) {
                $delivery->setIsReceived(true);
                $delivery->setReceivedDate(new \DateTime());
            }

            $em->persist($delivery);
            $em->flush();

            if ($isDeliveredStatus && $stockAddedCount > 0) {
                $this->addFlash('success', "Delivery order #{$delivery->getTrackingNumber()} created and marked as Delivered! {$stockAddedCount} units added to inventory.");
            } else {
                $this->addFlash('success', "Delivery order #{$delivery->getTrackingNumber()} created successfully.");
            }

            return $this->redirectToRoute('app_delivery_index');
        }

        // Available products for item selection (without pictures)
        $products = $productRepository->findBy([], ['productName' => 'ASC']);

        return $this->render('delivery/new.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/{id}/receive', name: 'app_delivery_receive', methods: ['POST'])]
    public function receive(
        Delivery $delivery,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('receive_delivery_' . $delivery->getId(), $request->request->get('_token'))) {
            if ($delivery->isReceived()) {
                $this->addFlash('warning', "Delivery #{$delivery->getTrackingNumber()} has already been received into inventory.");
                return $this->redirectToRoute('app_delivery_index');
            }

            $addedUnits = 0;
            foreach ($delivery->getItems() as $item) {
                $product = $item->getProduct();
                if ($product) {
                    $qty = $item->getQuantity();
                    $product->setQuantity($product->getQuantity() + $qty);

                    $sm = new StockMovement();
                    $sm->setProduct($product)
                        ->setType('IN')
                        ->setQuantity($qty)
                        ->setRemarks("Delivery #{$delivery->getTrackingNumber()} from {$delivery->getSupplier()}")
                        ->setUser($this->getUser());
                    $em->persist($sm);

                    $addedUnits += $qty;
                }
            }

            $delivery->setStatus('Delivered');
            $delivery->setIsReceived(true);
            $delivery->setReceivedDate(new \DateTime());

            $em->flush();

            if ($addedUnits > 0) {
                $this->addFlash('success', "Delivery #{$delivery->getTrackingNumber()} marked as Received! {$addedUnits} product units have been automatically added to inventory.");
            } else {
                $this->addFlash('success', "Delivery #{$delivery->getTrackingNumber()} marked as Received.");
            }
        }

        return $this->redirectToRoute('app_delivery_index');
    }
}
