<?php

namespace F3\Doctrine\Persistence\Metadata;

use Doctrine\ORM\Mapping\Driver\Driver;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

class FLOW3AnnotationsDriver extends AnnotationDriver
{
    /**
     * @var \F3\FLOW3\Reflection\ReflectionService
     */
    protected $reflectionService;

    /**
     * Injects a Reflection Service instance used for processing objects
     *
     * @param \F3\FLOW3\Reflection\ReflectionService $reflectionService
     * @return void
     * @author Karsten Dambekalns <karsten@typo3.org>
     */
    public function injectReflectionService(\F3\FLOW3\Reflection\ReflectionService $reflectionService)
    {
        $this->reflectionService = $reflectionService;
    }

    public function loadMetadataForClass($className, ClassMetadataInfo $metadata)
    {
        parent::loadMetadataForClass($className, $metadata);

        $classSchema = $this->reflectionService->getClassSchema($className);

        $idProperties = $classSchema->getIdentityProperties();
        $uuidPropertyName = $classSchema->getUuidPropertyName();

        foreach ($classSchema->getProperties() AS $propertyName => $data) {
            if (!$classSchema->isMultiValuedProperty($propertyName)) {
                if ($metadata->hasField($propertyName)) {
                    continue;
                }

                $mapping = array();
                $mapping['name'] = $propertyName;
                $mapping['nullable'] = false;
                $mapping['unique'] = false;
                // field or many-to-one/one-to-one property
                if ($propertyName == $uuidPropertyName) {
                    $mapping['type'] = 'string';
                    $mapping['length'] = 36;
                    $mapping['unique'] = true;
                } else if (in_array($propertyName, $idProperties)) {
                    $mapping['id'] = true;
                    $mapping['type'] = $data['type'];
                } else {
                    $mapping['type'] = $data['type'];
                }

                $metadata->mapField($mapping);
            }
        }
        
    }
}