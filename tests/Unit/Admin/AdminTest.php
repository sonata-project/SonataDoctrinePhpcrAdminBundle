<?php

declare(strict_types=1);

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
<<<<<<< HEAD
    /**
     * @doesNotPerformAssertions
     */
    public function testItCanBeInstanciated()
=======
    public function testItCanBeInstanciated(): void
>>>>>>> Applied fixes from FlintCI (#515)
    {
        $admin = new Admin('', '', '');
    }
}
