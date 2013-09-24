<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\DependencyInjection;

use Sonata\DoctrinePHPCRAdminBundle\DependencyInjection\SonataDoctrinePHPCRAdminExtension;

class SonataDoctrinePHPCRAdminExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->extension = new SonataDoctrinePHPCRAdminExtension();
        $refl = new \ReflectionClass($this->extension);
        $this->processDocumentTreeConfigMethod = $refl->getMethod('processDocumentTreeConfig');
        $this->processDocumentTreeConfigMethod->setAccessible(true);
    }

    public function getDocumentTreeConfigTests()
    {
        return array(
            // Valid expansion of single all
            array(
                array(
                    '\StdClass' => array(
                        'valid_children' => array('all')
                    )
                ),
                array(
                    '\StdClass' => array(
                        'valid_children' => array('\StdClass')
                    )
                ),
            ),
            // Expansion with a valid_children array with all and a class
            array(
                array(
                    '\StdClass' => array(
                        'valid_children' => array('all', '\StdClass')
                    )
                ),
                array(
                    '\StdClass' => array(
                        'valid_children' => array('\StdClass')
                    )
                ),
            ),
            // Empty config ignored
            array(array(), array()),
            // Empty valid children
            array(
                array('\StdClass' => array('valid_children' => array())),
                array('\StdClass' => array('valid_children' => array())),
            ),
            // Ensure all 'all' values do not appear in expanded valid_children
            array(
                array(
                    '\StdClass' => array('valid_children' => array('all')),
                    '\SplFileInfo' => array('valid_children' => array('all'))
                ),
                array(
                    '\StdClass' => array('valid_children' => array('\StdClass', '\SplFileInfo')),
                    '\SplFileInfo' => array('valid_children' => array('\StdClass', '\SplFileInfo'))
                ),
            ),
            // Allow valid children that are not mapped in the top level
            array(
                array(
                    '\StdClass' => array('valid_children' => array('\SplFileInfo'))
                ),
                array(
                    '\StdClass' => array('valid_children' => array('\SplFileInfo'))
                )
            ),
            // Complex example
            array(
                array(
                    'Doctrine\ODM\PHPCR\Document\Generic' => array('valid_children' => array(
                        'all'
                    )),
                    '\SplFileInfo' => array('valid_children' => array(
                        '\StdClass'
                    )),
                    '\StdClass' => array('valid_children' => array(
                        '\StdClass'
                    )),
                    '\ArrayIterator' => array('valid_children' => array()),
                ),
                array(
                    'Doctrine\ODM\PHPCR\Document\Generic' => array('valid_children' => array(
                        'Doctrine\ODM\PHPCR\Document\Generic',
                        '\SplFileInfo',
                        '\StdClass',
                        '\ArrayIterator',
                    )),
                    '\SplFileInfo' => array('valid_children' => array(
                        '\StdClass'
                    )),
                    '\StdClass' => array('valid_children' => array(
                        '\StdClass'
                    )),
                    '\ArrayIterator' => array('valid_children' => array()),
                )
            ),
            // Exception due to invalid child class
            array(
                array(
                    '\StdClass' => array('valid_children' => array('\Foo\Bar'))
                ),
                null,
                'InvalidArgumentException'
            ),
            // Exception due to invalid parent class
            array(
                array(
                    'Foo\Bar' => array('valid_children' => array('all'))
                ),
                null,
                'InvalidArgumentException'
            ),
            // Exception due to invalid class in an array with special 'all' value
            array(
                array(
                    '\StdClass' => array('valid_children' => array('all', 'Foo\Bar'))
                ),
                null,
                'InvalidArgumentException'
            ),
        );
    }

    /**
     * @dataProvider getDocumentTreeConfigTests
     */
    public function testProcessDocumentTreeConfig($config, $processed, $expectedException = null)
    {
        if ($expectedException) {
            $this->setExpectedException($expectedException);
            $this->processDocumentTreeConfigMethod->invokeArgs($this->extension, array($config));
        } else {
            $this->assertEquals(
                $processed,
                $this->processDocumentTreeConfigMethod->invokeArgs($this->extension, array($config))
            );
        }
    }

}
