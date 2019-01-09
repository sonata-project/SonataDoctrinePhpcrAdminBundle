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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SonataDoctrinePHPCRAdminBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container): void
    {
        $this->registerFormMapping();

        $container->addCompilerPass(new AddGuesserCompilerPass());
        $container->addCompilerPass(new AddTemplatesCompilerPass());
        $container->addCompilerPass(new AddTreeBrowserAssetsPass());
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        $this->registerFormMapping();
    }

    private function registerFormMapping(): void
    {
        FormHelper::registerFormTypeMapping([
            'doctrine_phpcr_type_filter_choice' => 'Sonata\DoctrinePHPCRAdminBundle\Form\Type\Filter\ChoiceType',
            'choice_field_mask' => 'Sonata\DoctrinePHPCRAdminBundle\Form\Type\ChoiceFieldMaskType',
            'doctrine_phpcr_odm_tree_manager' => 'Sonata\DoctrinePHPCRAdminBundle\Form\Type\TreeManagerType',
            'doctrine_phpcr_odm_tree' => 'Sonata\DoctrinePHPCRAdminBundle\Form\Type\TreeModelType',
        ]);
    }
}
