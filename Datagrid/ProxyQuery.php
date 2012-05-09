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

use PHPCR\Query\QOM\QueryObjectModelFactoryInterface;
use Doctrine\ODM\PHPCR\DocumentManager;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use PHPCR\Query\QOM\QueryObjectModelConstantsInterface as Constants;
use PHPCR\Util\QOM\QueryBuilder;
use PHPCR\Query\QOM\ConstraintInterface;

/**
 * This class is used to abstract the Admin Bundle from the different QueryBuilder implementations
 */
class ProxyQuery implements ProxyQueryInterface
{
    /**
     * QOM factory used to get access to the QueryBuilder and its parameters
     *
     * @var \PHPCR\Query\QOM\QueryObjectModelFactoryInterface
     */
    protected $qomFactory;

    /**
     * Query Builder Fluent interface for the QOM
     *
     * @var \PHPCR\Util\QOM\QueryBuilder
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
     * @var \Doctrine\ODM\PHPCR\DocumentManager;
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
     * @param \PHPCR\Query\QOM\QueryObjectModelFactoryInterface $qomFactory
     * @param \PHPCR\Util\QOM\QueryBuilder $queryBuilder
     */
    public function __construct(QueryObjectModelFactoryInterface $qomFactory, QueryBuilder $queryBuilder)
    {
        $this->qomFactory = $qomFactory;
        $this->qb = $queryBuilder;
    }

    /**
     * Executes the query, applying the source, the constraint of documents being of the phpcr:class of
     * this kind of document and builds an array of retrieved documents.
     *
     * @param array $params doesn't have any effect
     * @param mixed $hydrationMode doesn't have any effect
     * @return aray of documents
     */
    public function execute(array $params = array(), $hydrationMode = null)
    {
        // get nodes
        $nodes = $this->executeRaw();

        $documents = array();

        foreach ($nodes as $node) {
            $documents[$node->getPath()] = $this->documentManager->getunitOfWork()->createDocument($this->documentName, $node);
        }

        return $documents;
    }

    /**
     * Executes the query and returns the raw nodes.
     * 
     * @return array with nodes
     */
    public function executeRaw()
    {
        $qf = $this->qomFactory;
        $qb = $this->qb;

        $qb->from($qf->selector($this->getNodeType()));

        //constraint
        $qb->andWhere($qf->comparison($qf->propertyValue('phpcr:class'), Constants::JCR_OPERATOR_EQUAL_TO, $qf->literal($this->documentName)));

        //ordering
        if ($this->getSortBy()) {
            $qb->orderBy($qf->propertyValue($this->sortBy), $this->sortOrder);
        }

        return $qb->execute()->getNodes();
    }

    /**
     * Allows for direct calls to the QueryBuilder.
     *
     * @param string $name name of the method
     * @param array $args arguments of the call
     * @return void
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
     *
     * @return mixed
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
     */
    public function getSingleScalarResult()
    {
        /* TODO: Figure out who calls this method and what to do here in context of PHPCR */
    }


    /**
     * Gets the QueryBuilder
     *
     * @return \PHPCR\Util\QOM\QueryBuilder
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
     * @param \Doctrine\ODM\PHPCR\DocumentManager $documentManager
     */
    public function setDocumentManager(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * Gets the document manager
     *
     * @return \Doctrine\ODM\PHPCR\DocumentManager $documentManager
     */
    public function getDocumentManager()
    {
        return $this->documentManager;
    }

    /**
     * Sets the document name (Class of the document)
     *
     * @param string $documentName
     */
    public function setDocumentName($documentName)
    {
        $this->documentName = $documentName;
    }

    /**
     * Gets the document name (Class of the document)
     *
     * @return string $documentName
     */
    public function getDocumentName()
    {
        return $this->documentName;
    }
    /**
     * Gets the QueryObjectModelFactory
     *
     * @return \PHPCR\Query\QOM\QueryobjectModelFactory
     */
    public function getQueryObjectModelFactory()
    {
      return $this->qomFactory;
    }

    /**
     * Adds a constraint to the query
     *
     * @param ConstraintInterface $constraint
     * @return void
     */
    public function andWhere(ConstraintInterface $constraint)
    {
        $this->qb->andWhere($constraint);
    }

    /**
     * Gets a string with the type of the node
     *
     * @return string type of the node
     */
    public function getNodeType()
    {
        $classMD = $this->documentManager->getClassMetadata($this->documentName);
        return $classMD->nodeType;
    }

    /**
     * @return mixed
     */
    public function getUniqueParameterId()
    {
    }

    /**
     * @param array $associationMappings
     *
     * @return mixed
     */
    public function entityJoin(array $associationMappings)
    {
    }
}
