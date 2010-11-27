<?php

namespace F3\Doctrine\Persistence\Events;

use Doctrine\ORM\Event\LifecycleEventArgs;

class UUIDManager
{
    /**
     * @var \F3\FLOW3\Persistence\PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var array
     */
    private $uuidToEntityClass = array();

    /**
     * @var AbstractPlatform
     */
    private $platform;

    /**
     * @var Doctrine\DBAL\Connection
     */
    private $conn;

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
     * @param \Doctrine\DBAL\Connection $conn
     */
    public function injectConnection(\Doctrine\DBAL\Connection $conn)
    {
        $this->conn = $conn;
        $this->platform = $this->conn->getDatabasePlatform();
    }

    /**
     * @param string $identifier
     */
    public function getClassIdentifier($identifier)
    {
        if (!isset($this->uuidToEntityClass[$identifier])) {
            $sql = "SELECT entity_class FROM " . $this->settings['uuid_table'] . " " .
                   "WHERE uuid = " . $this->conn->quote($identifier);
            $this->uuidToEntityClass[$identifier] = $this->conn->fetchColumn($sql);
        }
        return $this->uuidToEntityClass[$identifier];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $object = $args->getEntity();
        $class  = $args->getEntityManager()
                      ->getClassMetadata(get_class($object));

        $id = $class->getIdentifierValues($object);
        $uuid = $id[0];

        $this->conn->insert($this->settings['uuid_table'], array(
            'entity_class'  => $this->uuidToEntityClass[$uuid],
            'uuid'          => $uuid
        ));
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getEntity();
        $class  = $args->getEntityManager()
                      ->getClassMetadata(get_class($object));

        if (property_exists($object, 'FLOW3_Persistence_Entity_UUID')) {
            $class->setIdentifierValues($object, array($object->FLOW3_Persistence_Entity_UUID));
        }
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $object = $args->getEntity();
        $class  = $args->getEntityManager()
                      ->getClassMetadata(get_class($object));

        $id = $class->getIdentifierValues($object);
        $this->uuidToEntityClass[$id[0]] = $class->name;
    }
}