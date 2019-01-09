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

namespace Sonata\DoctrinePHPCRAdminBundle\Builder;

use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;

class ShowBuilder implements ShowBuilderInterface
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
     * @param array                $templates Indexed by field type
     */
    public function __construct(TypeGuesserInterface $guesser, array $templates)
    {
        $this->guesser = $guesser;
        $this->templates = $templates;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseList(array $options = [])
    {
        return new FieldDescriptionCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function addField(FieldDescriptionCollection $list, $type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin): void
    {
        if (null == $type) {
            $guessType = $this->guesser->guessType($admin->getClass(), $fieldDescription->getName(), $admin->getModelManager());
            $fieldDescription->setType($guessType->getType());
        } else {
            $fieldDescription->setType($type);
        }

        $this->fixFieldDescription($admin, $fieldDescription);
        $admin->addShowFieldDescription($fieldDescription->getName(), $fieldDescription);

        $list->add($fieldDescription);
    }

    /**
     * The method defines the correct default settings for the provided FieldDescription.
     *
     * {@inheritdoc}
     *
     * @throws \RuntimeException if the $fieldDescription does not have a type
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription): void
    {
        $fieldDescription->setAdmin($admin);

        $metadata = null;
        if ($admin->getModelManager()->hasMetadata($admin->getClass())) {
            /** @var ClassMetadata $metadata */
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
            throw new \RuntimeException(sprintf('Please define a type for field `%s` in `%s`', $fieldDescription->getName(), \get_class($admin)));
        }

        $fieldDescription->setOption('code', $fieldDescription->getOption('code', $fieldDescription->getName()));
        $fieldDescription->setOption('label', $fieldDescription->getOption('label', $fieldDescription->getName()));

        if (!$fieldDescription->getTemplate()) {
            $fieldDescription->setTemplate($this->getTemplate($fieldDescription->getType()));

            if (ClassMetadata::MANY_TO_ONE == $fieldDescription->getMappingType()) {
                $fieldDescription->setTemplate('@SonataAdmin/CRUD/Association/show_many_to_one.html.twig');
            }

            if (ClassMetadata::MANY_TO_MANY == $fieldDescription->getMappingType()) {
                $fieldDescription->setTemplate('@SonataAdmin/CRUD/Association/show_many_to_many.html.twig');
            }
        }

        $mappingTypes = [
            ClassMetadata::MANY_TO_ONE,
            ClassMetadata::MANY_TO_MANY,
            'children',
            'child',
            'parent',
            'referrers',
        ];

        if ($metadata && $metadata->hasAssociation($fieldDescription->getName()) && \in_array($fieldDescription->getMappingType(), $mappingTypes)) {
            $admin->attachAdminClass($fieldDescription);
        }
    }

    /**
     * @param string $type
     *
     * @return string|null The template if found
     */
    private function getTemplate($type)
    {
        if (!isset($this->templates[$type])) {
            return;
        }

        return $this->templates[$type];
    }
}
