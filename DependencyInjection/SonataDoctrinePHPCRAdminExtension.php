<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Definition\Processor;


/**
 * SonataAdminBundleExtension
 *
 * @author      Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * @author      Michael Williams <michael.williams@funsational.com>
 * @author      Nacho Martín <nitram.ohcan@gmail.com>
 */
class SonataDoctrinePHPCRAdminExtension extends Extension
{
    /**
     *
     * @param array            $configs    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $defaultConfig = array(
            'templates' => array(
                'types' => array(
                    'list' => array(
                        'array'        => 'SonataAdminBundle:CRUD:list_array.html.twig',
                        'boolean'      => 'SonataAdminBundle:CRUD:list_boolean.html.twig',
                        'date'         => 'SonataAdminBundle:CRUD:list_date.html.twig',
                        'datetime'     => 'SonataAdminBundle:CRUD:list_datetime.html.twig',
                        'text'         => 'SonataAdminBundle:CRUD:base_list_field.html.twig',
                        'string'       => 'SonataAdminBundle:CRUD:base_list_field.html.twig',
                        'smallint'     => 'SonataAdminBundle:CRUD:base_list_field.html.twig',
                        'bigint'       => 'SonataAdminBundle:CRUD:base_list_field.html.twig',
                        'integer'      => 'SonataAdminBundle:CRUD:base_list_field.html.twig',
                        'decimal'      => 'SonataAdminBundle:CRUD:base_list_field.html.twig',
                        'identifier'   => 'SonataAdminBundle:CRUD:base_list_field.html.twig',
                        'node'         => 'SonataDoctrinePHPCRAdminBundle:CRUD:list_node.html.twig'
                    )
                )
            )
        );

        // let's add some magic, only overwrite template if the SonataIntlBundle is enabled
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['SonataIntlBundle'])) {
            $defaultConfig['templates']['types']['list'] = array_merge($defaultConfig['templates']['types']['list'], array(
                'date'         => 'SonataIntlBundle:CRUD:list_date.html.twig',
                'datetime'     => 'SonataIntlBundle:CRUD:list_datetime.html.twig',
                'smallint'     => 'SonataIntlBundle:CRUD:list_decimal.html.twig',
                'bigint'       => 'SonataIntlBundle:CRUD:list_decimal.html.twig',
                'integer'      => 'SonataIntlBundle:CRUD:list_decimal.html.twig',
                'decimal'      => 'SonataIntlBundle:CRUD:list_decimal.html.twig',
            ));
        }

        array_unshift($configs, $defaultConfig);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('doctrine_phpcr.xml');
        $loader->load('doctrine_phpcr_filter_types.xml');
        $loader->load('doctrine_phpcr_form_types.xml');
        $loader->load('form.xml');
        $loader->load('route.xml');
        $loader->load('twig.xml');
        $loader->load('block.xml');
        $loader->load('tree.xml');

        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        $pool = $container->getDefinition('sonata.admin.manager.doctrine_phpcr');
        $pool->addMethodCall('__hack_doctrine_phpcr__', $config);

        $container->getDefinition('sonata.admin.builder.doctrine_phpcr_list')
            ->replaceArgument(1, $config['templates']['types']['list']);

        $this->loadTreeTypes($config, $container);
    }

    /**
     * Set the tree type mapping configuration in the services
     *
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function loadTreeTypes($config, ContainerBuilder $container)
    {
        $container->setParameter('sonata_admin_doctrine_phpcr.tree_block.defaults', $config['document_tree_defaults']);
        $container->setParameter('sonata_admin_doctrine_phpcr.tree_confirm_move', $config['confirm_move']);
        $container->getDefinition('sonata.admin.doctrine_phpcr.phpcrodm_tree')
            ->replaceArgument(5, $config['document_tree']);
    }
}

