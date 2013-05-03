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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\PHPCR\Query\Query as PHPCRQuery;

class SimplePager extends Pager
{
    protected $haveToPaginate;

    /**
     * Returns a query for counting the total results.
     *
     * @return integer
     */
    public function computeNbResult()
    {
        return null;
    }

    /**
     * Get all the results for the pager instance
     *
     * @param mixed $hydrationMode A hydration mode identifier
     * @return array
     */
    public function getResults($hydrationMode = null)
    {
        if (!$this->results) {
            $this->results = $this->getQuery()->execute(array(), $hydrationMode);
            if (count($this->results) > $this->getMaxPerPage()) {
                $this->haveToPaginate = true;
                $this->results = new ArrayCollection($this->results->slice(0, $this->getMaxPerPage()));
            } else {
                $this->haveToPaginate = false;
            }
        }

        return $this->results;
    }

    /**
     * Returns true if the current query requires pagination.
     *
     * @return boolean
     */
    public function haveToPaginate()
    {
        return $this->haveToPaginate || $this->getPage() > 1;
    }

    protected function resetIterator()
    {
        parent::resetIterator();
        $this->haveToPaginate = false;
    }

    /**
     * Initializes the pager setting the offset and maxResults in ProxyQuery
     * and obtaining the total number of pages.
     *
     * @return void
     * @throws \RuntimeException the QueryBuilder is uninitialized.
     */
    public function init()
    {
        if (!$this->getQuery()) {
            throw new \RuntimeException("Uninitialized QueryBuilder");
        }
        $this->resetIterator();

        //if (count($this->getParameters()) > 0) {
        //    $this->getQuery()->setParameters($this->getParameters());
        //}

        if (0 == $this->getPage() || 0 == $this->getMaxPerPage()) {
            $this->setLastPage(0);
            $this->getQuery()->setFirstResult(0);
            $this->getQuery()->setMaxResults(0);
        } else {
            $offset = ($this->getPage() - 1) * $this->getMaxPerPage();
            $this->getQuery()->setFirstResult($offset);
            $this->getQuery()->setMaxResults($this->getMaxPerPage()+1);
            $this->initializeIterator();
            $this->setLastPage($this->getPage() + (int) $this->haveToPaginate);
        }
    }
}
