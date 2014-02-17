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
use Sonata\AdminBundle\DependencyInjection\AbstractSonataAdminExtension;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

/**
 * SonataAdminBundleExtension
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
    public function load(array $configs, ContainerBuilder $container)
    {
        $defaultConfig = array(
            'templates' => array(
                'types' => array(
                    'list' => array(
                        'node'         => 'SonataDoctrinePHPCRAdminBundle:CRUD:list_node.html.twig'
                    )
                )
            )
        );

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
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function loadTreeTypes($config, ContainerBuilder $container)
    {
        $options = $config['document_tree_options'];
        $container->setParameter('sonata_admin_doctrine_phpcr.tree_block.defaults', $config['document_tree_defaults']);
        $container->setParameter('sonata_admin_doctrine_phpcr.tree_confirm_move', $options['confirm_move']);
        unset($options['confirm_move']);
        $container->getDefinition('sonata.admin.doctrine_phpcr.phpcr_odm_tree')
            ->replaceArgument(5, $this->processDocumentTreeConfig($config['document_tree']));
        $container->getDefinition('sonata.admin.doctrine_phpcr.phpcr_odm_tree')
            ->replaceArgument(6, $options);
    }

    /**
     * Process the document tree config
     * Expand references to 'all' to an array of all types
     * Validate document types
     *
     * @param array $documentTree
     */
    private function processDocumentTreeConfig(array $documentTree)
    {
        $docClasses = $this->findAllDocumentClasses($documentTree);

        // Validate all document classes
        $invalidClasses = array_filter(
            $docClasses,
            function ($class) {
                return false === class_exists($class);
            }
        );
        if (count($invalidClasses)) {
            throw new \InvalidArgumentException(sprintf(
                'The following document types provided in valid_children are invalid: %s '.
                'The class names provided could not be loaded.',
                implode(', ', array_unique($invalidClasses))
            ));
        }

        // Process the config
        $processed = array();
        foreach ($documentTree as $docClass => $config) {
            // Expand 'all'
            if (false !== array_search('all', $config['valid_children'])) {
                $config['valid_children'] = $docClasses;
            }

            $processed[$docClass] = $config;
        }

        return $processed;
    }

    /**
     * Find all document classes within a document tree
     *
     * @param array $documentTree
     */
    private function findAllDocumentClasses(array $documentTree)
    {
        $documentClasses = array_unique(array_reduce(
            $documentTree,
            function ($result, $config) {
                return array_merge($result, $config['valid_children']);
            },
            array_keys($documentTree)
        ));

        if (false !== ($allIndex = array_search('all', $documentClasses))) {
            unset($documentClasses[$allIndex]);
        }

        return $documentClasses;
    }

    public function getNamespace()
    {
        return 'http://sonata-project.org/schema/dic/doctrine_phpcr_admin';
    }
}
