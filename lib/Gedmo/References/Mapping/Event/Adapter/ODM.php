<?php

namespace Gedmo\References\Mapping\Event\Adapter;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Proxy\Proxy as ORMProxy;
use Gedmo\Exception\InvalidArgumentException;
use Gedmo\Mapping\Event\Adapter\ODM as BaseAdapterODM;
use Gedmo\References\Mapping\Event\ReferencesAdapter;

/**
 * Doctrine event adapter for ODM adapted
 * for references behavior
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 * @package Gedmo\References\Mapping\Event\Adapter
 * @subpackage ODM
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
final class ODM extends BaseAdapterODM implements ReferencesAdapter
{
    public function getIdentifier($om, $object, $single = true)
    {
        if ($om instanceof DocumentManager) {
            return $this->extractIdentifier($om, $object, $single);
        }
        if ($om instanceof EntityManager) {
            if ($object instanceof ORMProxy) {
                $id = $om->getUnitOfWork()->getEntityIdentifier($object);
            } else {
                $meta = $om->getClassMetadata(get_class($object));
                $id = array();
                foreach ($meta->identifier as $name) {
                    $id[$name] = $meta->getReflectionProperty($name)->getValue($object);
                    // return null if one of identifiers is missing
                    if (!$id[$name]) {
                        return null;
                    }
                }
            }

            if ($single) {
                $id = current($id);
            }
            return $id;
        }
    }

    public function getSingleReference($om, $class, $identifier)
    {
        $this->throwIfNotEntityManager($om);
        return $om->getReference($class, $identifier);
    }

    private function throwIfNotEntityManager(EntityManager $em)
    {
    }
}