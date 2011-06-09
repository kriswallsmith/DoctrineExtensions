<?php

namespace Gedmo\References\Fixture\ODM\MongoDB;

use Doctrine\Common\Collections\Collection;

/**
 * @Document
 */
class Product
{
    /**
     * @Id
     */
    private $id;

    /**
     * @String
     */
    private $name;

    /**
     * @gedmo:ReferenceMany(type="entity", class="Gedmo\References\Fixtures\ORM\StockItem", mappedBy="product")
     */
    private $stockItems;

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

    public function getStockItems()
    {
        return $this->stockItems;
    }

    public function setStockItems(Collection $stockItems)
    {
        $this->stockItems = $stockItems;
    }
}
