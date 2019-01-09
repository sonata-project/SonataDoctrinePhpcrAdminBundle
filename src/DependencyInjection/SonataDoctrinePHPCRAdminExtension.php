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

namespace Sonata\DoctrinePHPCRAdminBundle\DependencyInjection;

use Sonata\AdminBundle\DependencyInjection\AbstractSonataAdminExtension;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * SonataAdminBundleExtension.
 *
 * @author      Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * @author      Michael Williams <michael.williams@funsational.com>
 * @author      Nacho Martín <nitram.ohcan@gmail.com>
 */
class SonataDoctrinePHPCRAdminExtension extends AbstractSonataAdminExtension
{
    /**
     * @param array            $configs   An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $defaultConfig = [
            'templates' => [
                'types' => [
                    'list' => [
                        'node' => '@SonataDoctrinePHPCRAdmin/CRUD/list_node.html.twig',
                    ],
                    'show' => [
                        'doctrine_phpcr_many_to_many' => '@SonataAdmin/CRUD/Association/show_many_to_many.html.twig',
                        'doctrine_phpcr_many_to_one' => '@SonataAdmin/CRUD/Association/show_many_to_one.html.twig',
                        'doctrine_phpcr_one_to_many' => '@SonataAdmin/CRUD/Association/show_one_to_many.html.twig',
                        'doctrine_phpcr_one_to_one' => '@SonataAdmin/CRUD/Association/show_one_to_one.html.twig',
                    ],
                ],
            ],
        ];

        $configs = $this->fixTemplatesConfiguration($configs, $container, $defaultConfig);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('doctrine_phpcr.xml');
        $loader->load('doctrine_phpcr_filter_types.xml');
        $loader->load('doctrine_phpcr_form_types.xml');
        $loader->load('form.xml');
        $loader->load('route.xml');
        $loader->load('twig.xml');
        $loader->load('block.xml');
        $loader->load('tree.xml');
        $loader->load('autocomplete.xml');

        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        $pool = $container->getDefinition('sonata.admin.manager.doctrine_phpcr');
        $pool->addMethodCall('__hack_doctrine_phpcr__', $config);

        $container->getDefinition('sonata.admin.builder.doctrine_phpcr_list')
            ->replaceArgument(1, $config['templates']['types']['list']);

        $container->getDefinition('sonata.admin.builder.doctrine_phpcr_show')
            ->replaceArgument(1, $config['templates']['types']['show']);

        if ($this->isConfigEnabled($container, $config['document_tree'])) {
            $this->loadDocumentTree($config['document_tree'], $container);
        }
    }

    public function getNamespace()
    {
        return 'http://sonata-project.org/schema/dic/doctrine_phpcr_admin';
    }

    /**
     * Set the document tree parameters and configuration.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function loadDocumentTree($config, ContainerBuilder $container): void
    {
        $configuration = [
            'routing_defaults' => $config['routing_defaults'],
            'repository_name' => $config['repository_name'],
            'sortable_by' => $config['sortable_by'],
            'move' => true,
            'reorder' => true,
        ];

        $container->setParameter('sonata_admin_doctrine_phpcr.tree_block.configuration', $configuration);

        foreach ($configuration as $key => $value) {
            $container->setParameter('sonata_admin_doctrine_phpcr.tree_block.'.$key, $value);
        }
    }
}
