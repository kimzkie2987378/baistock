<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'delivery_items')]
class DeliveryItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Delivery::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'delivery_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Delivery $delivery = null;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Product $product = null;

    #[ORM\Column(name: 'product_name', length: 150)]
    private string $productName = '';

    #[ORM\Column(type: Types::INTEGER)]
    private int $quantity = 1;

    #[ORM\Column(name: 'unit_cost', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $unitCost = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $subtotal = '0.00';

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id ? (int)$this->id : null;
    }

    public function getDelivery(): ?Delivery
    {
        return $this->delivery;
    }

    public function setDelivery(?Delivery $delivery): static
    {
        $this->delivery = $delivery;
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
            $this->productName = $product->getName();
        }
        return $this;
    }

    public function getProductName(): string
    {
        return $this->productName ?: ($this->product ? $this->product->getName() : 'Unknown Product');
    }

    public function setProductName(string $productName): static
    {
        $this->productName = $productName;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = max(1, $quantity);
        $this->recomputeSubtotal();
        return $this;
    }

    public function getUnitCost(): string
    {
        return $this->unitCost;
    }

    public function setUnitCost(string|float $unitCost): static
    {
        $this->unitCost = number_format((float)$unitCost, 2, '.', '');
        $this->recomputeSubtotal();
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

    private function recomputeSubtotal(): void
    {
        $this->subtotal = number_format((float)$this->unitCost * $this->quantity, 2, '.', '');
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
}
