<?php

namespace Sonata\DoctrinePHPCRAdminBundle\Description;

use Doctrine\Common\Persistence\ManagerRegistry;
use PHPCR\PathNotFoundException;
use Symfony\Cmf\Component\Resource\Description\Description;
use Symfony\Cmf\Component\Resource\Description\DescriptionEnhancerInterface;
use Symfony\Cmf\Component\Resource\Puli\Api\PuliResource;

/**
 * A description enhancer to add the position/sorting value of children on a parent.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class PositionEnhancer implements DescriptionEnhancerInterface
{
    /**
     * @var \PHPCR\SessionInterface
     */
    private $session;

    public function __construct(ManagerRegistry $manager, $sessionName)
    {
        $this->session = $manager->getConnection($sessionName);
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(Description $description)
    {
        try {
            $node = $this->session->getNode($description->getResource()->getPath());
        } catch (PathNotFoundException $exception) {
            return;
        }

        $description->set('position', $node->getIndex());
    }

    /**
     * {@inheritdoc}
     */
    public function supports(PuliResource $resource)
    {
        return true;
    }
}
