<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) 2010-2011 Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Admin;

use Sonata\AdminBundle\Admin\BaseFieldDescription;

/**
 * {@inheritDoc}
 */
class FieldDescription extends BaseFieldDescription
{
    public function __construct()
    {
        $this->parentAssociationMappings = array();
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException if the mapping is no array or of an
     *                                   unknown type.
     */
    public function setAssociationMapping($associationMapping)
    {
        if (!is_array($associationMapping)) {
           throw new \InvalidArgumentException('The association mapping must be an array');
        }

        $this->associationMapping = $associationMapping;

        if(isset($associationMapping['type'])){
            $this->type         = $this->type ?: $associationMapping['type'];
            $this->mappingType  = $this->mappingType ?: $associationMapping['type'];
        } else {
            throw new \InvalidArgumentException('Unknown association mapping type');
        }
        $this->fieldName    = $associationMapping['fieldName'];
    }

    /**
     * {@inheritDoc}
     */
    public function getTargetEntity()
    {
        if (isset($this->associationMapping['targetDocument'])) {
            return $this->associationMapping['targetDocument'];
        }
        if (isset($this->associationMapping['referringDocument'])) {
            return $this->associationMapping['referringDocument'];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException if the mapping information is not an array.
     */
    public function setFieldMapping($fieldMapping)
    {
        if (!is_array($fieldMapping)) {
            throw new \InvalidArgumentException('The field mapping must be an array');
        }

        $this->fieldMapping = $fieldMapping;

        $this->type         = $this->type ?: $fieldMapping['type'];
        $this->mappingType  = $this->mappingType ?: $fieldMapping['type'];
        $this->fieldName    = $this->fieldName ?: $fieldMapping['fieldName'];
    }

    /**
     * {@inheritDoc}
     */
    public function isIdentifier()
    {
        return isset($this->fieldMapping['id']) ? $this->fieldMapping['id'] : false;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue($object)
    {
        foreach ($this->parentAssociationMappings as $parentAssociationMapping) {
            $object = $this->getFieldValue($object, $parentAssociationMapping['fieldName']);
        }

        return $this->getFieldValue($object, $this->fieldName);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException if the list of mappings does contain
     *                                   something else than arrays.
     */
    public function setParentAssociationMappings(array $parentAssociationMappings)
    {
        foreach ($parentAssociationMappings as $parentAssociationMapping) {
            if (!is_array($parentAssociationMapping)) {
                throw new \RuntimeException('An association mapping must be an array');
            }
        }

        $this->parentAssociationMappings = $parentAssociationMappings;
    }
}
