<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Vitalii\Bundle\TrackerBundle\Entity\Issue;

class LoadIssueData extends AbstractFixture implements ContainerAwareInterface
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
    public function load(ObjectManager $manager)
    {
        $em = $this->container->get('doctrine')->getManager();
        /** @var User $user */
        $user = $manager->getRepository('OroUserBundle:User')->findOneByUsername('admin');

        $user2 = new User();
        $user2->setUsername('user2');
        $user2->setEmail('user2@example.com');
        $user2->setFirstName('Audrey');
        $user2->setLastName('Vaughan');
        $user2->setPassword('user2');
        $user2->setOrganization($user->getOrganization());
        $em->persist($user2);

        $typeClassName = ExtendHelper::buildEnumValueClassName('issue_type');
        $typeStory = $manager->getRepository($typeClassName)->findOneById('story');
        $typeTask = $manager->getRepository($typeClassName)->findOneById('task');
        $typeSubtask = $manager->getRepository($typeClassName)->findOneById('subtask');

        $priorityClassName = ExtendHelper::buildEnumValueClassName('issue_priority');
        $priorityNormal = $manager->getRepository($priorityClassName)->findOneById('normal');
        $priorityHigh = $manager->getRepository($priorityClassName)->findOneById('high');

        $issue1 = new Issue();
        $issue1->setCode('test-01')
            ->setSummary('First test issue')
            ->setDescription('Some arbitrary description')
            ->setReporter($user)
            ->setAssignee($user)
            ->setOrganization($user->getOrganization())
            ->setCollaborators(new ArrayCollection())
            ->setType($typeStory)
            ->setPriority($priorityNormal)
        ;
        $em->persist($issue1);

        $issue2 = new Issue();
        $issue2->setCode('test-02')
            ->setSummary('Second test issue')
            ->setReporter($user)
            ->setAssignee($user)
            ->setOrganization($user->getOrganization())
            ->setCollaborators(new ArrayCollection())
            ->setType($typeTask)
            ->setPriority($priorityHigh)
        ;
        $em->persist($issue2);

        $issue3 = new Issue();
        $issue3->setCode('test-03')
            ->setSummary('Subtask for the story')
            ->setReporter($user2)
            ->setAssignee($user)
            ->setOrganization($user->getOrganization())
            ->setCollaborators(new ArrayCollection())
            ->setType($typeSubtask)
            ->setPriority($priorityHigh)
            ->setParentIssue($issue1)
        ;
        $em->persist($issue3);

        $em->flush();
    }
}
