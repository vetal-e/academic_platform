<?php

namespace Vitalii\Bundle\TrackerBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class LoadIssueResolutionData extends AbstractFixture
{
    /** @var array */
    protected $data = [
        'Done' => false,
        'Fixed' => false,
        'Duplicate' => false,
        'Incomplete' => false,
        'Cannot reproduce' => false,
        'Rejected' => false,
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName('issue_resolution');

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);

        $priority = 1;
        foreach ($this->data as $name => $isDefault) {
            $enumOption = $enumRepo->createEnumValue($name, $priority++, $isDefault);
            $manager->persist($enumOption);
        }

        $manager->flush();
    }
}
