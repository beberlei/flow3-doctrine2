<?php

namespace F3\Doctrine\Persistence;

class DoctrineQueryFactory implements \F3\FLOW3\Persistence\QueryFactoryInterface
{
	/**
     * @var \F3\FLOW3\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Injects the FLOW3 object factory
     *
     * @param \F3\FLOW3\Object\ObjectManagerInterface $objectManager
     * @return void
     * @author Karsten Dambekalns <karsten@typo3.org>
     */
    public function injectObjectManager(\F3\FLOW3\Object\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Creates a query object working on the given class name
     *
     * @param string $className
     * @return \F3\Doctrine\Persistence\DoctrineQuery
     * @author Benjamin Eberlei <kontakt@beberlei.de>
     * @api
     */
    public function create($className)
    {
        return $this->objectManager->create('F3\Doctrine\Persistence\DoctrineQuery', $className);
    }
}