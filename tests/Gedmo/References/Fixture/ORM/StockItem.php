<?php

namespace Gedmo\References\Fixture\ORM;

/**
 * @Entity
 */
use Gedmo\References\Fixture\ODM\MongoDB\Product;

class StockItem
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Column
     */
    private $name;

    /**
     * @Column
     */
    private $sku;

    /**
     * @Column(type="integer")
     */
    private $quantity;

    /**
     * @gedmo:ReferenceOne(type="document", class="Gedmo\References\Fixture\ODM\MongoDB\Product", inversedBy="stockItems", identifier="productId")
     */
    private $product;

    /**
     * @Column(type="string")
     */
    private $productId;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getSku()
    {
        return $this->sku;
    }

    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    public function setProduct(Product $product)
    {
        $this->product = $product;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function getProductId()
    {
        return $this->productId;
    }
}
