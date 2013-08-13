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

class FieldDescription extends BaseFieldDescription
{
    public function __construct()
    {
        $this->parentAssociationMappings = array();
    }

    /**
     * Define the association mapping definition
     *
     * @param array $associationMapping
     * @throws \RuntimeException
     */
    public function setAssociationMapping($associationMapping)
    {
        if (!is_array($associationMapping)) {
           throw new \RuntimeException('The association mapping must be an array');
        }

        $this->associationMapping = $associationMapping;

        if(isset($associationMapping['type'])){
            $this->type         = $this->type ?: $associationMapping['type'];
            $this->mappingType  = $this->mappingType ?: $associationMapping['type'];
        } else {
            throw new \RuntimeException('Unknown association mapping type');
        }
        $this->fieldName    = $associationMapping['fieldName'];
    }

    /**
     * return the related Target Entity
     *
     * @return string|null
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
     * set the field mapping information
     *
     * @param array $fieldMapping
     *
     * @throws \RuntimeException
     */
    public function setFieldMapping($fieldMapping)
    {
        if (!is_array($fieldMapping)) {
            throw new \RuntimeException('The field mapping must be an array');
        }

        $this->fieldMapping = $fieldMapping;

        $this->type         = $this->type ?: $fieldMapping['type'];
        $this->mappingType  = $this->mappingType ?: $fieldMapping['type'];
        $this->fieldName    = $this->fieldName ?: $fieldMapping['fieldName'];
    }

    /**
     * return true if the FieldDescription is linked to an identifier field
     *
     * @return bool
     */
    public function isIdentifier()
    {
        return isset($this->fieldMapping['id']) ? $this->fieldMapping['id'] : false;
    }

    /**
     * return the value linked to the description
     *
     * @param mixed $object
     * @return bool|mixed
     */
    public function getValue($object)
    {
        foreach ($this->parentAssociationMappings as $parentAssociationMapping) {
            $object = $this->getFieldValue($object, $parentAssociationMapping['fieldName']);
        }

        return $this->getFieldValue($object, $this->fieldName);
    }

    /**
     * set the parent association mappings information
     *
     * @param array $parentAssociationMappings
     * @throws \RuntimeException
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
