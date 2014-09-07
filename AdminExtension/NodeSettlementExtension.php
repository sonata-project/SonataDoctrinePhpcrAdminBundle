<?php

namespace Sonata\DoctrinePHPCRAdminBundle\AdminExtension;

use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;

class NodeSettlementExtension extends AdminExtension
{
    protected $nodeSettlementStrategy;

    public function __construct(NodeSettlementStrategyInterface $nodeSettlementStrategy)
    {
        $this->nodeSettlementStrategy = $nodeSettlementStrategy;
    }

    /**
     * Asks the node settlement strategy for the correct path to what should be
     * the parent node of the new instance, and applies the result. Returns early
     * if the strategy cannot decide anything and returns false.
     *
     * {@inheritdoc}
     */
    public function alterNewInstance(AdminInterface $admin, $object)
    {
        $path = $this->nodeSettlementStrategy->getParentNodePath($object);

        if ($path === false) {
            return;
        }

        $documentRoot = $admin->getModelManager()
            ->getDocumentManager()
            ->find(null, $path);

        if (!$documentRoot) {
            throw new MissingDocumentException(sprintf(
                'Could not find document root at path "%s"',
                $path
            ));
        }

        $object->setParentDocument($documentRoot);
    }
}
