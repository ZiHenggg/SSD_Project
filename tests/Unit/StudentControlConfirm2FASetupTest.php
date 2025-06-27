<?php
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

        // Expect enable2FAForUser to be called once with correct values
        $this->studentRepoMock->expects($this->once())
            ->method('enable2FAForUser')
            ->with($email, $secret);

        $result = $this->studentControl->confirm2FASetup($email, $code, $secret);
        $this->assertTrue($result);
    }

    public function testConfirm2FASetupWithInvalidCode()
    {
        $secret = $this->tfa->createSecret();
        $invalidCode = '000000'; // Shouldn't match unless by coincidence
        $email = 'user@sit.singaporetech.edu.sg';

        // enable2FAForUser should NOT be called
        $this->studentRepoMock->expects($this->never())
            ->method('enable2FAForUser');

        $result = $this->studentControl->confirm2FASetup($email, $invalidCode, $secret);
        $this->assertFalse($result);
    }
}
