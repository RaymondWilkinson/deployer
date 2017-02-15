<?php

namespace REBELinBLUE\Deployer\Tests;

use REBELinBLUE\Deployer\User;

/**
 * Abstract class to set the user on all requests.
 */
abstract class AuthenticatedTestCase extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $user = factory(User::class)->create();

        $this->actingAs($user)->seeIsAuthenticated();
    }
}