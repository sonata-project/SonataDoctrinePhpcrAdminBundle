<?php

namespace Sonata\DoctrinePHPCRAdminBundle\AdminExtension;

/**
 * Implementations, when given an object, should be able to decide where it
 * should be located in the tree.
 */
interface NodeSettlementStrategyInterface
{
    /**
     * Tells the given object where to settle
     *
     * @param  object an object that will be persisted
     *
     * @return string|false the path to the node that should become the parent
     *                      for the given object. false means the strategy was
     *                      unable to find anything and may be skipped.
     */
    public function getParentNodePath($object);
}
