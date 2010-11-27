<?php

namespace F3\Doctrine\Persistence\Metadata;

use Doctrine\ORM\Mapping\Driver\Driver;

class FLOW3AnnotationsDriver implements Driver
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

    public function getAllClassNames()
    {
        return array();
    }

    public function isTransient($className)
    {
        return !$this->reflectionService->getClassSchema($className)->isAggregateRoot();
    }

    public function loadMetadataForClass($className, ClassMetadataInfo $metadata)
    {
        $classSchema = $this->reflectionService->getClassSchema($className);

        $idProperties = $classSchema->getIdentityProperties();
        $uuidPropertyName = $classSchema->getUuidPropertyName();

        foreach ($classSchema->getProperties() AS $propertyName => $data) {
            if ($classSchema->isMultiValuedProperty($propertyName)) {
                // definately an association many-to-many or one-to-many?
                

                
            } else {
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