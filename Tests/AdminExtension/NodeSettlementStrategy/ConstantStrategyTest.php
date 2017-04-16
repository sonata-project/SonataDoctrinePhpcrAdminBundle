<?php
namespace Sonata\DoctrinePHPCRAdminBundle\Tests\AdminExtension\NodeSettlementStrategy;

use Sonata\DoctrinePHPCRAdminBundle\AdminExtension\NodeSettlementStrategy\ConstantStrategy;
use Sonata\DoctrinePHPCRAdminBundle\Tests\AdminExtension\NodeSettlementStrategy\Fixtures\FixtureWithConstant;


class ConstantStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testGetParentNodePath()
    {
        $strategy = new ConstantStrategy('ROOT');
        $object   = new FixtureWithConstant;
        $this->assertEquals(
            '/somewhere/in/the/tree',
            $strategy->getParentNodePath($object)
        );
    }

    /**
     * @expectedException Sonata\DoctrinePHPCRAdminBundle\AdminExtension\NodeSettlementStrategy\Exception\MissingConstantException
     * @expectedExceptionMessage Constant with name "stdClass::whatever" could not be found
     */
    public function testWithMissingConst()
    {
        $strategy = new ConstantStrategy('whatever');
        $object   = new \stdClass;
        $strategy->getParentNodePath($object);
    }
}
