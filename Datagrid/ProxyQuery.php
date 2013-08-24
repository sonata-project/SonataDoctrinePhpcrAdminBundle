<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Datagrid;

use Doctrine\ODM\PHPCR\DocumentManager;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Doctrine\ODM\PHPCR\Query\QueryBuilder;

/**
 * This class is used to abstract the Admin Bundle from the different QueryBuilder implementations
 */
class ProxyQuery implements ProxyQueryInterface
{
    /**
     * Query Builder Fluent interface for the QOM
     *
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * Property that determines the Ordering of the results
     *
     * @var string
     */
    protected $sortBy;

    /**
     * Ordering of the results (ASC, DESC)
     *
     * @var string
     */
    protected $sortOrder;

    /**
     * PHPCR ODM Document Manager
     *
     * @var DocumentManager;
     */
    protected $documentManager;

    /**
     * Name of this document class
     *
     * @var string
     */
    protected $documentName;

    /**
     * Creates a Query Builder from the QOMFactory
     *
     * @param QueryBuilder $queryBuilder
     */
    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->qb = $queryBuilder;
    }

    /**
     * Executes the query, applying the source, the constraint of documents being of the phpcr:class of
     * this kind of document and builds an array of retrieved documents.
     *
     * @param array $params doesn't have any effect
     * @param mixed $hydrationMode doesn't have any effect
     *
     * @return array of documents
     */
    public function execute(array $params = array(), $hydrationMode = null)
    {
        if ($this->getSortBy()) {
            $this->qb->orderBy($this->sortBy, $this->sortOrder);
        }

        return $this->qb->getQuery()->execute();
    }

    /**
     * Allows for direct calls to the QueryBuilder.
     *
     * @param string $name name of the method
     * @param array $args arguments of the call
     *
     * @codeCoverageIgnore
     */
    public function __call($name, $args)
    {
        return call_user_func_array(array($this->qb, $name), $args);
    }

    /**
     * Set the property to be sorted by
     *
     * @param array $parentAssociationMappings
     * @param array $fieldMapping
     */
    public function setSortBy($parentAssociationMappings, $fieldMapping)
    {
        $this->sortBy = $fieldMapping['fieldName'];
    }

    /**
     * Gets the property that defines the ordering
     *
     * @return string the property to be sorted by
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * Sets the ordering
     *
     * @param string $sortOrder (ASC|DESC)
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * Gets the ordering
     *
     * @return string (ASC|DESC)
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @codeCoverageIgnore
     *
     * @throws \Exception
     */
    public function getSingleScalarResult()
    {
        /* TODO: Figure out who calls this method and what to do here in context of PHPCR */
        throw new \Exception('Used by what??');
    }


    /**
     * Gets the QueryBuilder
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
      return $this->qb;
    }

    /**
     * Sets the first result (offset)
     *
     * @param integer $firstResult
     */
    public function setFirstResult($firstResult)
    {
        $this->qb->setFirstResult($firstResult);
    }

    /**
     * Gets the first result (offset)
     *
     * @return integer the offset
     */
    public function getFirstResult()
    {
        return $this->qb->getFirstResult();
    }

    /**
     * Set maximum number of results to retrieve
     *
     * @param integer $maxResults
     */
    public function setMaxResults($maxResults)
    {
        $this->qb->setMaxResults($maxResults);
    }

    /**
     * Gets the maximum number of results to retrieve
     *
     * @return integer
     */
    public function getMaxResults()
    {
        return $this->qb->getMaxResults();
    }

    /**
     * Sets the document manager
     *
     * @param DocumentManager $documentManager
     */
    public function setDocumentManager(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * Gets the document manager
     *
     * @return DocumentManager $documentManager
     */
    public function getDocumentManager()
    {
        return $this->documentManager;
    }

    /**
     * @throws \Exception
     */
    public function getNodeType()
    {
        throw new \Exception('Used by what??');
        $classMD = $this->documentManager->getClassMetadata($this->documentName);
        return $classMD->nodeType;
    }

    public function getUniqueParameterId()
    {
    }

    /**
     * @param array $associationMappings
     */
    public function entityJoin(array $associationMappings)
    {
    }
}
