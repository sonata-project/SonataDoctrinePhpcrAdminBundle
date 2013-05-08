<?php

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
    protected $defaults;

    public function __construct($name, EngineInterface $templating, array $defaults = array())
    {
        parent::__construct($name, $templating);
        $this->defaults = $defaults;
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $form
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     *
     * @return void
     */
    public function buildEditForm(FormMapper $form, BlockInterface $block)
    {
        // there is nothing to edit here!
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        return $this->templating->renderResponse($blockContext->getTemplate(), $blockContext->getSettings(), $response);
    }

    /**
     * {@inheritdoc}
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
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @return void
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        // there is nothing to validate here
    }


}
