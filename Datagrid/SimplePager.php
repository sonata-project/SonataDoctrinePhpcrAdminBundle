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
    /** @var  bool $haveToPaginate */
    protected $haveToPaginate;

    /** @var int $threshold */
    protected $threshold;

    /** @var  int $thresholdCount */
    protected $thresholdCount;

    /**
     * The threshold parameter can be used to determine how far ahead
     * the pager should fetch results.
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
        $this->setMaxPerPage($maxPerPage);
        $this->setThreshold($threshold);
    }

    /**
     * @return string
     */
    public function getNbResults()
    {
        if ($this->getLastPage() == 1) {
            return $this->thresholdCount;
        } else {
            $n = ceil(($this->getLastPage() -1) * $this->getMaxPerPage());
            return "more then $n ";
        }
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

            $maxOffset = $this->getThreshold() > 0
                ? $this->getMaxPerPage() * $this->threshold + 1 : $this->getMaxPerPage() + 1;

            $this->getQuery()->setMaxResults($maxOffset);
            $this->initializeIterator();

            $t = (int) ceil($this->thresholdCount / $this->getMaxPerPage()) + $this->getPage() - 1;
            $this->setLastPage($t);
        }
    }

    /**
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
