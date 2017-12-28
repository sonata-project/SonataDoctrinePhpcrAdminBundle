<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\WebTest\Admin;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

class ContentAdminTest extends BaseTestCase
{
    public function setUp()
    {
        $this->db('PHPCR')->loadFixtures(['Sonata\DoctrinePHPCRAdminBundle\Tests\Resources\DataFixtures\Phpcr\LoadTreeData']);
        $this->client = $this->createClient();
    }

    public function testContentList()
    {
        $crawler = $this->client->request('GET', '/admin/tests/resources/content/list');
        $res = $this->client->getResponse();

        $this->assertResponseSuccess($res);
        $this->assertCount(1, $crawler->filter('html:contains("Content 1")'));
    }

    public function testContentWithChildEdit()
    {
        $crawler = $this->client->request('GET', '/admin/tests/resources/content/test/content/content-1/edit');
        $res = $this->client->getResponse();

        $this->assertResponseSuccess($res);
        $this->assertCount(1, $crawler->filter('input[value="content-1"]'));
        $this->assertCount(1, $crawler->filter('input[value="Content 1"]'));
        $this->assertCount(1, $crawler->filter('input[value="/test/content"]'));
        $this->assertCount(1, $crawler->filter('input[value="/test/routes/route-1"]'));
        // ToDo: Sub Admin for child association
        $this->assertCount(1, $crawler->filter('div[id$="child"] select'));

        // see the routes selection of a ModelType
        $this->assertCount(1, $crawler->filter('div[id$="_routes"] select'));
    }

    public function testContentWithChildrenEdit()
    {
        $crawler = $this->client->request('GET', '/admin/tests/resources/content/test/content/content-2/edit');
        $res = $this->client->getResponse();

        $this->assertResponseSuccess($res);
        $this->assertCount(1, $crawler->filter('input[value="content-2"]'));
        $this->assertCount(1, $crawler->filter('input[value="Content 2"]'));
        $this->assertCount(1, $crawler->filter('input[value="/test/content"]'));

        // see the children table view of a CollectionType
        $this->assertCount(1, $crawler->filter('div[id$="_children"] table'));
    }

    public function testContentCreate()
    {
        $crawler = $this->client->request('GET', '/admin/tests/resources/content/create');
        $res = $this->client->getResponse();
        $this->assertResponseSuccess($res);

        $button = $crawler->selectButton('Create');
        $form = $button->form();
        $node = $form->getFormNode();
        $actionUrl = $node->getAttribute('action');
        $uniqId = substr(strstr($actionUrl, '='), 1);

        $form[$uniqId.'[parentDocument]'] = '/test/content';
        $form[$uniqId.'[name]'] = 'foo-test';
        $form[$uniqId.'[title]'] = 'Foo Test';

        $this->client->submit($form);
        $res = $this->client->getResponse();

        // If we have a 302 redirect, then all is well
        $this->assertEquals(302, $res->getStatusCode());
    }

    public function testShowContent()
    {
        $crawler = $this->client->request('GET', '/admin/tests/resources/content/test/content/content-1/show');
        $res = $this->client->getResponse();

        if (200 !== $res->getStatusCode()) {
            echo $res->getContent();
        }
        $this->assertResponseSuccess($res);
    }
}
