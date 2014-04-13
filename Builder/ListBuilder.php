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
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;

use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;

class ListBuilder implements ListBuilderInterface
{
    /**
     * @var TypeGuesserInterface
     */
    protected $guesser;

    /**
     * @var array
     */
    protected $templates;

    /**
     * @param TypeGuesserInterface $guesser
     * @param array $templates
     */
    public function __construct(TypeGuesserInterface $guesser, array $templates = array())
    {
        $this->guesser = $guesser;
        $this->templates = $templates;
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseList(array $options = array())
    {
        return new FieldDescriptionCollection();
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function addField(FieldDescriptionCollection $list, $type = null, FieldDescriptionInterface $fieldDescription, AdminInterface $admin)
    {
        $this->buildField($type, $fieldDescription, $admin);
        $admin->addListFieldDescription($fieldDescription->getName(), $fieldDescription);

        $list->add($fieldDescription);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function getTemplate($type)
    {
        if (!isset($this->templates[$type])) {
            return null;
        }

        return $this->templates[$type];
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException if the $fieldDescription does not have a type.
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription)
    {
        if ($fieldDescription->getName() == '_action') {
            $this->buildActionFieldDescription($fieldDescription);
        }

        $fieldDescription->setAdmin($admin);

        if ($admin->getModelManager()->hasMetadata($admin->getClass())) {
            $metadata = $admin->getModelManager()->getMetadata($admin->getClass());

            // TODO sort on parent associations or node name
            $defaultSortable = true;
            if ($metadata->hasAssociation($fieldDescription->getName())
                || $metadata->nodename === $fieldDescription->getName()
            ) {
                $defaultSortable = false;
            }

            // TODO get and set parent association mappings, see
            // https://github.com/sonata-project/SonataDoctrinePhpcrAdminBundle/issues/106
            //$fieldDescription->setParentAssociationMappings($parentAssociationMappings);

            // set the default field mapping
            if (isset($metadata->mappings[$fieldDescription->getName()])) {
                $fieldDescription->setFieldMapping($metadata->mappings[$fieldDescription->getName()]);
                if ($fieldDescription->getOption('sortable') !== false) {
                    $fieldDescription->setOption('sortable', $fieldDescription->getOption('sortable', $defaultSortable));
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
                $fieldDescription->setTemplate('SonataDoctrinePHPCRAdminBundle:CRUD:list_phpcr_many_to_one.html.twig');
            }

            if ($fieldDescription->getMappingType() == ClassMetadata::MANY_TO_MANY) {
                $fieldDescription->setTemplate('SonataDoctrinePHPCRAdminBundle:CRUD:list_phpcr_many_to_many.html.twig');
            }

            if ($fieldDescription->getMappingType() == 'child' || $fieldDescription->getMappingType() == 'parent') {
                $fieldDescription->setTemplate('SonataDoctrinePHPCRAdminBundle:CRUD:list_phpcr_one_to_one.html.twig');
            }

            if ($fieldDescription->getMappingType() == 'children' || $fieldDescription->getMappingType() == 'referrers') {
                $fieldDescription->setTemplate('SonataDoctrinePHPCRAdminBundle:CRUD:list_phpcr_one_to_many.html.twig');
            }
        }

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
     * @param FieldDescriptionInterface $fieldDescription
     *
     * @return FieldDescriptionInterface
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
}
