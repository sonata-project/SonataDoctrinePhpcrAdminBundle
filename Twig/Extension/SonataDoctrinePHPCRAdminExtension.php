<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Twig\Extension;

use PHPCR\NodeInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;

class SonataDoctrinePHPCRAdminExtension extends \Twig_Extension
{
    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonata_doctrine_phpcr_admin';
    }

    /**
     * render a list element from the FieldDescription.
     *
     * @param object                    $object
     * @param FieldDescriptionInterface $fieldDescription
     * @param array                     $params
     *
     * @return string
     */
    public function renderListElement($object, FieldDescriptionInterface $fieldDescription, $params = array())
    {
        $template = $this->getTemplate($fieldDescription, 'SonataAdminBundle:CRUD:base_list_field.html.twig');

        return $this->output($fieldDescription, $template, array_merge($params, array(
            'admin' => $fieldDescription->getAdmin(),
            'object' => $object,
            'value' => $this->getValueFromFieldDescription($object, $fieldDescription),
            'field_description' => $fieldDescription,
        )));
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'render_node_property' => new \Twig_Filter_Method($this, 'renderNodeProperty', array('is_safe' => array('html'))),
            'render_node_path' => new \Twig_Filter_Method($this, 'renderNodePath', array('is_safe' => array('html'))),
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
}
