<?php
namespace Sonata\DoctrinePHPCRAdminBundle\Tests\AdminExtension\NodeSettlementStrategy;

use Sonata\DoctrinePHPCRAdminBundle\AdminExtension\NodeSettlementStrategy\MappingStrategy;

class MappingStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->strategy = new MappingStrategy(array(
            'Sonata\DoctrinePHPCRAdminBundle\Tests\AdminExtension' .
            '\NodeSettlementStrategy\MappingStrategyTest' => '/some/node'
        ));
    }

    public function testGetParentNodePath()
    {
        $object   = new self;
        $this->assertEquals(
            '/some/node',
            $this->strategy->getParentNodePath($object)
        );
    }

    /**
     * @expectedException Sonata\DoctrinePHPCRAdminBundle\AdminExtension\NodeSettlementStrategy\Exception\MissingMappingException
     * @expectedExceptionMessage No path mapping could be found for class stdClass
     */
    public function testWithMissingConst()
    {
        $object   = new \stdClass;
        $this->strategy->getParentNodePath($object);
    }
}
