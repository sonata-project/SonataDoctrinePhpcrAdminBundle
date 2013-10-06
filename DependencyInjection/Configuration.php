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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Michael Williams <mtotheikle@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sonata_doctrine_phpcr_admin', 'array');

        $rootNode
            ->fixXmlConfig('document_tree_default')
            ->fixXmlConfig('template')
            ->children()
                ->arrayNode('document_tree_defaults')
                    ->prototype('scalar')->end()
                ->end()

                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('form')
                            ->prototype('scalar')->end()
                            ->defaultValue(array('SonataDoctrinePHPCRAdminBundle:Form:form_admin_fields.html.twig'))
                        ->end()
                        ->arrayNode('filter')
                            ->prototype('scalar')->end()
                            ->defaultValue(array('SonataDoctrinePHPCRAdminBundle:Form:filter_admin_fields.html.twig'))
                        ->end()
                        ->arrayNode('types')
                            ->children()
                                ->arrayNode('list')
                                    ->useAttributeAsKey('name')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->scalarNode('pager_results')->defaultValue('SonataDoctrinePHPCRAdminBundle:Pager:simple_pager_results.html.twig')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('document_tree')
                    ->useAttributeAsKey('class')
                    ->prototype('array')
                        ->fixXmlConfig('valid_child', 'valid_children')
                        ->children()
                            ->arrayNode('valid_children')
                                ->prototype('scalar')->end()
                                ->info('class names of valid children, manage tree operations for them and hide other children')
                            ->end()
                            ->scalarNode('image')
                                ->defaultValue('')
                            ->end()
                        ->end()
                    ->end()
                 ->end()
                 ->booleanNode('confirm_move')->defaultFalse()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

