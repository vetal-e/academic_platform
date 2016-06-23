<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Functional;

use Oro\Bundle\DataGridBundle\Tests\Functional\AbstractDatagridTestCase;

/**
 * @dbIsolation
 */
class IssueGridTest extends AbstractDatagridTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures(['Vitalii\Bundle\TrackerBundle\Tests\Functional\DataFixtures\LoadIssueData']);
    }

    /**
     * @dataProvider gridProvider
     *
     * {@inheritdoc}
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
            'Dashboard recent issues grid' => [
                [
                    'gridParameters' => [
                        'gridName' => 'dashboard-recent-issues-grid',
                    ],
                    'gridFilters' => [],
                    'assert' => [],
                    'expectedResultCount' => 3,
                ],
            ],
            'Issues chart grid' => [
                [
                    'gridParameters' => [
                        'gridName' => 'issues-chart-grid',
                    ],
                    'gridFilters' => [],
                    'assert' => [],
                    'expectedResultCount' => 1,
                ],
            ],
        ];
    }
}
