<?php

namespace F3\Doctrine\Persistence;

use F3\FLOW3\Persistence\PersistenceManagerInterface;
use F3\FLOW3\Persistence\QueryInterface;
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\EntityManager;

class DoctrinePersistenceManager implements PersistenceManagerInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    public function injectEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getIdentifierByObject($object)
    {
        if ($this->em->contains($object)) {
            return $this->em->getUnitOfWork()->getEntityIdentifier($object);
            return $identifier[0];
        } else {
            throw new \RuntimeException("Entity is not managed. No identifier specified for this object.");
        }
    }

    /**
     * @param string $identifier
     */
    public function getObjectByIdentifier($identifier)
    {
        throw new \RuntimeException("Not supported by Doctrine 2. Use repository to query for Entity-Name + ID.");
    }

    public function getObjectCountByQuery(QueryInterface $query)
    {
        if (!($query instanceof DoctrineQuery)) {
            throw new \RuntimeException("DoctrinePersistenceManager only works with Doctrine Queries.");
        }

        /* @var $dqlQuery \Doctrine\ORM\Query */
        $dqlQuery = clone $query->getDoctrineQuery()->getQuery();
        $dqlQuery->setHint(Query::HINT_CUSTOM_TREE_WALKERS, array('DoctrineExtensions\Paginate\CountWalker'));
        $dqlQuery->setFirstResult(null)->setMaxResults(null);

        return $dqlQuery->getSingleScalarResult();
    }

    public function getObjectDataByQuery(QueryInterface $query)
    {
        return $query->execute();
    }

    public function initialize()
    {

    }

    public function injectSettings(array $settings)
    {

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
            throw new \RuntimeException("Cannot replace existing object that is not in the persistence maanger.");
        }

        if ($this->em->contains($newObject)) {
            throw new \RuntimeException("New object is already in the persistence manager. Cannot replace it into the persistence manager.");
        }

        $this->em->merge($newObject);
    }
}