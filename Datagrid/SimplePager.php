<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Datagrid;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\PHPCR\Query\Query as PHPCRQuery;

class SimplePager extends Pager
{
    /**
     * @var  boolean
     */
    protected $haveToPaginate;

    /**
     * How many pages to look forward to create links to next pages.
     *
     * @var int
     */
    protected $threshold;

    /**
     * @var int
     */
    protected $thresholdCount;

    /**
     * The threshold parameter can be used to determine how far ahead the pager
     * should fetch results.
     *
     * If set to 1 which is the minimal value the pager will generate a link to the next page
     * If set to 2 the pager will generate links to the next two pages
     * If set to 3 the pager will generate links to the next three pages
     * etc.
     *
     * @param integer $maxPerPage Number of records to display per page
     * @param int $threshold
     */
    public function __construct($maxPerPage = 10, $threshold = 2)
    {
        parent::__construct($maxPerPage);
        $this->setThreshold($threshold);
    }

    /**
     * Returns the exact count when there is only one page or when the current
     * equals the last page.
     *
     * In all other cases an estimate of the total count is returned.
     *
     * @return integer
     */
    public function getNbResults()
    {
        $n = ceil(($this->getLastPage() -1) * $this->getMaxPerPage());
        if ($this->getLastPage() == $this->getPage()) {
            return $n + $this->thresholdCount;
        }
        return $n;
    }

    /**
     * Get all the results for the pager instance
     *
     * @param mixed $hydrationMode A hydration mode identifier
     *
     * @return array
     */
    public function getResults($hydrationMode = null)
    {
        if (!$this->results) {
            $this->results = $this->getQuery()->execute(array(), $hydrationMode);
            $this->thresholdCount = count($this->results);
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
     * {@inheritDoc}
     */
    public function haveToPaginate()
    {
        return $this->haveToPaginate || $this->getPage() > 1;
    }

    /**
     * {@inheritDoc}
     */
    protected function resetIterator()
    {
        parent::resetIterator();
        $this->haveToPaginate = false;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException the QueryBuilder is uninitialized.
     */
    public function init()
    {
        if (!$this->getQuery()) {
            throw new \RuntimeException("Uninitialized QueryBuilder");
        }
        $this->resetIterator();

        if (0 == $this->getPage() || 0 == $this->getMaxPerPage()) {
            $this->setLastPage(0);
            $this->getQuery()->setFirstResult(0);
            $this->getQuery()->setMaxResults(0);
        } else {
            $offset = ($this->getPage() - 1) * $this->getMaxPerPage();
            $this->getQuery()->setFirstResult($offset);

            $maxOffset = $this->getThreshold() > 0
                ? $this->getMaxPerPage() * $this->threshold + 1 : $this->getMaxPerPage() + 1;

            $this->getQuery()->setMaxResults($maxOffset);
            $this->initializeIterator();

            $t = (int) ceil($this->thresholdCount / $this->getMaxPerPage()) + $this->getPage() - 1;
            $this->setLastPage($t);
        }
    }

    /**
     * Set how many pages to look forward to create links to next pages.
     *
     * @param int $threshold
     */
    public function setThreshold($threshold)
    {
        $this->threshold = (int) $threshold;
    }

    /**
     * @return int
     */
    public function getThreshold()
    {
        return $this->threshold;
    }
}
