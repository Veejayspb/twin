<?php

use twin\helper\Password;
use twin\test\helper\BaseTestCase;

final class PasswordTest extends BaseTestCase
{
    public function testCheck()
    {
        $password = 'password';
        $hash = Password::hash($password);

        $result = Password::check($password, $hash);
        $this->assertTrue($result);

        $result = Password::check('wrong pass', $hash);
        $this->assertFalse($result);
    }
}
