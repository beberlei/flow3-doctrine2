<?php

namespace F3\Doctrine\Persistence\Metadata;

use Doctrine\ORM\Mapping\ClassMetadataFactory;

class FLOW3MetadataFactory extends ClassMetadataFactory
{
    /**
     * @var \F3\FLOW3\Object\ObjectManagerInterface
     */
    private $objectManager;

    public function injectObjectManager(\F3\FLOW3\Object\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Creates a new ClassMetadata instance for the given class name.
     *
     * @param string $className
     * @return Doctrine\ORM\Mapping\ClassMetadata
     */
    protected function newClassMetadataInstance($className)
    {
        return $this->objectManager->create('F3\Doctrine\Persistence\Metadata\FLOW3Metadata', $className);
    }
}