<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
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

    public function setManagerRegistry(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function getExportFormats()
    {
        return array();
    }

    public function toString($object)
    {
        return $object instanceof Content && $object->getTitle()
            ? $object->getTitle()
            : $this->trans('link_add', array(), 'SonataAdminBundle');
    }

    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->tab('General') // the tab call is optional
                ->with('Content', array(
                    'class' => 'col-md-8',
                    'box_class' => 'box box-solid box-danger',
                    'description' => 'Main Content',
                ))
                    ->add('title')
                    ->add('name')
                ->end()
                ->with('References')
                    ->add('children', null, array(
                        'route' => array('name' => 'edit', 'parameters' => array()),
                        'associated_property' => 'id',
                        'admin_code' => 'sonata_admin_doctrine_phpcr.test.admin',
                        ))
                    ->add('child', null, array(
                        'route' => array('name' => 'edit', 'parameters' => array()),
                        'associated_property' => 'id',
                        'admin_code' => 'sonata_admin_doctrine_phpcr.test.admin',
                    ))
                    ->add(
                        'singleRoute',
                        null,
                        array('route' => array('name' => 'edit', 'parameters' => array()), 'associated_property' => 'id')
                    )
                    ->add(
                        'routes',
                        null,
                        array('route' => array('name' => 'edit', 'parameters' => array()), 'associated_property' => 'id')
                    )
                ->end()
            ->end()
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('title');
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('form.group_general')
            ->add('name', 'Symfony\Component\Form\Extension\Core\Type\TextType')
            ->add('title', 'Symfony\Component\Form\Extension\Core\Type\TextType')
            ->add(
                'children',
                'Sonata\CoreBundle\Form\Type\CollectionType',
                array('label' => false, 'type_options' => array(
                    'delete' => true,
                    'delete_options' => array(
                        'type' => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
                        'type_options' => array('required' => false, 'mapped' => false),
                    ), ),
                ),
                array('edit' => 'inline', 'inline' => 'table', 'admin_code' => 'sonata_admin_doctrine_phpcr.test.admin')
            )
            ->add(
                'routes',
                'Sonata\AdminBundle\Form\Type\ModelType',
                array('property' => 'title', 'multiple' => true, 'expanded' => false)
            )
            ->add('parentDocument', 'Symfony\Cmf\Bundle\TreeBrowserBundle\Form\Type\TreeSelectType', array(
                'widget' => 'browser',
                'root_node' => $this->getRootPath(),
            ))
            ->add(
                'child',
                'Sonata\AdminBundle\Form\Type\ModelType',
                array(
                    'property' => 'title',
                    'class' => 'Sonata\DoctrinePHPCRAdminBundle\Tests\Resources\Document\Content',
                    'btn_catalogue' => 'List',
                    'required' => false,
                ),
                array('admin_code' => 'sonata_admin_doctrine_phpcr.test.admin')
            )
            ->add('singleRoute', 'Symfony\Cmf\Bundle\TreeBrowserBundle\Form\Type\TreeSelectType', array(
                'widget' => 'browser',
                'root_node' => $this->getRootPath(),
            ))
            ->end();

        $formMapper->getFormBuilder()->get('parentDocument')->addModelTransformer(new DocumentToPathTransformer(
            $this->managerRegistry->getManagerForClass($this->getClass())
        ));

        $formMapper->getFormBuilder()->get('singleRoute')->addModelTransformer(new DocumentToPathTransformer(
            $this->managerRegistry->getManagerForClass($this->getClass())
        ));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', 'doctrine_phpcr_string')
            ->add('name', 'doctrine_phpcr_nodename');
    }
}
