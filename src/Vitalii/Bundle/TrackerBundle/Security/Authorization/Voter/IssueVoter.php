<?php

namespace Vitalii\Bundle\TrackerBundle\Security\Authorization\Voter;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Vitalii\Bundle\TrackerBundle\Entity\Issue;

class IssueVoter
{
    const ACCESS_GRANTED = 1;
    const ACCESS_ABSTAIN = 0;
    const ACCESS_DENIED = -1;

    const SUBTASK = 'subtask';

    /**
     * @return array
     */
    protected function getSupportedAttributes()
    {
        return array(self::SUBTASK);
    }

    /**
     * @return array
     */
    protected function getSupportedClasses()
    {
        return ['Vitalii\Bundle\TrackerBundle\Entity\Issue'];
    }

    /**
     * @param object $object
     * @return bool
     */
    protected function isClassSupported($object)
    {
        $classIsSupported = false;
        foreach ($this->getSupportedClasses() as $supportedClassName) {
            if (is_a($object, $supportedClassName, $allowString = true)) {
                $classIsSupported = true;
                break;
            }
        }

        return $classIsSupported;
    }

    /**
     * @param string $attribute
     * @param Issue $issue
     * @return bool
     */
    protected function isGranted($attribute, $issue)
    {
        switch ($attribute) {
            case self::SUBTASK:
                if ($issue->getType()->getId() === 'story') {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * @param TokenInterface $token
     * @param $object
     * @param array $attributes
     * @return int
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$object || !is_object($object)) {
            return self::ACCESS_ABSTAIN;
        }

        if (!$this->isClassSupported($object)) {
            return self::ACCESS_ABSTAIN;
        }

        foreach ($attributes as $attribute) {
            if (!in_array($attribute, $this->getSupportedAttributes(), $strict = true)) {
                return self::ACCESS_ABSTAIN;
            }
        }

        foreach ($attributes as $attribute) {
            if ($this->isGranted($attribute, $object)) {
                return self::ACCESS_GRANTED;
            }
        }

        return self::ACCESS_DENIED;
    }
}
