<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolation
 */
class IssueFormTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient(array(), $this->generateBasicAuthHeader());
    }

    public function testForm()
    {
        $crawler = $this->client->request('GET', '/tracker/issue/create');
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $form = $crawler->filter('form[name=tracker_issue]')->form();
        $form['tracker_issue[code]'] = 'tst-01';
        $form['tracker_issue[summary]'] = 'Test summary';

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();

        $this->assertContains(
            'issues-grid',
            $this->client->getResponse()->getContent()
        );
        $this->assertContains(
            'tst-01',
            $this->client->getResponse()->getContent()
        );
        $this->assertContains(
            'Test summary',
            $this->client->getResponse()->getContent()
        );
    }
}
