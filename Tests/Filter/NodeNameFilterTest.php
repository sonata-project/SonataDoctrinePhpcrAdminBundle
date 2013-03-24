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

use Sonata\DoctrinePHPCRAdminBundle\Filter\NodeNameFilter;
use Sonata\DoctrinePHPCRAdminBundle\Form\Type\Filter\ChoiceType;

class NodeNameFilterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->proxyQuery = $this->getMockBuilder('Sonata\DoctrinePHPCRAdminBundle\Datagrid\ProxyQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $this->qb = $this->getMockBuilder('Doctrine\ODM\PHPCR\Query\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->exprBuilder = $this->getMock('Doctrine\ODM\PHPCR\Query\ExpressionBuilder');
        $this->expr = $this->getMock('Doctrine\Common\Collections\Expr\Expression');
        $this->filter = new NodeNameFilter();
    }

    public function testFilterNullData()
    {
        $res = $this->filter->filter($this->proxyQuery, null, 'somefield', null);
        $this->assertNull($res);
        $this->assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayData()
    {
        $res = $this->filter->filter($this->proxyQuery, null, 'somefield', array());
        $this->assertNull($res);
        $this->assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayDataSpecifiedType()
    {
        $res = $this->filter->filter($this->proxyQuery, null, 'somefield', array('type' => ChoiceType::TYPE_EQUAL));
        $this->assertNull($res);
        $this->assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayDataWithMeaninglessValue()
    {
        $this->proxyQuery->expects($this->never())
            ->method('andWhere');

        $this->qb->expects($this->never())
            ->method('andWhere');

        $this->filter->filter($this->proxyQuery, null, 'somefield', array('type' => ChoiceType::TYPE_EQUAL, 'value' => ' '));
        $this->assertFalse($this->filter->isActive());
    }

    public function getFilters()
    {
        return array(
            array('eqNodeName', ChoiceType::TYPE_EQUAL),
            array('likeNodeName', ChoiceType::TYPE_NOT_CONTAINS, '%somevalue%'),
            array('likeNodeName', ChoiceType::TYPE_CONTAINS, '%somevalue%'),
            array('likeNodeName', ChoiceType::TYPE_CONTAINS_WORDS, '%somevalue%'),
        );
    }

    /**
     * @dataProvider getFilters
     */
    public function testFilterSwitch($operatorMethod, $choiceType, $expectedValue = 'somevalue')
    {
        $this->proxyQuery->expects($this->exactly(2))
            ->method('getQueryBuilder')
            ->will($this->returnValue($this->qb));
        $this->qb->expects($this->once())
            ->method('expr')
            ->will($this->returnValue($this->exprBuilder));
        $this->qb->expects($this->once())
            ->method('andWhere')
            ->will($this->returnValue($this->qb));
        $this->exprBuilder->expects($this->once())
            ->method($operatorMethod)
            ->with($expectedValue)
            ->will($this->returnValue($this->expr));

        $this->filter->filter(
            $this->proxyQuery,
            null,
            'somefield',
            array('type' => $choiceType, 'value' => 'somevalue')
        );
        $this->assertTrue($this->filter->isActive());
    }
}