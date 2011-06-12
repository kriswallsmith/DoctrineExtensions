<?php

namespace Gedmo\References;

use Doctrine\Common\EventArgs;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata as ORMClassMetadata;
use Doctrine\ORM\EntityManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as MongoDBClassMetadata;
use Gedmo\Exception\InvalidArgumentException;
use Gedmo\Mapping\MappedEventSubscriber;

class ReferencesListener extends MappedEventSubscriber
{
    private $managers;

    public function __construct(array $managers = array()) {
        $this->managers = $managers;
    }

    public function loadClassMetadata(EventArgs $eventArgs)
    {
        $ea = $this->getEventAdapter($eventArgs);
        $this->loadMetadataForObjectClass($ea->getObjectManager(), $eventArgs->getClassMetadata());
    }

    public function postLoad(EventArgs $eventArgs)
    {

    }

    public function prePersist(EventArgs $eventArgs)
    {
        $ea = $this->getEventAdapter($eventArgs);
        $om = $ea->getObjectManager();
        $uow = $om->getUnitOfWork();
        $object = $ea->getObject();
        $meta = $om->getClassMetadata(get_class($object));
        $config = $this->getConfiguration($om, $meta->name);
        foreach ($config['referenceOne'] as $mapping) {
            if (isset($mapping['identifier'])) {
                $property = $meta->reflClass->getProperty($mapping['field']);
                $property->setAccessible(true);
                $referencedObject = $property->getValue($object);
                if (is_object($referencedObject)) {
                    $meta->setFieldValue(
                        $object,
                        $mapping['identifier'],
                        $this->extractIdentifier(
                            $this->managers[$mapping['type']],
                            $referencedObject
                        )
                    );
                }
            }
        }
    }

    public function preUpdate(EventArgs $eventArgs)
    {

    }

    public function getSubscribedEvents()
    {
        return array(
            'postLoad',
            'loadClassMetadata',
            'prePersist',
            'preUpdate',
        );
    }

    public function registerManager($type, ObjectManager $manager)
    {
        $this->managers[$type] = $manager;
    }

    protected function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * TODO: put this method into appropriate driver
     *
     * Extracts identifiers from object or proxy
     *
     * @param DocumentManager $dm
     * @param object $object
     * @param bool $single
     * @return mixed - array or single identifier
     */
    private function extractIdentifier(ObjectManager $om, $object, $single = true)
    {
        if ($om instanceof DocumentManager) {
            $meta = $om->getClassMetadata(get_class($object));
            if ($object instanceof Proxy) {
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
        if ($om instanceof EntityManager) {
            if ($object instanceof Proxy) {
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
}
