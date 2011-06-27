<?php

namespace Gedmo\References;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\Common\EventArgs;
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
        $ea = $this->getEventAdapter($eventArgs);
        $om = $ea->getObjectManager();
        $object = $ea->getObject();
        $meta = $om->getClassMetadata(get_class($object));
        $config = $this->getConfiguration($om, $meta->name);
        foreach ($config['referenceOne'] as $mapping) {
            $property = $meta->reflClass->getProperty($mapping['field']);
            $property->setAccessible(true);
            if (isset($mapping['identifier'])) {
                $referencedObjectId = $meta->getFieldValue($object, $mapping['identifier']);
                if (null !== $referencedObjectId) {
                    $property->setValue(
                        $object,
                        $ea->getSingleReference(
                            $this->getManager($mapping['type']),
                            $mapping['class'],
                            $referencedObjectId
                        )
                    );
                }
            }
        }
        foreach ($config['referenceMany'] as $mapping) {
            $property = $meta->reflClass->getProperty($mapping['field']);
            $property->setAccessible(true);
            if (isset($mapping['mappedBy'])) {
                $id         = $ea->extractIdentifier($om, $object);
                $manager    = $this->getManager($mapping['type']);
                $class      = $mapping['class'];
                $refMeta    = $manager->getClassMetadata($class);
                $refConfig  = $this->getConfiguration($manager, $refMeta->name);
                if (isset($refConfig['referenceOne'][$mapping['mappedBy']])) {
                    $refMapping = $refConfig['referenceOne'][$mapping['mappedBy']];
                    $identifier = $refMapping['identifier'];
                    $property->setValue(
                        $object,
                        new LazyCollection(
                            function() use ($id, &$manager, $class, $identifier)
                            {
                                $results = $manager
                                    ->getRepository($class)
                                    ->findBy(array(
                                        $identifier => $id,
                                    ));
                                return new ArrayCollection((is_array($results) ? $results : $results->toArray()));
                            }
                        )
                    );
                }
            }
        }
    }

    public function prePersist(EventArgs $eventArgs)
    {
        $this->updateReferences($eventArgs);

    }

    public function preUpdate(EventArgs $eventArgs)
    {
        $this->updateReferences($eventArgs);
    }

    private function updateReferences(EventArgs $eventArgs)
    {
        $ea = $this->getEventAdapter($eventArgs);
        $om = $ea->getObjectManager();
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
                        $ea->getIdentifier(
                            $this->getManager($mapping['type']),
                            $referencedObject
                        )
                    );
                }
            }
        }
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

    public function registerManager($type, $manager)
    {
        $this->managers[$type] = $manager;
    }

    public function getManager($type)
    {
        return $this->managers[$type];
    }

    protected function getNamespace()
    {
        return __NAMESPACE__;
    }
}
