<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Admin;

use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Sonata\DoctrinePHPCRAdminBundle\Admin\FieldDescription;

class FieldDescriptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider testDescribesSingleValuedAssociationProvider
     *
     * @param mixed $mappingType
     * @param bool  $expected
     */
    public function testDescribesSingleValuedAssociation($mappingType, $expected)
    {
        $fd = new FieldDescription();
        $fd->setAssociationMapping(array(
            'fieldName' => 'foo',
            'type' => $mappingType,
        ));
        $this->assertSame($expected, $fd->describesSingleValuedAssociation());
    }

    public function testDescribesSingleValuedAssociationProvider()
    {
        return array(
            'many to one' => array(ClassMetadata::MANY_TO_ONE, true),
            'parent' => array('parent', true),
            'child' => array('child', true),
            'many to many' => array(ClassMetadata::MANY_TO_MANY, false),
            'children' => array('children', false),
            'referrers' => array('referrers', false),
            'mixedreferrers' => array('mixedreferrers', false),
            'string' => array('string', false),
        );
    }

    /**
     * @dataProvider testDescribesCollectionValuedAssociationProvider
     *
     * @param mixed $mappingType
     * @param bool  $expected
     */
    public function testDescribesCollectionValuedAssociation($mappingType, $expected)
    {
        $fd = new FieldDescription();
        $fd->setAssociationMapping(array(
            'fieldName' => 'foo',
            'type' => $mappingType,
        ));
        $this->assertSame($expected, $fd->describesCollectionValuedAssociation());
    }

    public function testDescribesCollectionValuedAssociationProvider()
    {
        return array(
            'many to one' => array(ClassMetadata::MANY_TO_ONE, false),
            'parent' => array('parent', false),
            'child' => array('child', false),
            'many to many' => array(ClassMetadata::MANY_TO_MANY, true),
            'children' => array('children', true),
            'referrers' => array('referrers', true),
            'mixedreferrers' => array('mixedreferrers', true),
            'string' => array('string', false),
        );
    }
}
