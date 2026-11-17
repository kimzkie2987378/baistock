<?php

namespace App\Entity;

use App\Repository\SaleItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SaleItemRepository::class)]
#[ORM\Table(name: 'sale_items')]
class SaleItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Sale::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'sale_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Sale $sale = null;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Product $product = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $quantity = 1;

    #[ORM\Column(name: 'unit_price', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $unitPrice = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $subtotal = '0.00';

    public function getId(): ?int
    {
        return $this->id ? (int)$this->id : null;
    }

    public function getSale(): ?Sale
    {
        return $this->sale;
    }

    public function setSale(?Sale $sale): static
    {
        $this->sale = $sale;
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;
        if ($product) {
            $this->unitPrice = $product->getPrice();
            $this->calculateTotal();
        }
        return $this;
    }

    public function getProductName(): string
    {
        return $this->product ? $this->product->getName() : 'General Item';
    }

    public function setProductName(string $name): static
    {
        // Compatibility
        return $this;
    }

    public function getUnitPrice(): string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string|float $unitPrice): static
    {
        $this->unitPrice = number_format((float)$unitPrice, 2, '.', '');
        $this->calculateTotal();
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = max(1, $quantity);
        $this->calculateTotal();
        return $this;
    }

    public function getSubtotal(): string
    {
        return $this->subtotal;
    }

    public function setSubtotal(string|float $subtotal): static
    {
        $this->subtotal = number_format((float)$subtotal, 2, '.', '');
        return $this;
    }

    public function getTotalPrice(): string
    {
        return $this->subtotal;
    }

    public function setTotalPrice(string|float $totalPrice): static
    {
        return $this->setSubtotal($totalPrice);
    }

    private function calculateTotal(): void
    {
        $total = ((float)$this->unitPrice) * $this->quantity;
        $this->subtotal = number_format($total, 2, '.', '');
    }
}
