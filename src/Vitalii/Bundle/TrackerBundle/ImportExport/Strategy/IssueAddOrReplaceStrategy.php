<?php

namespace Vitalii\Bundle\TrackerBundle\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

class IssueAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * @var IssueImportHelper
     */
    protected $issueImportHelper;
    /**
     * @param IssueImportHelper $issueImportHelper
     */
    public function setIssueImportHelper(IssueImportHelper $issueImportHelper)
    {
        $this->issueImportHelper = $issueImportHelper;
    }

}
