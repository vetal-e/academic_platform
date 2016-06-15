<?php

namespace Vitalii\Bundle\TrackerBundle\Migrations\Demo\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Vitalii\Bundle\TrackerBundle\Entity\Issue;

class LoadIssueData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['Vitalii\Bundle\TrackerBundle\Migrations\Demo\ORM\LoadUserData'];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $em = $this->container->get('doctrine')->getManager();

        /** @var User $userAdmin */
        $userAdmin = $manager->getRepository('OroUserBundle:User')->findOneByUsername('admin');
        /** @var User $userVitaly */
        $userVitaly = $this->getReference('vitaly');
        /** @var User $userSergey */
        $userSergey = $this->getReference('sergey');

        $typeClassName = ExtendHelper::buildEnumValueClassName('issue_type');
        $typeStory = $manager->getRepository($typeClassName)->findOneById('story');
        $typeTask = $manager->getRepository($typeClassName)->findOneById('task');
        $typeSubtask = $manager->getRepository($typeClassName)->findOneById('subtask');

        $priorityClassName = ExtendHelper::buildEnumValueClassName('issue_priority');
        $priorityNormal = $manager->getRepository($priorityClassName)->findOneById('normal');
        $priorityHigh = $manager->getRepository($priorityClassName)->findOneById('high');

        $issueStory = new Issue();
        $issueStory->setCode('demo-1')
            ->setSummary('Demo story')
            ->setDescription('Some arbitrary description')
            ->setReporter($userAdmin)
            ->setAssignee($userSergey)
            ->setOrganization($userAdmin->getOrganization())
            ->setType($typeStory)
            ->setPriority($priorityNormal)
            ->setCollaborators(new ArrayCollection())
        ;
        $em->persist($issueStory);
        $this->setReference('issueStory', $issueStory);

        $issueSubtask1 = new Issue();
        $issueSubtask1->setCode('demo-2')
            ->setSummary('Demo subtask for the story')
            ->setReporter($userVitaly)
            ->setAssignee($userVitaly)
            ->setOrganization($userVitaly->getOrganization())
            ->setType($typeSubtask)
            ->setPriority($priorityHigh)
            ->setCollaborators(new ArrayCollection())
            ->setParentIssue($issueStory)
        ;
        $em->persist($issueSubtask1);
        $this->setReference('issueSubtask1', $issueSubtask1);

        $issueSubtask2 = new Issue();
        $issueSubtask2->setCode('demo-4')
            ->setSummary('Another demo subtask')
            ->setReporter($userSergey)
            ->setAssignee($userSergey)
            ->setOrganization($userSergey->getOrganization())
            ->setType($typeSubtask)
            ->setPriority($priorityHigh)
            ->setCollaborators(new ArrayCollection())
            ->setParentIssue($issueStory)
        ;
        $em->persist($issueSubtask2);
        $this->setReference('issueSubtask2', $issueSubtask2);

        $issueTask = new Issue();
        $issueTask->setCode('demo-3')
            ->setSummary('Demo task')
            ->setReporter($userSergey)
            ->setAssignee($userVitaly)
            ->setOrganization($userSergey->getOrganization())
            ->setType($typeTask)
            ->setPriority($priorityNormal)
            ->setCollaborators(new ArrayCollection())
        ;
        $em->persist($issueTask);
        $this->setReference('issueTask', $issueTask);

        $em->flush();
    }
}