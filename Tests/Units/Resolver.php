<?php

namespace Gl3n\SerializationGroupBundle\Tests\Units;

use mageekguy\atoum;
use Prophecy\Argument;
use Gl3n\SerializationGroupBundle;

/**
 * Tests on Resolver
 */
class Resolver extends atoum\test
{
    /**
     * @var \Prophecy\Prophet
     */
    private $prophet;

    /**
     * Groups configuration
     *
     * @var array
     */
    private static $groups = [
        'group2' => [
            'roles' => ['ROLE_USER'],
            'include' => []
        ],
        'group3' => [
            'roles' => ['ROLE_ADMIN'],
            'include' => ['group2']
        ],
        'group4' => [
            'roles' => ['ROLE_SUPER_ADMIN'],
            'include' => ['group1', 'group3']
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function beforeTestMethod($testMethod)
    {
        $this->prophet = new \Prophecy\Prophet;
    }

    /**
     * Tests "resolve" method without security checking
     */
    public function test_resolve_withoutSecurity()
    {
        $authorizationChecker = $this->prophet->prophesize('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
        $authorizationChecker->isGranted(Argument::any())->willReturn(true);

        $resolver = new SerializationGroupBundle\Resolver($authorizationChecker->reveal(), self::$groups);

        // Group 1
        $this
            ->array($resolver->resolve('group1'))
            ->hasSize(1)
            ->containsValues(['group1']);

        // Group 2
        $this
            ->array($resolver->resolve('group2'))
            ->hasSize(1)
            ->containsValues(['group2']);

        // Group 3
        $this
            ->array($resolver->resolve('group3'))
            ->hasSize(2)
            ->containsValues(['group2', 'group3']);

        // Group 4
        $this
            ->array($resolver->resolve('group4'))
            ->hasSize(4)
            ->containsValues(['group1', 'group2', 'group3', 'group4']);
    }

    /**
     * Tests "resolve" method with security checking
     */
    public function test_resolve_withSecurity()
    {
        $authorizationChecker = $this->prophet->prophesize('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
        $authorizationChecker->isGranted(['ROLE_USER'])->willReturn(true);
        $authorizationChecker->isGranted(['ROLE_ADMIN'])->willReturn(true);
        $authorizationChecker->isGranted(['ROLE_SUPER_ADMIN'])->willReturn(false);

        $resolver = new SerializationGroupBundle\Resolver($authorizationChecker->reveal(), self::$groups);

        $resolver->resolve('group1');
        $resolver->resolve('group2');
        $resolver->resolve('group3');
        $this
            ->exception(function() use ($resolver)
            {
                $resolver->resolve('group4');
            })
            ->isInstanceOf('Symfony\Component\Security\Core\Exception\AccessDeniedException');
    }
}
