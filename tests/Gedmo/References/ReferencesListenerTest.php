<?php

namespace Gedmo\References;


require_once __DIR__.'/Fixture/ODM/MongoDB/Product.php';
require_once __DIR__.'/Fixture/ORM/StockItem.php';

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver as MongoDBAnnotationDriver;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver as ORMAnnotationDriver;
use Gedmo\References\Fixture\ODM\MongoDB\Product;
use Gedmo\References\Fixture\ORM\StockItem;
use Tool\BaseTestCaseOM;

class ReferencesListenerTest extends BaseTestCaseOM
{
    private $em;
    private $dm;

    protected function setUp()
    {
        parent::setUp();

        $reader = new AnnotationReader();
        $reader->setDefaultAnnotationNamespace('Doctrine\ODM\MongoDB\Mapping\\');

        $this->dm = $this->getMockDocumentManager('test', new MongoDBAnnotationDriver($reader, __DIR__ . '/Fixture/ODM/MongoDB'));

        $listener = new ReferencesListener(array(
            'entity' => $this->em,
            'document' => $this->dm
        ));

        $this->evm->addEventSubscriber($listener);

        $reader = new AnnotationReader();
        $reader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');

        $this->em = $this->getMockSqliteEntityManager(array('Gedmo\References\Fixture\ORM\StockItem'), new ORMAnnotationDriver($reader, __DIR__ . '/Fixture/ORM'));
    }

    public function testShouldPersistReferencedIdentifiersIntoIdentifierField()
    {
        $stockItem = new StockItem();
        $stockItem->setName('Apple TV');
        $stockItem->setSku('APP-TV');
        $stockItem->setQuantity(25);

        $product = new Product();
        $product->setName('Apple TV');

        $this->dm->persist($product);
        $this->dm->flush();

        $stockItem->setProduct($product);

        $this->em->persist($stockItem);

        $this->assertEquals($product->getId(), $stockItem->getProductId());
    }
}