<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Datagrid;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Query\Builder\AbstractNode;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

/**
 * This class is used to abstract the Admin Bundle from the different QueryBuilder implementations.
 */
class ProxyQuery implements ProxyQueryInterface
{
    /**
     * Query Builder Fluent interface for the QOM.
     *
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * The alias name used for the document FQN.
     *
     * @var string
     */
    protected $alias;

    /**
     * The root path.
     *
     * @var string|null
     */
    protected $root;

    /**
     * Property that determines the Ordering of the results.
     *
     * @var string
     */
    protected $sortBy;

    /**
     * Ordering of the results (ASC, DESC).
     *
     * @var string
     */
    protected $sortOrder;

    /**
     * PHPCR ODM Document Manager.
     *
     * @var DocumentManager
     */
    protected $documentManager;

    /**
     * Name of this document class.
     *
     * @var string
     */
    protected $documentName;

    /**
     * Creates a Query Builder from the QOMFactory.
     *
     * @param string $alias Short name to use instead of the FQN
     *                      of the document
     *
     * @throws \InvalidArgumentException if alias is not a string or an empty string
     */
    public function __construct(QueryBuilder $queryBuilder, $alias)
    {
        if (!\is_string($alias) || '' === $alias) {
            throw new \InvalidArgumentException('$alias must be a non empty string');
        }

        $this->qb = $queryBuilder;
        $this->alias = $alias;
    }

    /**
     * Allows for direct calls to the QueryBuilder.
     *
     * @param string $name name of the method
     * @param array  $args arguments of the call
     *
     * @codeCoverageIgnore
     */
    public function __call($name, $args)
    {
        return \call_user_func_array([$this->qb, $name], $args);
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param string $root root path to restrict what documents to find
     */
    public function setRootPath($root)
    {
        $this->root = $root;
    }

    /**
     * Executes the query, applying the source, the constraint of documents being of the phpcr:class of
     * this kind of document and builds an array of retrieved documents.
     *
     * @param array $params        doesn't have any effect
     * @param mixed $hydrationMode doesn't have any effect
     *
     * @throws \Exception if $this->sortOrder is not ASC or DESC
     *
     * @return array of documents
     */
    public function execute(array $params = [], $hydrationMode = null)
    {
        // always clone the original queryBuilder
        $queryBuilder = clone $this->qb;

        if ($this->getSortBy()) {
            $orderByChildren = $queryBuilder->getChildrenOfType(AbstractNode::NT_ORDER_BY);
            $queryBuilder->removeChildrenOfType(AbstractNode::NT_ORDER_BY);

            switch ($this->sortOrder) {
                case 'DESC':
                    $queryBuilder->orderBy()->desc()->field($this->alias.'.'.$this->sortBy);

                    break;
                case 'ASC':
                    $queryBuilder->orderBy()->asc()->field($this->alias.'.'.$this->sortBy);

                    break;
                default:
                    throw new \Exception('Unsupported sort order direction: '.$this->sortOrder);
            }

            foreach ($orderByChildren as $orderByChild) {
                $queryBuilder->addChild($orderByChild);
            }
        }

        if ($this->root) {
            $queryBuilder->andWhere()->descendant($this->root, $this->alias);
        }

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * Set the property to be sorted by.
     *
     * {@inheritdoc}
     */
    public function setSortBy($parentAssociationMappings, $fieldMapping)
    {
        $this->sortBy = $fieldMapping['fieldName'];

        return $this;
    }

    /**
     * Gets the property that defines the ordering.
     *
     * @return string the property to be sorted by
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * Set the sort ordering.
     *
     * {@inheritdoc}
     *
     * @param string $sortOrder (ASC|DESC)
     *
     * @throws \InvalidArgumentException if $sortOrder is not one of ASC or DESC
     */
    public function setSortOrder($sortOrder)
    {
        if (!\in_array($sortOrder, ['ASC', 'DESC'], true)) {
            throw new \InvalidArgumentException(sprintf('The parameter $sortOrder must be one of "ASC" or "DESC", got "%s"', $sortOrder));
        }
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * Get the ordering.
     *
     * @return string ASC or DESC
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
     * Gets the QueryBuilder.
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->qb;
    }

    /**
     * Sets the first result (offset).
     *
     * {@inheritdoc}
     */
    public function setFirstResult($firstResult)
    {
        $this->qb->setFirstResult($firstResult);

        return $this;
    }

    /**
     * Gets the first result (offset).
     *
     * @return int the offset
     */
    public function getFirstResult()
    {
        return $this->qb->getFirstResult();
    }

    /**
     * Set maximum number of results to retrieve.
     *
     * {@inheritdoc}
     */
    public function setMaxResults($maxResults)
    {
        $this->qb->setMaxResults($maxResults);

        return $this;
    }

    /**
     * Gets the maximum number of results to retrieve.
     *
     * @return int
     */
    public function getMaxResults()
    {
        return $this->qb->getMaxResults();
    }

    /**
     * Sets the document manager.
     */
    public function setDocumentManager(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;

        return $this;
    }

    /**
     * Gets the document manager.
     *
     * @return DocumentManager $documentManager
     */
    public function getDocumentManager()
    {
        return $this->documentManager;
    }

    public function getUniqueParameterId()
    {
    }

    public function entityJoin(array $associationMappings)
    {
    }
}
