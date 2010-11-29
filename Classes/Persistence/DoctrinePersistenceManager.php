<?php

namespace F3\Doctrine\Persistence;

use F3\FLOW3\Persistence\PersistenceManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\EntityManager;

class DoctrinePersistenceManager implements PersistenceManagerInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $settings = array();

    public function injectEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getIdentifierByObject($object)
    {
        if ($this->em->contains($object)) {
            $identifier = $this->em->getUnitOfWork()->getEntityIdentifier($object);
            return $identifier[0];
        } elseif (property_exists($object, 'FLOW3_Persistence_Entity_UUID')) {
            // entities created get an UUID set through AOP
            return $object->FLOW3_Persistence_Entity_UUID;
        } elseif (property_exists($object, 'FLOW3_Persistence_ValueObject_Hash')) {
            // valueobjects created get a hash set through AOP
            return $object->FLOW3_Persistence_ValueObject_Hash;
        } else {
            return NULL;
        }
    }

    /**
     * @param string $identifier
     */
    public function getObjectByIdentifier($identifier)
    {
        
    }

    public function getObjectCountByQuery(\F3\FLOW3\Persistence\QueryInterface $query)
    {

    }

    public function getObjectDataByQuery(\F3\FLOW3\Persistence\QueryInterface $query)
    {
        return $query->execute();
    }

    public function initialize()
    {
        if (!isset($this->settings['uuid_table'])) {
            $this->settings['uuid_table'] = 'uuids';
        }
    }

    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
    }

    public function isNewObject($object)
    {
        return ($this->em->getUnitOfWork()->getEntityState($object, UnitOfWork::STATE_NEW) == UnitOfWork::STATE_NEW);
    }

    public function persistAll()
    {
        $this->em->flush();
    }

    public function replaceObject($existingObject, $newObject)
    {
        if (!$this->em->contains($existingObject)) {
            // exception
        }

        if ($this->em->contains($newObject)) {
            // exception: object should be new!
        }

        // 1. copy UUID/primary key from existing to newObject
        // 2. merge $newObject into persistence context
    }
}