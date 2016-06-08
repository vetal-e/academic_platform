<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolation
 */
class ControllersTest extends WebTestCase
{
    const TEST_ISSUE_ID = 1;

    protected function setUp()
    {
        $this->initClient(array(), $this->generateBasicAuthHeader());
    }

    public function testIndex()
    {
        $crawler = $this->client->request('GET', $this->getUrl('tracker.issue_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertEquals('All Issues', $crawler->filter('#page-title')->html());
    }

//    public function testView()
//    {
//        $crawler = $this->client->request(
//            'GET',
//            $this->getUrl(
//                'oro_calendar_view',
//                array('id' => self::DEFAULT_USER_CALENDAR_ID)
//            )
//        );
//        $result = $this->client->getResponse();
//        $this->assertHtmlResponseStatusCodeEquals($result, 200);
//        $this->assertEquals('John Doe - Calendars - John Doe', $crawler->filter('#page-title')->html());
//    }
}
