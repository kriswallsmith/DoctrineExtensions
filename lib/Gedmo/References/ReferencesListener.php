<?php

namespace Gedmo\References;

use Doctrine\ODM\MongoDB\DocumentManager;

use Doctrine\Common\EventArgs;
use Gedmo\Mapping\MappedEventSubscriber;

class ReferencesListener extends MappedEventSubscriber
{
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
        $object = $om instanceof DocumentManager ? $eventArgs->getDocument() : $eventArgs->getEntity();
        $meta = $om->getClassMetadata(get_class($object));
        var_dump($this->getConfiguration($om, $meta->name));
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

    protected function getNamespace()
    {
        return __NAMESPACE__;
    }
}
