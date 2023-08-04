<?php

use twin\helper\Password;

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
