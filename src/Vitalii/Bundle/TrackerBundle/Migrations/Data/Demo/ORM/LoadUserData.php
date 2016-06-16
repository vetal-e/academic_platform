<?php

namespace Vitalii\Bundle\TrackerBundle\Migrations\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadRolesData;
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
        $userManager = $this->container->get('oro_user.manager');

        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
        $businessUnit = $manager
            ->getRepository('OroOrganizationBundle:BusinessUnit')
            ->findOneBy(['name' => LoadOrganizationAndBusinessUnitData::MAIN_BUSINESS_UNIT]);

        $adminRole = $manager->getRepository('OroUserBundle:Role')
            ->findOneBy(['role' => LoadRolesData::ROLE_ADMINISTRATOR]);
        $userAdmin = $manager->getRepository('OroUserBundle:User')->findOneByUsername('admin');
        if (empty($userAdmin)) {
            /** @var User $userAdmin */
            $userAdmin = $userManager->createUser();
            $userAdmin
                ->setUsername('admin')
                ->setEmail('admin@example.com')
                ->setEnabled(true)
                ->setPlainPassword('admin')
                ->addRole($adminRole)
                ->setOrganization($organization)
                ->addOrganization($organization)
                ->addBusinessUnit($businessUnit)
                ->setOwner($businessUnit)
                ->setFirstName('John')
                ->setLastName('Doe');

            $userManager->updateUser($userAdmin);
        }

        /** @var User $userVitaly */
        $userVitaly = $userManager->createUser();
        $userVitaly
            ->setUsername('vitaly')
            ->setEmail('vitaly@example.com')
            ->setEnabled(true)
            ->setPlainPassword('vitaly')
            ->setOrganization($organization)
            ->addOrganization($organization)
            ->addBusinessUnit($businessUnit)
            ->setFirstName('Vitaly')
            ->setLastName('Eryomenko');

        $userManager->updateUser($userVitaly);

        /** @var User $userSergey */
        $userSergey = $userManager->createUser();
        $userSergey
            ->setUsername('sergey')
            ->setEmail('sergey@example.com')
            ->setEnabled(true)
            ->setPlainPassword('sergey')
            ->setOrganization($organization)
            ->addOrganization($organization)
            ->addBusinessUnit($businessUnit)
            ->setFirstName('Sergey')
            ->setLastName('Zhuravel');

        $userManager->updateUser($userSergey);

        $this->setReference($userVitaly->getUsername(), $userVitaly);
        $this->setReference($userSergey->getUsername(), $userSergey);

        $em->flush();
    }
}
