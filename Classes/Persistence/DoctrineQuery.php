<?php

namespace F3\Doctrine\Persistence;

/**
 * @scope prototype
 */
class DoctrineQuery implements \F3\FLOW3\Persistence\QueryInterface
{
    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    private $qb;

    /**
     * @var int
     */
    private $paramIndex = 1;
    
    /**
     * @param string $entityClass
     * @param EntityManager $entityManager
     */
    public function __construct($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function injectEntityManager(EntityManager $em)
    {
        $this->em = $em;
        $this->qb = $em->createQueryBuilder();
    }

    /**
     * @return Doctrine\ORM\QueryBuilder
     */
    public function getDoctrineQueryBuilder()
    {
        return $this->qb;
    }

    /**
     * Executes the query against the backend and returns the result
     *
     * @return \F3\FLOW3\Persistence\QueryResultInterface The query result
     * @api
     */
    public function execute()
    {
        return new DoctrineQueryResult($this->qb->getQuery()->getResult(), $this);
    }

    /**
     * Sets the property names to order the result by. Expected like this:
     * array(
     *  'foo' => \F3\FLOW3\Persistence\QueryInterface::ORDER_ASCENDING,
     *  'bar' => \F3\FLOW3\Persistence\QueryInterface::ORDER_DESCENDING
     * )
     *
     * @param array $orderings The property names to order by
     * @return \F3\FLOW3\Persistence\QueryInterface
     * @api
     */
    public function setOrderings(array $orderings)
    {
        foreach ($orderings AS $propertyName => $order) {
            $this->qb->addOrderBy($this->qb->getRootAlias() . "." . $propertyName, self::ORDER_DESCENDING ? 'DESC' : 'ASC');
        }
        return $this;
    }

    /**
     * Sets the maximum size of the result set to limit. Returns $this to allow
     * for chaining (fluid interface)
     *
     * @param integer $limit
     * @return \F3\FLOW3\Persistence\QueryInterface
     * @api
     */
    public function setLimit($limit)
    {
        $this->qb->setMaxResults($limit);
        return $this;
    }

    /**
     * Sets the start offset of the result set to offset. Returns $this to
     * allow for chaining (fluid interface)
     *
     * @param integer $offset
     * @return \F3\FLOW3\Persistence\QueryInterface
     * @api
     */
    public function setOffset($offset)
    {
        $this->qb->setFirstResult($offset);
        return $this;
    }

    /**
     * The constraint used to limit the result set. Returns $this to allow
     * for chaining (fluid interface)
     *
     * @param object $constraint Some constraint, depending on the backend
     * @return \F3\FLOW3\Persistence\QueryInterface
     * @api
     */
    public function matching($constraint)
    {
        $this->qb->where($constraint);
        return $this;
    }

    /**
     * Performs a logical conjunction of the two given constraints. The method
     * takes one or more contraints and concatenates them with a boolean AND.
     * It also accepts a single array of constraints to be concatenated.
     *
     * @param mixed $constraint1 The first of multiple constraints or an array of constraints.
     * @return object
     * @api
     */
    public function logicalAnd($constraint1)
    {
        return \call_user_func_array(array($this->qb->expr(), "andX"), \func_get_args());
    }

    /**
     * Performs a logical disjunction of the two given constraints. The method
     * takes one or more contraints and concatenates them with a boolean OR.
     * It also accepts a single array of constraints to be concatenated.
     *
     * @param mixed $constraint1 The first of multiple constraints or an array of constraints.
     * @return object
     * @api
     */
    public function logicalOr($constraint1)
    {
        return \call_user_func_array(array($this->qb->expr(), "orX"), \func_get_args());
    }

    /**
     * Performs a logical negation of the given constraint
     *
     * @param object $constraint Constraint to negate
     * @return object
     * @api
     */
    public function logicalNot($constraint)
    {
        return $this->qb->expr()->not($constraint);
    }

    /**
     * Returns an equals criterion used for matching objects against a query.
     *
     * It matches if the $operand equals the value of the property named
     * $propertyName. If $operand is NULL a strict check for NULL is done. For
     * strings the comparison can be done with or without case-sensitivity.
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @param boolean $caseSensitive Whether the equality test should be done case-sensitive for strings
     * @return object
     * @todo Decide what to do about equality on multi-valued properties
     * @api
     */
    public function equals($propertyName, $operand, $caseSensitive = TRUE)
    {
        return $this->qb->expr()->eq($propertyName, $this->getParamNeedle($operand));
    }

    /**
     * Returns a like criterion used for matching objects against a query.
     * Matches if the property named $propertyName is like the $operand, using
     * standard SQL wildcards.
     *
     * @param string $propertyName The name of the property to compare against
     * @param string $operand The value to compare with
     * @param boolean $caseSensitive Whether the matching should be done case-sensitive
     * @return object
     * @throws \F3\FLOW3\Persistence\Exception\InvalidQueryException if used on a non-string property
     * @api
     */
    public function like($propertyName, $operand, $caseSensitive = TRUE)
    {
        return $this->qb->expr()->like($propertyName, $this->getParamNeedle($operand));
    }

    /**
     * Returns a "contains" criterion used for matching objects against a query.
     * It matches if the multivalued property contains the given operand.
     *
     * If NULL is given as $operand, there will never be a match!
     *
     * @param string $propertyName The name of the multivalued property to compare against
     * @param mixed $operand The value to compare with
     * @return object
     * @throws \F3\FLOW3\Persistence\Exception\InvalidQueryException if used on a single-valued property
     * @api
     */
    public function contains($propertyName, $operand)
    {
        return "(" . $this->getParamNeedle($operand) . " MEMBER OF " . $this->qb->getRootAlias() . "." . $propertyName . ")";
    }

    /**
     * Returns an "isEmpty" criterion used for matching objects against a query.
     * It matches if the multivalued property contains no values or is NULL.
     *
     * @param string $propertyName The name of the multivalued property to compare against
     * @return boolean
     * @throws \F3\FLOW3\Persistence\Exception\InvalidQueryException if used on a single-valued property
     * @api
     */
    public function isEmpty($propertyName)
    {
        return "(" . $this->qb->getRootAlias() . "." . $propertyName . " IS EMPTY)";
    }

    /**
     * Returns an "in" criterion used for matching objects against a query. It
     * matches if the property's value is contained in the multivalued operand.
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with, multivalued
     * @return object
     * @throws \F3\FLOW3\Persistence\Exception\InvalidQueryException if used on a multi-valued property
     * @api
     */
    public function in($propertyName, $operand)
    {
        // Take care: In cannot be needled at the moment! DQL escapes it, but only as literals, making caching a bit harder.
        // This is a todo for Doctrine 2.1
        return $this->qb->expr()->in($propertyName, $operand);
    }

    /**
     * Returns a less than criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @return object
     * @throws \F3\FLOW3\Persistence\Exception\InvalidQueryException if used on a multi-valued property or with a non-literal/non-DateTime operand
     * @api
     */
    public function lessThan($propertyName, $operand)
    {
        return $this->qb->expr()->lt($propertyName, $this->getParamNeedle($operand));
    }

    /**
     * Returns a less or equal than criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @return object
     * @throws \F3\FLOW3\Persistence\Exception\InvalidQueryException if used on a multi-valued property or with a non-literal/non-DateTime operand
     * @api
     */
    public function lessThanOrEqual($propertyName, $operand)
    {
        return $this->qb->expr()->lte($propertyName, $this->getParamNeedle($operand));
    }

    /**
     * Returns a greater than criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @return object
     * @throws \F3\FLOW3\Persistence\Exception\InvalidQueryException if used on a multi-valued property or with a non-literal/non-DateTime operand
     * @api
     */
    public function greaterThan($propertyName, $operand)
    {
        return $this->qb->expr()->gt($propertyName, $this->getParamNeedle($operand));
    }

    /**
     * Returns a greater than or equal criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @return object
     * @throws \F3\FLOW3\Persistence\Exception\InvalidQueryException if used on a multi-valued property or with a non-literal/non-DateTime operand
     * @api
     */
    public function greaterThanOrEqual($propertyName, $operand)
    {
        return $this->qb->expr()->gte($propertyName, $this->getParamNeedle($operand));
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDoctrineQueryBuilder()
    {
        return $this->qb;
    }

    /**
     * @return \Doctrine\ORM\Query\Expr
     */
    public function getDoctrineExpression()
    {
        return $this->qb->expr();
    }

    /**
     * @param  mixed $operand
     * @return string
     */
    private function getParamNeedle($operand)
    {
        $idx = $this->paramIndex++;
        $this->qb->setParameter($idx, $operand);
        return "?$idx";
    }
}