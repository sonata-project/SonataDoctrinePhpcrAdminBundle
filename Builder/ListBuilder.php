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

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;

use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;

class ListBuilder implements ListBuilderInterface
{
    protected $guesser;

    protected $templates = array();

    /**
     * @param \Sonata\AdminBundle\Guesser\TypeGuesserInterface $guesser
     * @param array $templates
     */
    public function __construct(TypeGuesserInterface $guesser, $templates = array())
    {
        $this->guesser = $guesser;
        $this->templates = $templates;
    }

    /**
     * Returns an empty field description collection
     *
     * @param array $options
     * @return FieldDescriptionCollection
     */
    public function getBaseList(array $options = array())
    {
        return new FieldDescriptionCollection;
    }

    /**
     * Adds a field to the Field description collection and sets its type.
     * If not type provided, will try to guess it.
     *
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionCollection $list
     * @param string|null $type
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @return FieldDescriptionCollection
     */
    public function addField(FieldDescriptionCollection $list, $type = null, FieldDescriptionInterface $fieldDescription, AdminInterface $admin)
    {
        $this->buildField($type, $fieldDescription, $admin);
        $admin->addListFieldDescription($fieldDescription->getName(), $fieldDescription);

        return $list->add($fieldDescription);
    }

    /**
     * Sets the Field description type.
     * If not type provided, will try to guess it.
     *
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionCollection $list
     * @param string|null $type
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @return FieldDescriptionCollection
     */
    public function buildField($type = null, FieldDescriptionInterface $fieldDescription, AdminInterface $admin)
    {
        if ($type == null) {
            $guessType = $this->guesser->guessType($admin->getClass(), $fieldDescription->getName(), $admin->getModelManager());
            $fieldDescription->setType($guessType->getType());
        } else {
            $fieldDescription->setType($type);
        }

        $this->fixFieldDescription($admin, $fieldDescription);
    }

    /**
     * The method defines the correct default settings for the provided
     * FieldDescription
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return void
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription)
    {
        if ($fieldDescription->getName() == '_action') {
            $this->buildActionFieldDescription($fieldDescription);
        }

        $fieldDescription->setAdmin($admin);

        if ($admin->getModelManager()->hasMetadata($admin->getClass())) {
            $metadata = $admin->getModelManager()->getMetadata($admin->getClass());

            // set the default field mapping
            if (isset($metadata->fieldMappings[$fieldDescription->getName()])) {
                $fieldDescription->setFieldMapping($metadata->fieldMappings[$fieldDescription->getName()]);
                if ($fieldDescription->getOption('sortable') !== false) {
                    $fieldDescription->setOption('sortable', $fieldDescription->getOption('sortable', $fieldDescription->getName()));
                    $fieldDescription->setOption('sort_parent_association_mappings', $fieldDescription->getOption('sort_parent_association_mappings', $fieldDescription->getParentAssociationMappings()));
                    $fieldDescription->setOption('sort_field_mapping', $fieldDescription->getOption('sort_field_mapping', $fieldDescription->getFieldMapping()));
                }
            }

            // set the default association mapping
            if (isset($metadata->associationMappings[$fieldDescription->getName()])) {
                $fieldDescription->setAssociationMapping($metadata->associationMappings[$fieldDescription->getName()]);
            }

            $fieldDescription->setOption('_sort_order', $fieldDescription->getOption('_sort_order', 'ASC'));
        }

        if (!$fieldDescription->getType()) {
            throw new \RuntimeException(sprintf('Please define a type for field `%s` in `%s`', $fieldDescription->getName(), get_class($admin)));
        }

        $fieldDescription->setOption('code', $fieldDescription->getOption('code', $fieldDescription->getName()));
        $fieldDescription->setOption('label', $fieldDescription->getOption('label', $fieldDescription->getName()));

        if (!$fieldDescription->getTemplate()) {

            $fieldDescription->setTemplate($this->getTemplate($fieldDescription->getType()));

            if ($fieldDescription->getMappingType() == ClassMetadata::MANY_TO_ONE) {
                $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list_orm_many_to_one.html.twig');
            }

            if ($fieldDescription->getMappingType() == ClassMetadata::ONE_TO_ONE) {
                $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list_orm_one_to_one.html.twig');
            }

            if ($fieldDescription->getMappingType() == ClassMetadata::ONE_TO_MANY) {
                $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list_orm_one_to_many.html.twig');
            }

            if ($fieldDescription->getMappingType() == ClassMetadata::MANY_TO_MANY) {
                $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list_orm_many_to_many.html.twig');
            }
        }

        if ($fieldDescription->getMappingType() == ClassMetadata::MANY_TO_ONE) {
            $admin->attachAdminClass($fieldDescription);
        }

        if ($fieldDescription->getMappingType() == ClassMetadata::ONE_TO_ONE) {
            $admin->attachAdminClass($fieldDescription);
        }

        if ($fieldDescription->getMappingType() == ClassMetadata::ONE_TO_MANY) {
            $admin->attachAdminClass($fieldDescription);
        }

        if ($fieldDescription->getMappingType() == ClassMetadata::MANY_TO_MANY) {
            $admin->attachAdminClass($fieldDescription);
        }
    }

    /**
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    public function buildActionFieldDescription(FieldDescriptionInterface $fieldDescription)
    {
        if (null === $fieldDescription->getTemplate()) {
            $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list__action.html.twig');
        }

        if (null === $fieldDescription->getType()) {
            $fieldDescription->setType('action');
        }

        if (null === $fieldDescription->getOption('name')) {
            $fieldDescription->setOption('name', 'Action');
        }

        if (null === $fieldDescription->getOption('code')) {
            $fieldDescription->setOption('code', 'Action');
        }

        if (null !== $fieldDescription->getOption('actions')) {
            $actions = $fieldDescription->getOption('actions');
            foreach ($actions as $k => $action) {
                if (!isset($action['template'])) {
                    $actions[$k]['template'] = sprintf('SonataAdminBundle:CRUD:list__action_%s.html.twig', $k);
                }
            }

            $fieldDescription->setOption('actions', $actions);
        }

        return $fieldDescription;
    }

    /**
     * @param $type
     * @return string
     */
    private function getTemplate($type)
    {
        if (!isset($this->templates[$type])) {
            return null;
        }

        return $this->templates[$type];
    }
}
