<?php

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\WebTest\Dashboard;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class TreeBrowserTest extends BaseTestCase
{
    public function setUp()
    {
        $this->db('PHPCR')->loadFixtures(array('Sonata\DoctrinePHPCRAdminBundle\Tests\Resources\DataFixtures\Phpcr\LoadTreeData'));
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
