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

namespace Sonata\DoctrinePHPCRAdminBundle\Block;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TreeBlockService extends AbstractBlockService
{
    /**
     * @var array
     */
    protected $defaults;

    /**
     * @param string          $name
     * @param EngineInterface $templating
     * @param array           $defaults
     */
    public function __construct($name, EngineInterface $templating, array $defaults = [])
    {
        parent::__construct($name, $templating);
        $this->defaults = $defaults;
    }

    /**
     * {@inheritdoc}
     *
     * NOOP as there is nothing to edit.
     */
    public function buildEditForm(FormMapper $form, BlockInterface $block): void
    {
        // there is nothing to edit here
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
        // the callables are a workaround to make bundle configuration win over the default values
        // see https://github.com/sonata-project/SonataDoctrinePhpcrAdminBundle/pull/345
        $resolver->setDefaults([
            'template' => function (Options $options, $value) {
                return $value ?: '@SonataDoctrinePHPCRAdmin/Block/tree.html.twig';
            },
            'id' => function (Options $options, $value) {
                return $value ?: '/';
            },
            'selected' => function (Options $options, $value) {
                return $value ?: null;
            },
            'routing_defaults' => function (Options $options, $value) {
                return $value ?: $this->defaults;
            },
        ]);
    }
}
