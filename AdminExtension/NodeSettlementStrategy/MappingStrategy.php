<?php

namespace Sonata\DoctrinePHPCRAdminBundle\AdminExtension\NodeSettlementStrategy;

use Sonata\DoctrinePHPCRAdminBundle\AdminExtension\NodeSettlementStrategyInterface;
use Sonata\DoctrinePHPCRAdminBundle\AdminExtension\NodeSettlementStrategy\Exception\MissingMappingException;
use Doctrine\Common\Util\ClassUtils;

/**
 * Looks for a match for the object's class in an internal hash, and use it as a
 * settlement for the node.
 */
class MappingStrategy implements NodeSettlementStrategyInterface
{
    /**
     * @var array an associative array that associates a document class to a
     *            path in the tree that should receive documents of this class.
     */
    protected $mapping;

    public function __construct($mapping)
    {
        $this->mapping = $mapping;
    }

    public function getParentNodePath($object)
    {
        $class = ClassUtils::getClass($object);
        if (isset($this->mapping[$class])) {
            return $this->mapping[$class];
        }

        throw new MissingMappingException(sprintf(
            'No path mapping could be found for class %s',
            $class
        ));
    }
}
