<?php

namespace Gl3n\SerializationGroupBundle;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Resolves groups and permissions
 */
class Resolver
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var array
     */
    private $groups;

    /**
     * Constructor
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param array                         $groups
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, array $groups)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->groups = $groups;
    }

    /**
     * Resolves group
     *
     * @param string $groupName
     *
     * @return array An array of group names
     */
    public function resolve($groupName)
    {
        $groupNames = [$groupName];

        if (isset($this->groups[$groupName])) {
            if (0 < count($this->groups[$groupName]['roles']) && !$this->authorizationChecker->isGranted($this->groups[$groupName]['roles'])) {
                throw new AccessDeniedException(
                    sprintf('User has not the required role to use "%s" serialization group', $groupName)
                );
            }
            foreach ($this->groups[$groupName]['include'] as $includedGroupName) {
                $groupNames = array_merge($groupNames, $this->resolve($includedGroupName));
            }
        }

        return $groupNames;
    }
}