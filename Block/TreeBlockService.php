<?php

namespace Sonata\DoctrinePHPCRAdminBundle\Block;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Model\BlockInterface;

class TreeBlockService extends BaseBlockService
{
    protected $templating;

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
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @param null|\Symfony\Component\HttpFoundation\Response $response
     *
     * @return Response
     */
    public function execute(BlockInterface $block, Response $response = null)
    {
        $options = array_merge(array('id' => '/', 'selected' => null), array('routing_defaults' => $this->defaults));
        if (null !== $block->getSettings()) {
            $options = array_merge($options, $block->getSettings());
        }
        return $this->templating->renderResponse('SonataDoctrinePHPCRAdminBundle:Block:tree.html.twig', $options, $response);
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
