<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface
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

        $userApi = new UserApi();
        $userApi->setUser($user)
            ->setOrganization($user->getOrganization())
            ->setApiKey('211dd8446a6d2c6f0517882f1d175f00407f00f3')
        ;
        $em->persist($userApi);

        $em->flush();
    }
}
