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

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Fixtures\App\DataFixtures\Phpcr;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PHPCR\Util\NodeHelper;
use Sonata\DoctrinePHPCRAdminBundle\Tests\Fixtures\App\Document\Content;

class LoadTreeData implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        NodeHelper::createPath($manager->getPhpcrSession(), '/test');

        NodeHelper::createPath($manager->getPhpcrSession(), '/test/content');
        NodeHelper::createPath($manager->getPhpcrSession(), '/test/routes');

        $contentRoot = $manager->find(null, '/test/content');
        $routeRoot = $manager->find(null, '/test/routes');

        $singleRoute = new Content();
        $singleRoute->setName('route-1');
        $singleRoute->setTitle('Route 1');
        $singleRoute->setParentDocument($routeRoot);
        $manager->persist($singleRoute);

        $routeAlikeA = new Content();
        $routeAlikeA->setName('route-2');
        $routeAlikeA->setTitle('Route 2');
        $routeAlikeA->setParentDocument($routeRoot);
        $manager->persist($routeAlikeA);

        $routeAlikeB = new Content();
        $routeAlikeB->setName('route-3');
        $routeAlikeB->setTitle('Route 3');
        $routeAlikeB->setParentDocument($routeRoot);
        $manager->persist($routeAlikeB);

        $child = new Content();
        $child->setName('child');
        $child->setTitle('Content Child');

        $content = new Content();
        $content->setName('content-1');
        $content->setTitle('Content 1');
        $content->setSingleRoute($singleRoute);
        $content->addRoute($routeAlikeA);
        $content->addRoute($routeAlikeB);
        $content->setChild($child);
        $content->setParentDocument($contentRoot);
        $manager->persist($content);

        $childA = new Content();
        $childA->setName('content-3');
        $childA->setTitle('Content Child A');

        $childB = new Content();
        $childB->setName('content-3');
        $childB->setTitle('Content Child B');

        $content = new Content();
        $content->setName('content-2');
        $content->setParentDocument($contentRoot);
        $content->addChild($childA);
        $content->addChild($childB);
        $content->setTitle('Content 2');
        $manager->persist($content);

        $manager->flush();
    }
}
