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
                $property->setValue(
                    $object,
                    $ea->getSingleReference(
                        $this->managers[$mapping['type']],
                        $mapping['class'],
                        $referencedObjectId
                    )
                );
            }
        }
        foreach ($config['referenceMany'] as $mapping) {
        }
    }

    public function prePersist(EventArgs $eventArgs)
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
}
