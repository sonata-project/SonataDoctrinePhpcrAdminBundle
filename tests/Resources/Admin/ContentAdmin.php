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

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Resources\Admin;

use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\DocumentToPathTransformer;
use Doctrine\Common\Persistence\ManagerRegistry;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrinePHPCRAdminBundle\Admin\Admin;
use Sonata\DoctrinePHPCRAdminBundle\Tests\Resources\Document\Content;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class ContentAdmin extends Admin
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function setManagerRegistry(ManagerRegistry $managerRegistry): void
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function getExportFormats()
    {
        return [];
    }

    public function toString($object)
    {
        return $object instanceof Content && $object->getTitle()
            ? $object->getTitle()
            : $this->trans('link_add', [], 'SonataAdminBundle');
    }

    public function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->tab('General') // the tab call is optional
                ->with('Content', [
                    'class' => 'col-md-8',
                    'box_class' => 'box box-solid box-danger',
                    'description' => 'Main Content',
                ])
                    ->add('title')
                    ->add('name')
                ->end()
                ->with('References')
                    ->add('children', null, [
                        'route' => ['name' => 'edit', 'parameters' => []],
                        'associated_property' => 'id',
                        'admin_code' => 'sonata_admin_doctrine_phpcr.test.admin',
                        ])
                    ->add('child', null, [
                        'route' => ['name' => 'edit', 'parameters' => []],
                        'associated_property' => 'id',
                        'admin_code' => 'sonata_admin_doctrine_phpcr.test.admin',
                    ])
                    ->add(
                        'singleRoute',
                        null,
                        ['route' => ['name' => 'edit', 'parameters' => []], 'associated_property' => 'id']
                    )
                    ->add(
                        'routes',
                        null,
                        ['route' => ['name' => 'edit', 'parameters' => []], 'associated_property' => 'id']
                    )
                ->end()
            ->end()
        ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('id')
            ->add('title');
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->with('form.group_general')
            ->add('name', 'Symfony\Component\Form\Extension\Core\Type\TextType')
            ->add('title', 'Symfony\Component\Form\Extension\Core\Type\TextType')
            ->add(
                'children',
                'Sonata\CoreBundle\Form\Type\CollectionType',
                ['label' => false, 'type_options' => [
                    'delete' => true,
                    'delete_options' => [
                        'type' => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
                        'type_options' => ['required' => false, 'mapped' => false],
                    ], ],
                ],
                ['edit' => 'inline', 'inline' => 'table', 'admin_code' => 'sonata_admin_doctrine_phpcr.test.admin']
            )
            ->add(
                'routes',
                'Sonata\AdminBundle\Form\Type\ModelType',
                ['property' => 'title', 'multiple' => true, 'expanded' => false]
            )
            ->add('parentDocument', 'Symfony\Cmf\Bundle\TreeBrowserBundle\Form\Type\TreeSelectType', [
                'widget' => 'browser',
                'root_node' => $this->getRootPath(),
            ])
            ->add(
                'child',
                'Sonata\AdminBundle\Form\Type\ModelType',
                [
                    'property' => 'title',
                    'class' => 'Sonata\DoctrinePHPCRAdminBundle\Tests\Resources\Document\Content',
                    'btn_catalogue' => 'List',
                    'required' => false,
                ],
                ['admin_code' => 'sonata_admin_doctrine_phpcr.test.admin']
            )
            ->add('singleRoute', 'Symfony\Cmf\Bundle\TreeBrowserBundle\Form\Type\TreeSelectType', [
                'widget' => 'browser',
                'root_node' => $this->getRootPath(),
            ])
            ->end();

        $formMapper->getFormBuilder()->get('parentDocument')->addModelTransformer(new DocumentToPathTransformer(
            $this->managerRegistry->getManagerForClass($this->getClass())
        ));

        $formMapper->getFormBuilder()->get('singleRoute')->addModelTransformer(new DocumentToPathTransformer(
            $this->managerRegistry->getManagerForClass($this->getClass())
        ));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('title', 'doctrine_phpcr_string')
            ->add('name', 'doctrine_phpcr_nodename');
    }
}
