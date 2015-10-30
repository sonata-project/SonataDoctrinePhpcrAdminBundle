<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Twig\Extension;

use PHPCR\NodeInterface;

class SonataDoctrinePHPCRAdminExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'render_node_property' => new \Twig_SimpleFilter(array($this, 'renderNodeProperty'), array('is_safe' => array('html'))),
            'render_node_path'     => new \Twig_SimpleFilter(array($this, 'renderNodePath'), array('is_safe' => array('html'))),
        );
    }

    /**
     * Renders a property of a node.
     *
     * @param NodeInterface $node
     * @param string        $property
     *
     * @return string String representation of the property
     */
    public function renderNodeProperty(NodeInterface $node, $property)
    {
        return $node->getProperty($property)->getString();
    }

    /**
     * Renders a path of a node.
     *
     * @param NodeInterface $node
     *
     * @return string Node path
     */
    public function renderNodePath(NodeInterface $node)
    {
        return $node->getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonata_doctrine_phpcr_admin';
    }
}
