<?php

namespace Gedmo\References\Mapping\Event\Adapter;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Proxy\Proxy as MongoDBProxy;
use Doctrine\ORM\EntityManager;
use Gedmo\Mapping\Event\Adapter\ORM as BaseAdapterORM;
use Gedmo\References\Mapping\Event\ReferencesAdapter;

/**
 * Doctrine event adapter for ORM adapted
 * for references behavior
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 * @package Gedmo\References\Mapping\Event\Adapter
 * @subpackage ORM
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
final class ORM extends BaseAdapterORM implements ReferencesAdapter
{
    public function getIdentifier(ObjectManager $om, $object, $single = true)
    {
        if ($om instanceof EntityManager) {
            return $this->extractIdentifier($om, $object, $single);
        }
        if ($om instanceof DocumentManager) {
            $meta = $om->getClassMetadata(get_class($object));
            if ($object instanceof MongoDBProxy) {
                $id = $om->getUnitOfWork()->getDocumentIdentifier($object);
            } else {
                $id = $meta->getReflectionProperty($meta->identifier)->getValue($object);
            }

            if ($single || !$id) {
                return $id;
            } else {
                return array($meta->identifier => $id);
            }
        }
    }

    public function getSingleReference(ObjectManager $om, $class, $identifier)
    {
        $this->throwIfNotDocumentManager($om);
        return $om->getReference($class, $identifier);
    }

    private function throwIfNotDocumentManager(DocumentManager $dm)
    {
    }
}