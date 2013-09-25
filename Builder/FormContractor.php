<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Builder;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;

use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;

class FormContractor implements FormContractorInterface
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * The method defines the correct default settings for the provided FieldDescription
     *
     * {@inheritDoc}
     *
     * @throws \RuntimeException if the $fieldDescription does not specify a type.
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription)
    {
        $metadata = null;
        if ($admin->getModelManager()->hasMetadata($admin->getClass())) {
            /** @var \Doctrine\ODM\PHPCR\Mapping\ClassMetadata $metadata */
            $metadata = $admin->getModelManager()->getMetadata($admin->getClass());

            // set the default field mapping
            if (isset($metadata->mappings[$fieldDescription->getName()])) {
                $fieldDescription->setFieldMapping($metadata->mappings[$fieldDescription->getName()]);
            }

            // set the default association mapping
            if ($metadata->hasAssociation($fieldDescription->getName())) {
                $fieldDescription->setAssociationMapping($metadata->getAssociation($fieldDescription->getName()));
            }
        }

        if (!$fieldDescription->getType()) {
            throw new \RuntimeException(sprintf('Please define a type for field `%s` in `%s`', $fieldDescription->getName(), get_class($admin)));
        }

        $fieldDescription->setAdmin($admin);
        $fieldDescription->setOption('edit', $fieldDescription->getOption('edit', 'standard'));

        $mappingTypes = array(
            ClassMetadata::MANY_TO_ONE,
            ClassMetadata::MANY_TO_MANY,
            'children',
            'child', 'parent',
            'referrers',
        );

        if ($metadata && $metadata->hasAssociation($fieldDescription->getName()) && in_array($fieldDescription->getMappingType(), $mappingTypes)) {
            $admin->attachAdminClass($fieldDescription);
        }
    }

    /**
     * @return FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormBuilder($name, array $options = array())
    {
        return $this->getFormFactory()->createNamedBuilder($name, 'form', null, $options);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \LogicException if a sonata_type_model field does not have a
     *                         target model configured.
     */
    public function getDefaultOptions($type, FieldDescriptionInterface $fieldDescription)
    {
        $options = array();
        $options['sonata_field_description'] = $fieldDescription;

        if ($type == 'doctrine_phpcr_odm_tree') {
            $options['class']         = $fieldDescription->getTargetEntity();
            $options['model_manager'] = $fieldDescription->getAdmin()->getModelManager();
        }

        if ($type == 'sonata_type_model' || $type == 'sonata_type_model_list') {
            if (!$fieldDescription->getTargetEntity()) {
                throw new \LogicException(sprintf('The current field `%s` does not have a target model defined. Please specify the `targetDocument`
                    in the Reference.', $fieldDescription->getName()));
            }

            $options['class']         = $fieldDescription->getTargetEntity();
            $options['model_manager'] = $fieldDescription->getAdmin()->getModelManager();

        } else if ($type == 'sonata_type_admin') {

            if (!$fieldDescription->getAssociationAdmin()) {
                throw $this->getAssociationAdminException($fieldDescription);
            }

            $options['data_class'] = $fieldDescription->getAssociationAdmin()->getClass();
            $fieldDescription->setOption('edit', $fieldDescription->getOption('edit', 'admin'));

        } else if ($type == 'sonata_type_collection') {

            if (!$fieldDescription->getAssociationAdmin()) {
                throw $this->getAssociationAdminException($fieldDescription);
            }

            $options['type']         = 'sonata_type_admin';
            $options['modifiable']   = true;
            $options['type_options'] = array(
                'sonata_field_description' => $fieldDescription,
                'data_class'               => $fieldDescription->getAssociationAdmin()->getClass()
            );

        }

        return $options;
    }

    /**
     * @param FieldDescriptionInterface $fieldDescription
     *
     * @return \LogicException
     */
    protected function getAssociationAdminException(FieldDescriptionInterface $fieldDescription)
    {
        $msg = sprintf('The current field `%s` is not linked to an admin. Please create one', $fieldDescription->getName());
        if (in_array($fieldDescription->getMappingType(), array(ClassMetadata::MANY_TO_ONE, ClassMetadata::MANY_TO_MANY, 'referrers'))) {
            if ($fieldDescription->getTargetEntity()) {
                $msg .= " for the target document: `{$fieldDescription->getTargetEntity()}`";
            }
            $msg .= ", specify the `targetDocument` in the Reference, or the `referringDocument` in the Referrers or use the option `admin_code` to link it.";
        } else {
            $msg .= ' and use the option `admin_code` to link it.';
        }

        return new \LogicException($msg);
    }
}
