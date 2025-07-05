<?php

/**
 * StudentControlConfirm2FASetupTest
 *
 * This test suite verifies the logic for confirming 2FA setup after scanning the QR code.
 *
 * Covers:
 * - confirm2FASetup() with valid TOTP code
 * - confirm2FASetup() with invalid code
 *
 * Ensures:
 * - Correct codes trigger persistence of 2FA secret
 * - Incorrect codes are rejected and nothing is persisted
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Control\StudentControl;
use App\Repository\StudentRepository;
use RobThree\Auth\TwoFactorAuth;

class StudentControlConfirm2FASetupTest extends TestCase
{
    private $studentRepoMock;
    private $studentControl;
    private $tfa;

    protected function setUp(): void
    {
        $this->studentRepoMock = $this->createMock(StudentRepository::class);
        $this->studentControl = new StudentControl($this->studentRepoMock);
        $this->tfa = new TwoFactorAuth('SSD App');
        $_SESSION = [];
    }

    public function testConfirm2FASetupWithCorrectCode()
    {
        $secret = $this->tfa->createSecret();
        $code = $this->tfa->getCode($secret);
        $email = 'user@sit.singaporetech.edu.sg';

        $this->studentRepoMock->expects($this->once())
            ->method('enable2FAForUser')
            ->with($email, $secret);

        $result = $this->studentControl->confirm2FASetup($email, $code, $secret);
        $this->assertTrue($result);
    }

    public function testConfirm2FASetupWithInvalidCode()
    {
        $secret = $this->tfa->createSecret();
        $invalidCode = '000000';
        $email = 'user@sit.singaporetech.edu.sg';

        $this->studentRepoMock->expects($this->never())
            ->method('enable2FAForUser');

        $result = $this->studentControl->confirm2FASetup($email, $invalidCode, $secret);
        $this->assertFalse($result);
    }
}
