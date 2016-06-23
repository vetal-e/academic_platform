<?php

namespace Vitalii\Bundle\TrackerBundle\ImportExport\Strategy;

use Oro\Bundle\EntityBundle\ORM\OroEntityManager;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Oro\Bundle\UserBundle\Entity\User;

class IssueAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * @var IssueImportHelper
     */
    protected $issueImportHelper;

    /**
     * @var OroEntityManager
     */
    protected $entityManager;

    /**
     * @param IssueImportHelper $issueImportHelper
     */
    public function setIssueImportHelper(IssueImportHelper $issueImportHelper)
    {
        $this->issueImportHelper = $issueImportHelper;
    }

    /**
     * @param OroEntityManager $entityManager
     */
    public function setEntityManager(OroEntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        $collaborators = $entity->getCollaborators();
        $unitOfWork = $this->entityManager->getUnitOfWork();

        foreach ($collaborators as $collaborator) {
            if (is_object($collaborator)
                and $collaborator instanceof User
                and $unitOfWork->getEntityState($collaborator) === $unitOfWork::STATE_MANAGED
            ) {
                // workaround for the bug, to not let the doctrine persist issues through related persisted users
                // (which weren't actually changed)
                $unitOfWork->markReadOnly($collaborator);
            }
        }

        return parent::afterProcessEntity($entity);
    }
}
