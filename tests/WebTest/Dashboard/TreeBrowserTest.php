<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\WebTest\Dashboard;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class TreeBrowserTest extends BaseTestCase
{
    public function setUp()
    {
        $this->db('PHPCR')->loadFixtures(['Sonata\DoctrinePHPCRAdminBundle\Tests\Resources\DataFixtures\Phpcr\LoadTreeData']);
        $this->client = $this->createClient();
    }

    public function testTreeOnDashboardLoadsWithNoErrors()
    {
        $crawler = $this->client->request('GET', '/admin/dashboard');
        $res = $this->client->getResponse();

        $this->assertResponseSuccess($res);

        $this->assertCount(1, $crawler->filter('div#tree'));
    }
}
