<?php
namespace Sonata\DoctrinePHPCRAdminBundle\Tests\AdminExtension;

use Sonata\DoctrinePHPCRAdminBundle\AdminExtension\NodeSettlementExtension;

class NodeSettlementExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Sonata\DoctrinePHPCRAdminBundle\AdminExtension\MissingDocumentException
     */
    public function testAlterNewInstance()
    {
        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $nodeSettlementStrategy = $this->getMock(
            'Sonata\DoctrinePHPCRAdminBundle\AdminExtension\NodeSettlementStrategyInterface'
        );
        $modelManager = $this->getMockBuilder('Sonata\DoctrinePHPCRAdminBundle\Model\ModelManager')
            ->disableOriginalConstructor()
            ->getMock();
        $documentManager = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();
        $object    = $this->getMock('Doctrine\ODM\PHPCR\HierarchyInterface');
        $extension = new NodeSettlementExtension($nodeSettlementStrategy);

        $nodeSettlementStrategy->expects($this->exactly(3))
            ->method('getParentNodePath')
            ->will($this->onConsecutiveCalls(
                false,
                $path = '/some/path',
                $path
            ));

        $admin->expects($this->exactly(2))
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));
        $modelManager->expects($this->exactly(2))
            ->method('getDocumentManager')
            ->will($this->returnValue($documentManager));
        $documentManager->expects($this->exactly(2))
            ->method('find')
            ->with(null, $path)
            ->will($this->onConsecutiveCalls($parent = new \StdClass, null));

        $object->expects($this->once())
            ->method('setParentDocument')
            ->with($this->equalTo($parent));

        $extension->alterNewInstance($admin, $object);
        $extension->alterNewInstance($admin, $object);
        $extension->alterNewInstance($admin, $object);
    }
}
