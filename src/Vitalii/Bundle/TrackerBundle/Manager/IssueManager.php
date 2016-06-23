<?php

namespace Vitalii\Bundle\TrackerBundle\Manager;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\EntityBundle\ORM\Registry;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\NoteBundle\Entity\Note;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Vitalii\Bundle\TrackerBundle\Entity\Issue;
use Vitalii\Bundle\TrackerBundle\Entity\IssueCodesCache;

class IssueManager
{
    private $doctrine;
    private $token;

    /**
     * @param Registry $doctrine
     * @param TokenStorage $tokenStorage
     */
    public function __construct(Registry $doctrine, TokenStorage $tokenStorage)
    {
        $this->doctrine = $doctrine;
        $this->token = $tokenStorage->getToken();
    }

    /**
     * @param Issue $issue
     */
    public function addCollaboratorsFromIssue(Issue $issue)
    {
        $collaborators = [];
        $collaborators[] = $issue->getReporter();
        $collaborators[] = $issue->getAssignee();

        foreach ($collaborators as $collaborator) {
            $issue->addCollaborators($collaborator);
        }

        $this->doctrine->getManager()->flush();
    }

    /**
     * @param Note $note
     */
    public function addCollaboratorsFromNote(Note $note)
    {
        $issue = $note->getTarget();

        $issue->addCollaborators($note->getOwner());

        $this->doctrine->getManager()->flush();
    }

    /**
     * @param Note $note
     */
    public function updateDateOnNote(Note $note)
    {
        /** @var Issue $issue */
        $issue = $note->getTarget();
        $issue->setUpdatedAt(new \DateTime('now'));

        $this->doctrine->getManager()->flush();
    }

    /**
     * @return User[]
     */
    public function getCollaboratorsChoices()
    {
        $options = [];
        $em = $this->doctrine->getManager();
        $collaborators = $em
            ->createQueryBuilder()
            ->from('OroUserBundle:User', 'u')
            ->select('distinct u')
            ->join('u.issue_collaborators', 'i')
            ->getQuery()
            ->getArrayResult();

        foreach ($collaborators as $collaborator) {
            $options[$collaborator['id']] = $collaborator['username'];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getTypeChoices()
    {
        $className = ExtendHelper::buildEnumValueClassName('issue_type');

        $em = $this->doctrine->getManager();
        /** @var EntityManager $em */
        $types = $em
            ->createQueryBuilder()
            ->from($className, 't')
            ->select('t')
            ->where('t.id <> :subtaskId')
            ->setParameter('subtaskId', 'subtask')
            ->getQuery()
            ->getResult();

        return $types;
    }

    /**
     * @return null|string
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function generateCode()
    {
        $em = $this->doctrine->getManager();

        /** @var EntityManager $em */
        /** @var Issue $latestIssue */
        $latestIssue = $em
            ->createQueryBuilder()
            ->from('VitaliiTrackerBundle:Issue', 'i')
            ->select('i')
            ->orderBy('i.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!empty($latestIssue)) {
            $latestCode = $latestIssue->getCode();

            $latestCodeText = $latestCode;
            $latestCodeNumber = 0;

            $latestMatches = [];
            if (preg_match('/(.+)(?:-)(\d+)$/', $latestCode, $latestMatches)) {
                $latestCodeText = $latestMatches[1];
                $latestCodeNumber = $latestMatches[2];
            }

            /** @var IssueCodesCache $cachedCode */
            $cachedCode = $this->doctrine->getRepository('VitaliiTrackerBundle:IssueCodesCache')
                ->findOneByCode($latestCodeText);

            if (!empty($cachedCode)) {
                $latestCodeNumber = $cachedCode->getNumber();
            } else {
                $cachedCode = new IssueCodesCache();
                $cachedCode->setCode($latestCodeText);
                $em->persist($cachedCode);
            }

            $newCodeNumber = $latestCodeNumber + 1;

            $cachedCode->setNumber($newCodeNumber);
            $em->flush($cachedCode);

            return "$latestCodeText-$newCodeNumber";
        }

        return null;
    }
}
