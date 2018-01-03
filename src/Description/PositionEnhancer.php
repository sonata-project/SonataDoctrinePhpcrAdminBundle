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

namespace Sonata\DoctrinePHPCRAdminBundle\Description;

use Doctrine\Common\Persistence\ManagerRegistry;
use PHPCR\PathNotFoundException;
use PHPCR\Util\PathHelper;
use Symfony\Cmf\Component\Resource\Description\Description;
use Symfony\Cmf\Component\Resource\Description\DescriptionEnhancerInterface;
use Symfony\Cmf\Component\Resource\Puli\Api\PuliResource;
use Symfony\Cmf\Component\Resource\Repository\Resource\CmfResource;

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

    /**
     * @param ManagerRegistry $manager
     * @param $sessionName
     */
    public function __construct(ManagerRegistry $manager, $sessionName)
    {
        $this->session = $manager->getConnection($sessionName);
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(Description $description)
    {
        $nodePath = $description->getResource()->getPath();
        $nodeName = PathHelper::getNodeName($nodePath);
        $parentPath = PathHelper::getParentPath($nodePath);

        try {
            $parentNode = $this->session->getNode($parentPath);
        } catch (PathNotFoundException $exception) {
            return false;
        }

        $nodeIterator = $parentNode->getNodes();
        $nodeIterator->rewind();
        $counter = 0;
        while ($nodeIterator->valid()) {
            ++$counter;
            if ($nodeIterator->key() === $nodeName) {
                break;
            }
            $nodeIterator->next();
        }

        $description->set('position', $counter);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(PuliResource $resource)
    {
        if (!$resource instanceof CmfResource) {
            return false;
        }

        try {
            $parentNode = $this->session->getNode(PathHelper::getParentPath($resource->getPath()));
        } catch (PathNotFoundException $exception) {
            return false;
        }

        // Todo: check for non orderable type

        return true;
    }
}
