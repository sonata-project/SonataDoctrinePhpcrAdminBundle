<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Builder;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\AbstractFormContractor;

class FormContractor extends AbstractFormContractor
{
    /**
     * The method defines the correct default settings for the provided FieldDescription.
     *
     * {@inheritdoc}
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

        if ($fieldDescription->describesAssociation()) {
            $admin->attachAdminClass($fieldDescription);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions($type, FieldDescriptionInterface $fieldDescription)
    {
        $options = parent::getDefaultOptions($type, $fieldDescription);

        switch ($type) {
            case 'Sonata\DoctrinePHPCRAdminBundle\Form\Type\TreeModelType':
            case 'doctrine_phpcr_odm_tree':
                $options['class'] = $fieldDescription->getTargetEntity();
                $options['model_manager'] = $fieldDescription->getAdmin()->getModelManager();
                break;
        }

        return $options;
    }
}
