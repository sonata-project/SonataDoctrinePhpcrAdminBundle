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

namespace Sonata\DoctrinePHPCRAdminBundle;

use Sonata\CoreBundle\Form\FormHelper;
use Sonata\DoctrinePHPCRAdminBundle\DependencyInjection\Compiler\AddGuesserCompilerPass;
use Sonata\DoctrinePHPCRAdminBundle\DependencyInjection\Compiler\AddTemplatesCompilerPass;
use Sonata\DoctrinePHPCRAdminBundle\DependencyInjection\Compiler\AddTreeBrowserAssetsPass;
use Sonata\DoctrinePHPCRAdminBundle\Form\Type\ChoiceFieldMaskType;
use Sonata\DoctrinePHPCRAdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\DoctrinePHPCRAdminBundle\Form\Type\TreeManagerType;
use Sonata\DoctrinePHPCRAdminBundle\Form\Type\TreeModelType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SonataDoctrinePHPCRAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $this->registerFormMapping();

        $container->addCompilerPass(new AddGuesserCompilerPass());
        $container->addCompilerPass(new AddTemplatesCompilerPass());
        $container->addCompilerPass(new AddTreeBrowserAssetsPass());
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->registerFormMapping();
    }

    private function registerFormMapping()
    {
        FormHelper::registerFormTypeMapping([
            'doctrine_phpcr_type_filter_choice' => ChoiceType::class,
            'choice_field_mask' => ChoiceFieldMaskType::class,
            'doctrine_phpcr_odm_tree_manager' => TreeManagerType::class,
            'doctrine_phpcr_odm_tree' => TreeModelType::class,
        ]);
    }
}
