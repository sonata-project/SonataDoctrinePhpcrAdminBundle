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
     */
    public function __construct(QueryObjectModelFactoryInterface $qomFactory)
    {
        $this->qomFactory = $qomFactory;
        $this->qb = new QueryBuilder($qomFactory);
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
        $qf = $this->qomFactory;
        $qb = $this->qb;

        //selector
        $classMD = $this->documentManager->getClassMetadata($this->documentName);
        $qb->from($qf->selector($classMD->nodeType));

        //constraint
        $qb->andWhere($qf->comparison($qf->propertyValue('[phpcr:class]'), Constants::JCR_OPERATOR_EQUAL_TO, $qf->literal($this->documentName)));

        //ordering
        $qb->orderBy($qf->propertyValue($this->sortBy), $this->sortOrder);

        $nodes = $qb->execute()->getNodes();

        $documents = array();

        foreach ($nodes as $path => $node) {
            $documents[$node->getPath()] = $this->documentManager->getunitOfWork()->createDocument($this->documentName, $node);
        }
        return $documents;

    }

    /**
     * Allows for direct calls to the QueryBuilder. TODO: I am not sure it this should exist in PHPCR context.
     *
     * @param string $name name of the method
     * @param array $args arguments of the call
     * @return void
     */
    public function __call($name, $args)
    {
        return call_user_func_array(array($this->qb, $name), $args);
    }

    /**
     * Set the property to be sorted by
     *
     * @param string $sortBy property name
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;
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
     * Sets the document name (Class of the document)
     *
     * @param string $documentName
     */
    public function setDocumentName($documentName)
    {
        $this->documentName = $documentName;
    }
}
