<?php

namespace App\Controller;

use App\Entity\Sale;
use App\Repository\SaleRepository;
use App\Repository\SettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/receipts')]
#[IsGranted('ROLE_USER')]
class ReceiptsController extends AbstractController
{
    #[Route('', name: 'app_receipts_index', methods: ['GET'])]
    public function index(SaleRepository $saleRepository): Response
    {
        $sales = $saleRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('receipts/index.html.twig', [
            'sales' => $sales,
        ]);
    }

    #[Route('/{id}', name: 'app_receipts_show', methods: ['GET'])]
    public function show(Sale $sale, SettingRepository $settingRepository): Response
    {
        $storeName = $settingRepository->get('store_name', 'BaiStock Neighborhood Store');
        $storeAddress = $settingRepository->get('store_address', 'Corner Rizal & Luna St., Cebu City');
        $storeContact = $settingRepository->get('store_contact', '+63 917 123 4567');
        $footerNote = $settingRepository->get('receipt_footer', 'Salamat sa pagpalit! Mabuhay!');

        return $this->render('receipts/show.html.twig', [
            'sale' => $sale,
            'store_name' => $storeName,
            'store_address' => $storeAddress,
            'store_contact' => $storeContact,
            'footer_note' => $footerNote,
        ]);
    }
}
