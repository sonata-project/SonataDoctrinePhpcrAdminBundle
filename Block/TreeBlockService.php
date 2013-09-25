<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Block;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TreeBlockService extends BaseBlockService
{
    /**
     * @var array
     */
    protected $defaults;

    /**
     * @param string $name
     * @param EngineInterface $templating
     * @param array $defaults
     */
    public function __construct($name, EngineInterface $templating, array $defaults = array())
    {
        parent::__construct($name, $templating);
        $this->defaults = $defaults;
    }

    /**
     * {@inheritDoc}
     *
     * NOOP as there is nothing to edit.
     */
    public function buildEditForm(FormMapper $form, BlockInterface $block)
    {
        // there is nothing to edit here!
    }

    /**
     * {@inheritDoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        return $this->templating->renderResponse($blockContext->getTemplate(), $blockContext->getSettings(), $response);
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'template'         => 'SonataDoctrinePHPCRAdminBundle:Block:tree.html.twig',
            'id'               => '/',
            'selected'         => null,
            'routing_defaults' => $this->defaults,
        ));
    }

    /**
     * {@inheritDoc}
     *
     * NOOP as we do not edit and hence have nothing to validate.
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        // there is nothing to validate here
    }
}
