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

use Sonata\DoctrinePHPCRAdminBundle\Admin\FieldDescription;

class FormContractor implements FormContractorInterface
{
    protected $fieldFactory;

    /**
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * The method defines the correct default settings for the provided FieldDescription
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return void
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription)
    {
        $metadata = null;
        if ($admin->getModelManager()->hasMetadata($admin->getClass())) {
            /** @var Doctrine\ODM\PHPCR\Mapping\ClassMetadata $metadata */
            $metadata = $admin->getModelManager()->getMetadata($admin->getClass());
            
            // set the default field mapping
            if (isset($metadata->mappings[$fieldDescription->getName()])) {
                $fieldDescription->setFieldMapping($metadata->mappings[$fieldDescription->getName()]);
            }

            // set the default association mapping
            if (isset($metadata->referrersMappings[$fieldDescription->getName()])) {
                $fieldDescription->setAssociationMapping($metadata->referrersMappings[$fieldDescription->getName()]);
            }
        }

        if (!$fieldDescription->getType()) {
            throw new \RuntimeException(sprintf('Please define a type for field `%s` in `%s`', $fieldDescription->getName(), get_class($admin)));
        }

        $fieldDescription->setAdmin($admin);
        $fieldDescription->setOption('edit', $fieldDescription->getOption('edit', 'standard'));
        
        $mappingTypes = array(
            ClassMetadata::MANY_TO_ONE,
            ClassMetadata::MANY_TO_MANY
        );


        if ($metadata && isset($metadata->referrersMappings[$fieldDescription->getName()]) && in_array($fieldDescription->getMappingType(), $mappingTypes)) {
            $admin->attachAdminClass($fieldDescription);
        }
    }

    /**
     * @return \Symfony\Component\Form\FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @param string $name
     * @param array $options
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function getFormBuilder($name, array $options = array())
    {
        return $this->getFormFactory()->createNamedBuilder($name, 'form', null, $options);
    }

    /**
     * @param $type
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return array
     */
    public function getDefaultOptions($type, FieldDescriptionInterface $fieldDescription)
    {
        $options = array();
        $options['sonata_field_description'] = $fieldDescription;

        if ($type == 'doctrine_phpcr_type_tree_model') {
            $options['class']         = $fieldDescription->getTargetEntity();
            $options['model_manager'] = $fieldDescription->getAdmin()->getModelManager();
        }

        if ($type == 'sonata_type_model') {
            $options['class']         = $fieldDescription->getTargetEntity();
            $options['model_manager'] = $fieldDescription->getAdmin()->getModelManager();

            switch ($fieldDescription->getMappingType()) {
                case ClassMetadata::MANY_TO_MANY:
                    $options['multiple']            = true;
                    $options['parent']              = 'choice';
                    break;

                case ClassMetadata::MANY_TO_ONE:
                    break;
            }

            if ($fieldDescription->getOption('edit') == 'list') {
                $options['parent'] = 'text';

                if (!array_key_exists('required', $options)) {
                    $options['required'] = false;
                }
            }

        } else if ($type == 'sonata_type_admin') {

            // nothing here ...
            $options['edit'] = 'inline';

        } else if ($type == 'sonata_type_collection') {

            $options['type']         = 'sonata_type_admin';
            $options['modifiable']   = true;
            $options['type_options'] = array(
                'sonata_field_description' => $fieldDescription,
                'data_class'               => $fieldDescription->getAssociationAdmin()->getClass()
            );

        }

        return $options;
    }
}
