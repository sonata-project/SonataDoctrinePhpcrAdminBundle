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
 * This class try to unify the query usage with Doctrine
 */
class ProxyQuery implements ProxyQueryInterface
{
    protected $qomFactory;

    protected $qb;

    protected $sortBy;

    protected $sortOrder;

    protected $documentManager;

    protected $documentName;

    public function __construct(QueryObjectModelFactoryInterface $qomFactory)
    {
        $this->qomFactory = $qomFactory;
        $this->qb = new QueryBuilder($qomFactory);
    }

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

        $nodes = $qb->execute();

        $documents = array();

        foreach ($nodes as $path => $node) {
            $documents[$node->getPath()] = $this->documentManager->getunitOfWork()->createDocument($this->documentName, $node->getNode());
        }
        return $documents;

    }

    public function __call($name, $args)
    {
        return call_user_func_array(array($this->qb, $name), $args);
    }

    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;
    }

    public function getSortBy()
    {
        return $this->sortBy;
    }

    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    public function getSingleScalarResult()
    {
        /* FIX: What to do here? */
    }


    public function getQueryBuilder()
    {
      return $this->qomFactory;
    }

    public function setFirstResult($firstResult)
    {
        $this->qb->setFirstResult($firstResult);
    }

    public function getFirstResult()
    {
        return $this->qb->getFirstResult();
    }

    public function setMaxResults($maxResults)
    {
        $this->qb->setMaxResults($maxResults);
    }

    public function getMaxResults()
    {
        return $this->qb->getMaxResults();
    }

    public function setDocumentManager(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function setDocumentName($documentName)
    {
        $this->documentName = $documentName;
    }
}
