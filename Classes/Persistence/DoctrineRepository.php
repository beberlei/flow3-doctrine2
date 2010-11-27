<?php

namespace F3\Doctrine\Persistence;

use F3\FLOW3\Persistence\RepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class DoctrineRepository implements RepositoryInterface
{
    /**
     * Objects added to this repository during the current session
     *
     * @var \SplObjectStorage
     */
    protected $addedObjects;
    /**
     * Objects removed but not found in $this->addedObjects at removal time
     *
     * @var \SplObjectStorage
     */
    protected $removedObjects;

    /**
     * @var \F3\FLOW3\Persistence\QueryFactoryInterface
     */
    protected $queryFactory;

    /**
     * @var \F3\FLOW3\Persistence\PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var string
     */
    protected $objectType;

    public function __construct()
    {
        $this->addedObjects = new \SplObjectStorage();
        $this->removedObjects = new \SplObjectStorage();

        if ($this->objectType === NULL) {
            $this->objectType = str_replace(array('\\Repository\\', 'Repository'), array('\\Model\\', ''), $this->FLOW3_AOP_Proxy_getProxyTargetClassName());
        }
    }

    /**
     * @param EntityManager $em
     * @author Benjamin Eberlei <kontakt@beberlei.de>
     */
    public function injectEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Injects the persistence manager
     *
     * @param \F3\FLOW3\Persistence\PersistenceManagerInterface $persistenceManager
     * @return void
     * @author Robert Lemke <robert@typo3.org>
     */
    public function injectPersistenceManager(\F3\FLOW3\Persistence\PersistenceManagerInterface $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * Injects a QueryFactory instance
     *
     * @param \F3\FLOW3\Persistence\QueryFactoryInterface $queryFactory
     * @return void
     * @author Karsten Dambekalns <karsten@typo3.org>
     */
    public function injectQueryFactory(\F3\FLOW3\Persistence\QueryFactoryInterface $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }

    /**
     * @return Doctrine\ORM\EntityRepository
     * @author Benjamin Eberlei <kontakt@beberlei.de>
     */
    public function getDoctrineRepository()
    {
        return $this->em->getRepository($this->objectType);
    }

    public function add($object)
    {
        $this->addedObjects->attach($object);
        $this->removedObjects->detach($object);
        
        $this->em->persist($object);
    }

    public function countAll()
    {
        // build a dql query for single key entities otherwise throw not supported excetion.
    }

    /**
     * @return \F3\FLOW3\Persistence\QueryInterface
     */
    public function createQuery()
    {
        return $this->queryFactory->create($this->objectType);
    }

    public function findByUuid($uuid)
    {
        
    }

    public function getAddedObjects()
    {
        return $this->addedObjects;
    }

    public function getRemovedObjects()
    {
        return $this->removedObjects;
    }

    public function remove($object)
    {
        if ($this->addedObjects->contains($object)) {
            $this->addedObjects->detach($object);
        }
        $this->em->remove($object);
        $this->removedObjects->attach($object);
    }

    public function removeAll()
    {
        // TODO: use DQL here? would be much more performant
        foreach ($this->findAll() AS $object) {
            $this->remove($object);
        }
    }

    public function replace($existingObject, $newObject)
    {
        if (!($existingObject instanceof $this->objectType)) {
            throw new \F3\FLOW3\Persistence\Exception\IllegalObjectTypeException('The modified object given to update() was not of the type (' . $this->objectType . ') this repository manages.', 1249479625);
        }
        if (!($newObject instanceof $this->objectType)) {
            throw new \F3\FLOW3\Persistence\Exception\IllegalObjectTypeException('The modified object given to update() was not of the type (' . $this->objectType . ') this repository manages.', 1249479625);
        }
    }

    public function setDefaultOrderings(array $defaultOrderings)
    {
        
    }

    public function update($modifiedObject)
    {
        if (!($modifiedObject instanceof $this->objectType)) {
            throw new \F3\FLOW3\Persistence\Exception\IllegalObjectTypeException('The modified object given to update() was not of the type (' . $this->objectType . ') this repository manages.', 1249479625);
        }

        
    }

    /**
     * Magic for everyone!
     * 
     * @param <type> $method
     * @param <type> $args
     * @return <type>
     */
    public function __call($method, $args)
    {
        return $this->getDoctrineRepository()->__call($method, $arguments);
    }

    /**
     * Returns the class name of this class. Seems useless until you think about
     * the possibility of $this *not* being an AOP proxy. If $this is an AOP proxy
     * this method will be overridden.
     *
     * @return string Class name of the repository. If it is proxied, it's still the (target) class name.
     * @author Karsten Dambekalns <karsten@typo3.org>
     */
    protected function FLOW3_AOP_Proxy_getProxyTargetClassName()
    {
        return get_class($this);
    }
}