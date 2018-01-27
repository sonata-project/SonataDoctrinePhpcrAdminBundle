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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle.
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
            ->fixXmlConfig('template')
            ->children()
                ->arrayNode('templates')
                    ->children()
                        ->arrayNode('form')
                            ->prototype('scalar')->end()
                            ->defaultValue(['@SonataDoctrinePHPCRAdmin/Form/form_admin_fields.html.twig'])
                        ->end()
                        ->arrayNode('filter')
                            ->prototype('scalar')->end()
                            ->defaultValue(['@SonataDoctrinePHPCRAdmin/Form/filter_admin_fields.html.twig'])
                        ->end()
                        ->arrayNode('types')
                            ->children()
                                ->arrayNode('list')
                                    ->useAttributeAsKey('name')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('show')
                                    ->useAttributeAsKey('name')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->scalarNode('pager_results')->defaultValue('@SonataDoctrinePHPCRAdmin/Pager/simple_pager_results.html.twig')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('document_tree')
                    ->addDefaultsIfNotSet()
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('repository_name')
                            ->defaultNull()
                            ->info('The repository name the resource API connects to.')
                        ->end()
                        ->arrayNode('routing_defaults')
                            ->prototype('scalar')->end()
                            ->info('Routing defaults passed to the resources API call.')
                        ->end()
                        ->scalarNode('sortable_by')
                            ->defaultValue('position')
                            ->info('Defines by which property to sort sibling documents.')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
