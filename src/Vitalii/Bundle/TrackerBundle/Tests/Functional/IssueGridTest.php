<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Functional;

use Oro\Bundle\DataGridBundle\Tests\Functional\AbstractDatagridTestCase;

class IssueGridTest extends AbstractDatagridTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures(['Vitalii\Bundle\TrackerBundle\Tests\Functional\DataFixtures\LoadIssueData']);
    }

    /**
     * @dataProvider gridProvider
     *
     * @param array $requestData
     */
    public function testGrid($requestData)
    {
        $requestData['gridParameters'][$requestData['gridParameters']['gridName']]['_pager']['_per_page'] = 100;

        parent::testGrid($requestData);
    }

    /**
     * {@inheritdoc}
     */
    public function gridProvider()
    {
        return [
            'Issues grid' => [
                [
                    'gridParameters' => [
                        'gridName' => 'issues-grid',
                    ],
                    'gridFilters' => [],
                    'assert' => [],
                    'expectedResultCount' => 3,
                ],
            ],
        ];
    }
}
