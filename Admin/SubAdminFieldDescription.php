<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Admin;

use Sonata\AdminBundle\Admin\BaseFieldDescription;

class SubAdminFieldDescription extends BaseFieldDescription
{

    public function setFieldMapping($fieldMapping){}

    public function setAssociationMapping($associationMapping){}

    public function setParentAssociationMappings(array $parentAssociationMappings){}

    /**
     * return the related Target Entity
     *
     * @return string|null
     */
    public function getTargetEntity()
    {
        return null;
    }

    /**
     * return true if the FieldDescription is linked to an identifier field
     *
     * @return bool
     */
    public function isIdentifier()
    {
        return false;
    }

    /**
     * return the value linked to the description
     *
     * @param mixed $object
     *
     * @return bool|mixed
     */
    public function getValue($object)
    {
        $subAdmin = $this->getAdmin()->getSubAdmin($object);
        $label = $subAdmin->trans($subAdmin->getLabel());
        return $label;
    }

}
