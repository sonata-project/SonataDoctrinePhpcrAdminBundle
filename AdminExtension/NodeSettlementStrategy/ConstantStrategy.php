<?php

namespace Sonata\DoctrinePHPCRAdminBundle\AdminExtension\NodeSettlementStrategy;

use Sonata\DoctrinePHPCRAdminBundle\AdminExtension\NodeSettlementStrategyInterface;
use Sonata\DoctrinePHPCRAdminBundle\AdminExtension\NodeSettlementStrategy\Exception\MissingConstantException;

/**
 * Looks for a constant in the class definition of the object, and use it as a
 * settlement for the node.
 */
class ConstantStrategy implements NodeSettlementStrategyInterface
{
    protected $constantName;

    public function __construct($constantName)
    {
        $this->constantName = $constantName;
    }

    public function getParentNodePath($object)
    {
        $fullConstantName = sprintf('%s::%s', get_class($object), $this->constantName);

        if (defined($fullConstantName)) {
            return constant($fullConstantName);
        }

        throw new MissingConstantException(sprintf(
            'Constant with name "%s" could not be found',
            $fullConstantName
        ));
    }
}
