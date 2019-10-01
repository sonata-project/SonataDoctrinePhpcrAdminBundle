<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    Symfony\Cmf\Bundle\ResourceBundle\CmfResourceBundle::class => ['all' => true],
    Symfony\Cmf\Bundle\ResourceRestBundle\CmfResourceRestBundle::class => ['all' => true],
    JMS\SerializerBundle\JMSSerializerBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Knp\Bundle\MenuBundle\KnpMenuBundle::class => ['all' => true],
    Sonata\AdminBundle\SonataAdminBundle::class => ['all' => true],
    Sonata\CoreBundle\SonataCoreBundle::class => ['all' => true],
    Sonata\Doctrine\Bridge\Symfony\Bundle\SonataDoctrineBundle::class => ['all' => true],
    Sonata\BlockBundle\SonataBlockBundle::class => ['all' => true],
    Sonata\DoctrinePHPCRAdminBundle\SonataDoctrinePHPCRAdminBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Cmf\Bundle\TreeBrowserBundle\CmfTreeBrowserBundle::class => ['all' => true],
    Doctrine\Bundle\PHPCRBundle\DoctrinePHPCRBundle::class => ['all' => true],
];
