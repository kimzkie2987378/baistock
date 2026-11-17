<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\StockMovement;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/products')]
#[IsGranted('ROLE_USER')]
class ProductController extends AbstractController
{
    #[Route('', name: 'app_product_index', methods: ['GET'])]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $search = $request->query->get('q');
        $category = $request->query->get('category');
        $stock = $request->query->get('stock'); // all, low, out, in

        $products = $productRepository->searchAndFilter($search, $category, $stock);
        $categories = $productRepository->getCategories();

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'current_category' => $category,
            'current_stock' => $stock,
            'search_query' => $search,
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        ProductRepository $repo,
        CategoryRepository $catRepo
    ): Response {
        if ($request->isMethod('POST')) {
            $name = trim($request->request->get('name', ''));
            $categoryName = trim($request->request->get('category', 'Groceries'));
            $price = (float) $request->request->get('price', 0);
            $quantity = (int) $request->request->get('quantity', 0);
            $minStock = (int) $request->request->get('minStock', 10);
            $description = trim($request->request->get('description', ''));

            if (!$name) {
                $this->addFlash('error', 'Product name is required.');
            } elseif ($price < 0) {
                $this->addFlash('error', 'Price cannot be negative.');
            } else {
                $category = $catRepo->findOneBy(['categoryName' => $categoryName]);
                if (!$category) {
                    $category = new Category();
                    $category->setCategoryName($categoryName);
                    $em->persist($category);
                }

                $product = new Product();
                $product->setProductName($name)
                    ->setCategory($category)
                    ->setPrice($price)
                    ->setQuantity($quantity)
                    ->setMinimumStock($minStock)
                    ->setDescription($description)
                    ->setCreatedAt(new \DateTime());

                // Handle product image upload
                $imageFile = $request->files->get('product_image');
                if ($imageFile) {
                    $ext = strtolower($imageFile->guessExtension() ?: $imageFile->getClientOriginalExtension() ?: 'jpg');
                    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                    if (in_array($ext, $allowed)) {
                        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $ext;
                        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/products';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $imageFile->move($uploadDir, $newFilename);
                        $product->setImagePath($newFilename);
                    }
                }

                $em->persist($product);

                if ($quantity > 0) {
                    $sm = new StockMovement();
                    $sm->setProduct($product)
                        ->setType('IN')
                        ->setQuantity($quantity)
                        ->setRemarks('Initial Stock')
                        ->setUser($this->getUser());
                    $em->persist($sm);
                }

                $em->flush();

                $this->addFlash('success', "Product '$name' added successfully!");
                return $this->redirectToRoute('app_product_index');
            }
        }

        return $this->render('product/new.html.twig', [
            'categories' => $repo->getCategories(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(
        Product $product,
        Request $request,
        EntityManagerInterface $em,
        ProductRepository $repo,
        CategoryRepository $catRepo
    ): Response {
        if ($request->isMethod('POST')) {
            $name = trim($request->request->get('name', ''));
            $categoryName = trim($request->request->get('category', ''));
            $price = (float) $request->request->get('price', 0);
            $minStock = (int) $request->request->get('minStock', 10);
            $description = trim($request->request->get('description', ''));

            if (!$name) {
                $this->addFlash('error', 'Product name is required.');
            } elseif ($price < 0) {
                $this->addFlash('error', 'Price cannot be negative.');
            } else {
                $category = $catRepo->findOneBy(['categoryName' => $categoryName]);
                if (!$category && $categoryName) {
                    $category = new Category();
                    $category->setCategoryName($categoryName);
                    $em->persist($category);
                }

                $product->setProductName($name)
                    ->setCategory($category)
                    ->setPrice($price)
                    ->setMinimumStock($minStock)
                    ->setDescription($description)
                    ->setUpdatedAt(new \DateTime());

                // Handle product image upload in edit
                $imageFile = $request->files->get('product_image');
                if ($imageFile) {
                    $ext = strtolower($imageFile->guessExtension() ?: $imageFile->getClientOriginalExtension() ?: 'jpg');
                    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                    if (in_array($ext, $allowed)) {
                        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $ext;
                        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/products';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $imageFile->move($uploadDir, $newFilename);
                        $product->setImagePath($newFilename);
                    }
                } elseif ($request->request->get('remove_image') === '1') {
                    $product->setImagePath(null);
                }

                $em->flush();

                $this->addFlash('success', "Product '{$product->getName()}' updated successfully!");
                return $this->redirectToRoute('app_product_index');
            }
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'categories' => $repo->getCategories(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        // Enforce SRS Access Control: Staff accounts cannot perform owner-only actions like deleting records
        if (!$this->isGranted('ROLE_OWNER') && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Access denied: Store staff accounts cannot delete products. Only Owner or Admin can perform this action.');
            return $this->redirectToRoute('app_product_index');
        }

        if ($this->isCsrfTokenValid('delete_product_' . $product->getId(), $request->request->get('_token'))) {
            $name = $product->getName();
            $em->remove($product);
            $em->flush();
            $this->addFlash('success', "Product '$name' deleted successfully.");
        }

        return $this->redirectToRoute('app_product_index');
    }

    #[Route('/export/csv', name: 'app_product_export_csv', methods: ['GET'])]
    public function exportCsv(ProductRepository $repo): Response
    {
        $products = $repo->findBy([], ['productName' => 'ASC']);
        
        $handle = fopen('php://memory', 'r+');
        fputcsv($handle, ['ID', 'SKU', 'Name', 'Category', 'Price (PHP)', 'Stock Quantity', 'Min Stock', 'Status']);

        foreach ($products as $p) {
            fputcsv($handle, [
                $p->getId(),
                $p->getSku(),
                $p->getName(),
                $p->getCategoryName(),
                $p->getPrice(),
                $p->getQuantity(),
                $p->getMinStock(),
                $p->getStockStatusLabel(),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return new Response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="baistock_inventory_' . date('Y-m-d') . '.csv"',
        ]);
    }
}
