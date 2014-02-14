<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Tree;

use Doctrine\ODM\PHPCR\DocumentManager;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\DoctrinePHPCRAdminBundle\Model\ModelManager;
use Sonata\DoctrinePHPCRAdminBundle\Tree\PhpcrOdmTree;
use Symfony\Component\Templating\Helper\CoreAssetsHelper;
use Symfony\Component\Translation\TranslatorInterface;

class PhpcrOdmTreeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DocumentManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dm;
    /**
     * @var ModelManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $defaultModelManager;
    /**
     * @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $translator;
    /**
     * @var CoreAssetsHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetHelper;
    /**
     * @var Pool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pool;

    public function setUp()
    {
        $this->dm = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')->disableOriginalConstructor()->getMock();
        $this->dm->expects($this->once())
            ->method('find')
            ->will($this->returnValue(new \stdClass()));

        $this->defaultModelManager = $this->getMockBuilder('Sonata\DoctrinePHPCRAdminBundle\Model\ModelManager')->disableOriginalConstructor()->getMock();
        $this->translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->disableOriginalConstructor()->getMock();
        $this->assetHelper = $this->getMockBuilder('Symfony\Component\Templating\Helper\CoreAssetsHelper')->disableOriginalConstructor()->getMock();

        $this->pool = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')->disableOriginalConstructor()->getMock();
    }

    public function testMoveWithoutAdmin()
    {
        $movedPath = '/cms/to-move';
        $targetPath = '/cms/target/moved';
        $urlSafeId = 'urlSafeId';

        $this->defaultModelManager->expects($this->once())
            ->method('getNormalizedIdentifier')
            ->will($this->returnValue($targetPath));
        $this->defaultModelManager->expects($this->once())
            ->method('getUrlsafeIdentifier')
            ->will($this->returnValue($urlSafeId));
        $this->pool->expects($this->once())
            ->method('getAdminByClass')
            ->will($this->returnValue(null));

        $tree = new PhpcrOdmTree(
            $this->dm,
            $this->defaultModelManager,
            $this->pool,
            $this->translator,
            $this->assetHelper,
            array(),
            array('depth' => 1, 'precise_children' => true)
        );

        $this->assertEquals(
            array('id' => $targetPath, 'url_safe_id' => $urlSafeId),
            $tree->move($movedPath, $targetPath));
    }

    function testMoveWithAdmin()
    {
        $movedPath = '/cms/to-move';
        $targetPath = '/cms/target/moved';
        $urlSafeId = 'urlSafeId';

        $admin = $this->getMockBuilder('Sonata\DoctrinePHPCRAdminBundle\Admin\Admin')->disableOriginalConstructor()->getMock();
        $admin->expects($this->once())
            ->method('getNormalizedIdentifier')
            ->will($this->returnValue($targetPath));
        $admin->expects($this->once())
            ->method('getUrlsafeIdentifier')
            ->will($this->returnValue($urlSafeId));
        $this->pool->expects($this->once())
            ->method('getAdminByClass')
            ->will($this->returnValue($admin));

        $tree = new PhpcrOdmTree(
            $this->dm,
            $this->defaultModelManager,
            $this->pool,
            $this->translator,
            $this->assetHelper,
            array(),
            array('depth' => 1, 'precise_children' => true)
        );
        $this->assertEquals(
            array('id' => $targetPath, 'url_safe_id' => $urlSafeId),
            $tree->move($movedPath, $targetPath));
    }
}
