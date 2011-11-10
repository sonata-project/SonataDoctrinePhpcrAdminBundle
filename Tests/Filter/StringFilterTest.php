<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Filter;

use Sonata\DoctrinePHPCRAdminBundle\Filter\StringFilter;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use PHPCR\Query\QOM\QueryObjectModelConstantsInterface as Constants;

class StringFilterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->qb = $this->getMockBuilder('Sonata\DoctrinePHPCRAdminBundle\Datagrid\ProxyQuery')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testFilterNoData1()
    {
        $this->qb->expects($this->never())
            ->method('andWhere');
        $stringFilter = new StringFilter();
        $stringFilter->filter($this->qb, null, 'somefield', null);
    }

    public function testFilterNoData2()
    {
        $this->qb->expects($this->never())
            ->method('andWhere');
        $stringFilter = new StringFilter();
        $stringFilter->filter($this->qb, null, 'somefield', array());
    }

    public function testFilterNoData3()
    {
        $this->qb->expects($this->never())
            ->method('andWhere');
        $stringFilter = new StringFilter();
        $stringFilter->filter($this->qb, null, 'somefield', array('type' => ChoiceType::TYPE_EQUAL));
    }

    public function testFilterNoData4()
    {
        $this->qb->expects($this->never())
            ->method('andWhere');
        $stringFilter = new StringFilter();
        $stringFilter->filter($this->qb, null, 'somefield', array('type' => ChoiceType::TYPE_EQUAL, 'value' => ' '));
    }

    public function testFilterTypeEqual()
    {
        $field = 'somefield';
        $value = 'somevalue';
        $comparison = $this->getMock('PHPCR\Query\QOM\ComparisonInterface', array(), array());
        $constant = Constants::JCR_OPERATOR_EQUAL_TO;
        $property = $this->getMock('PHPCR\Query\QOM\PropertyValueInterface', array(), array());
        $staticOperand = $this->getMock('PHPCR\Query\QOM\StaticOperandInterface', array(), array());
        $qf = $this->getMock('PHPCR\Query\QOM\QueryObjectModelFactoryInterface', array(), array());
        $qf->expects($this->once())
            ->method('propertyValue')
            ->with('['.$field.']')
            ->will($this->returnValue($property));
        $qf->expects($this->once())
            ->method('literal')
            ->with($value)
            ->will($this->returnValue($staticOperand));
        $qf->expects($this->once())
            ->method('comparison')
            ->with($property, Constants::JCR_OPERATOR_EQUAL_TO, $staticOperand)
            ->will($this->returnValue($comparison));
        $this->qb->expects($this->once())
            ->method('getQueryObjectModelFactory')
            ->will($this->returnValue($qf));
        $this->qb->expects($this->once())
            ->method('andWhere')
            ->with($comparison);
        $stringFilter = new StringFilter();
        $stringFilter->filter($this->qb, null, 'somefield', array('type' => ChoiceType::TYPE_EQUAL, 'value' => 'somevalue'));
    }

    //public function testFilterTypeContains()
    //{
    //    $field = 'somefield';
    //    $value = 'somevalue';
    //    $fulltext = $this->getMock('PHPCR\Query\QOM\FullTextSearchInterface', array(), array());
    //    $constant = Constants::JCR_OPERATOR_EQUAL_TO;
    //    $property = $this->getMock('PHPCR\Query\QOM\PropertyValueInterface', array(), array());
    //    $staticOperand = $this->getMock('PHPCR\Query\QOM\StaticOperandInterface', array(), array());
    //    $qf = $this->getMock('PHPCR\Query\QOM\QueryObjectModelFactoryInterface', array(), array());

    //    $qf->expects($this->once())
    //        ->method('fullTextSearch')
    //        ->with($field, $property)
    //        ->with($this->returnValue($fullText))
    //    $qf->expects($this->once())
    //        ->method('comparison')
    //        ->with($property, Constants::JCR_OPERATOR_EQUAL_TO, $staticOperand)
    //        ->will($this->returnValue($fulltext));
    //    $this->qb->expects($this->once())
    //        ->method('getQueryObjectModelFactory')
    //        ->will($this->returnValue($qf));
    //    $this->qb->expects($this->once())
    //        ->method('andWhere')
    //        ->with($comparison);
    //    $stringFilter = new StringFilter();
    //    $stringFilter->filter($this->qb, null, 'somefield', array('type' => ChoiceType::TYPE_CONTAINS, 'value' => 'somevalue'));
    //}
}
