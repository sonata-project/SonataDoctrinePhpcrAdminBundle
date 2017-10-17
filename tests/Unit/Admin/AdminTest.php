<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Unit\Admin;

use PHPUnit\Framework\TestCase;
use Sonata\DoctrinePHPCRAdminBundle\Admin\Admin;

class AdminTest extends TestCase
{
    public function testItCanBeInstanciated()
    {
        $admin = new Admin('', '', '');
    }
}
