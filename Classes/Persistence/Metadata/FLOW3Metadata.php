<?php

namespace F3\Doctrine\Persistence\Metadata;

use Doctrine\ORM\Mapping\ClassMetadata;

class FLOW3Metadata extends ClassMetadata
{
    /**
     *
     * @var \F3\FLOW3\Object\ObjectManagerInterface
     */
    private $objectManager;

    public function injectObjectManager(\F3\FLOW3\Object\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Creates a new instance of the mapped class, without invoking the constructor.
     *
     * @return object
     */
    public function newInstance()
    {
        return $this->objectManager->create($this->name);
    }
}