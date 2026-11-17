<?php

namespace App\Controller;

use App\Entity\Delivery;
use App\Entity\Product;
use App\Entity\Sale;
use App\Entity\Setting;
use App\Entity\StockMovement;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Repository\SaleRepository;
use App\Repository\SettingRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/settings')]
#[IsGranted('ROLE_USER')]
class SettingsController extends AbstractController
{
    #[Route('', name: 'app_settings_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        SettingRepository $settingRepo,
        EntityManagerInterface $em
    ): Response {
        if ($request->isMethod('POST')) {
            // Check authorization: Staff cannot change store settings
            if (!$this->isGranted('ROLE_OWNER') && !$this->isGranted('ROLE_ADMIN')) {
                $this->addFlash('error', 'Access denied: Only Store Owner or Admin can update settings.');
                return $this->redirectToRoute('app_settings_index');
            }

            $fields = [
                'store_name' => $request->request->get('store_name'),
                'store_address' => $request->request->get('store_address'),
                'store_contact' => $request->request->get('store_contact'),
                'currency_symbol' => $request->request->get('currency_symbol', '₱'),
                'default_min_stock' => $request->request->get('default_min_stock', '10'),
                'receipt_footer' => $request->request->get('receipt_footer'),
            ];

            foreach ($fields as $key => $val) {
                if ($val !== null) {
                    $settingRepo->set($key, trim($val));
                }
            }

            $this->addFlash('success', 'Store settings updated successfully.');
            return $this->redirectToRoute('app_settings_index');
        }

        $settings = [
            'store_name' => $settingRepo->get('store_name', 'BaiStock Neighborhood Store'),
            'store_address' => $settingRepo->get('store_address', 'Corner Rizal & Luna St., Cebu City'),
            'store_contact' => $settingRepo->get('store_contact', '+63 917 123 4567'),
            'currency_symbol' => $settingRepo->get('currency_symbol', '₱'),
            'default_min_stock' => $settingRepo->get('default_min_stock', '10'),
            'receipt_footer' => $settingRepo->get('receipt_footer', 'Salamat sa pagpalit! Mabuhay!'),
        ];

        return $this->render('settings/index.html.twig', [
            'settings' => $settings,
        ]);
    }

    #[Route('/update-admin', name: 'app_settings_update_admin', methods: ['POST'])]
    public function updateAdmin(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Access denied: Only the System Administrator can change admin credentials.');
            return $this->redirectToRoute('app_settings_index');
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_settings_index');
        }

        $newUsername = strtolower(trim($request->request->get('admin_username', '')));
        $newEmail = strtolower(trim($request->request->get('admin_email', '')));
        $newPassword = $request->request->get('admin_password', '');
        $confirmPassword = $request->request->get('confirm_password', '');

        if (!$newUsername || !$newEmail) {
            $this->addFlash('error', 'Admin username and email cannot be empty.');
            return $this->redirectToRoute('app_settings_index');
        }

        // Validate uniqueness of username if changed
        $existingByUsername = $userRepository->findOneBy(['username' => $newUsername]);
        if ($existingByUsername && $existingByUsername->getId() !== $currentUser->getId()) {
            $this->addFlash('error', "Username '$newUsername' is already taken by another account.");
            return $this->redirectToRoute('app_settings_index');
        }

        // Validate uniqueness of email if changed
        $existingByEmail = $userRepository->findOneBy(['email' => $newEmail]);
        if ($existingByEmail && $existingByEmail->getId() !== $currentUser->getId()) {
            $this->addFlash('error', "Email '$newEmail' is already taken by another account.");
            return $this->redirectToRoute('app_settings_index');
        }

        $currentUser->setUsername($newUsername);
        $currentUser->setEmail($newEmail);

        if (!empty($newPassword)) {
            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'New passwords do not match. Please try again.');
                return $this->redirectToRoute('app_settings_index');
            }
            if (strlen($newPassword) < 6) {
                $this->addFlash('error', 'Password must be at least 6 characters long.');
                return $this->redirectToRoute('app_settings_index');
            }
            $currentUser->setPassword($hasher->hashPassword($currentUser, $newPassword));
        }

        $em->flush();

        $this->addFlash('success', 'Admin credentials (username/email/password) updated successfully.');
        return $this->redirectToRoute('app_settings_index');
    }

    #[Route('/reset-data', name: 'app_settings_reset_data', methods: ['POST'])]
    public function resetData(
        Request $request,
        KernelInterface $kernel
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Only System Administrator can reset database records.');
            return $this->redirectToRoute('app_settings_index');
        }

        if ($this->isCsrfTokenValid('reset_data', $request->request->get('_token'))) {
            $application = new Application($kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput(['command' => 'app:seed-data']);
            $output = new BufferedOutput();
            $application->run($input, $output);

            $this->addFlash('success', 'Database records reset and re-seeded with standard BaiStock data.');
        }

        return $this->redirectToRoute('app_settings_index');
    }

    #[Route('/backup/export', name: 'app_settings_backup_export', methods: ['GET'])]
    public function backupExport(
        ProductRepository $productRepo,
        SaleRepository $saleRepo
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_OWNER')) {
            $this->addFlash('error', 'Access denied.');
            return $this->redirectToRoute('app_settings_index');
        }

        $data = [
            'export_date' => date('c'),
            'system' => 'BaiStock Storage Management System',
            'version' => '1.0.0',
            'products' => [],
            'sales' => [],
        ];

        foreach ($productRepo->findAll() as $p) {
            $data['products'][] = [
                'sku' => $p->getSku(),
                'name' => $p->getName(),
                'category' => $p->getCategory(),
                'price' => $p->getPrice(),
                'quantity' => $p->getQuantity(),
                'minStock' => $p->getMinStock(),
                'description' => $p->getDescription(),
            ];
        }

        foreach ($saleRepo->findAll() as $s) {
            $items = [];
            foreach ($s->getItems() as $item) {
                $items[] = [
                    'product' => $item->getProductName(),
                    'qty' => $item->getQuantity(),
                    'unitPrice' => $item->getUnitPrice(),
                    'totalPrice' => $item->getTotalPrice(),
                ];
            }
            $data['sales'][] = [
                'receipt' => $s->getReceiptNumber(),
                'date' => $s->getCreatedAt()->format('c'),
                'total' => $s->getTotalAmount(),
                'cashier' => $s->getCashierName(),
                'items' => $items,
            ];
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return new Response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="baistock_backup_' . date('Ymd_His') . '.json"',
        ]);
    }
}
