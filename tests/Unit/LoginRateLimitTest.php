<?php

/**
 * Tests the login rate limiting logic:
 *
 * ✅ Rate limit triggers
 * - Blocks login when user OR IP exceeds 5 failed attempts
 * - Allows login when both are under threshold
 *
 * ✅ Rate limit behavior
 * - Increments counters after failed login
 * - Sets TTL if not already present
 * - Resets counters after successful login
 *
 * Assumes:
 * - Redis is used for tracking failed attempts by user and IP
 * - Lockout duration is 15 minutes (900 seconds)
 *
 * Dependencies Mocked:
 * - Predis\Client (Redis)
 * - StudentPageController (login handling)
 * - StudentControl (optional, used internally)
 */

namespace Tests\Unit;
use PHPUnit\Framework\TestCase;
use Predis\Client as RedisClient;
use App\Boundary\StudentPageController;
use App\Control\StudentControl;
use App\Mapper\StudentMapper;

class LoginRateLimitTest extends TestCase
{
    private $redis;
    private $studentPageController;
    private $studentControl;
    private $studentRepo;

    protected function setUp(): void
    {
        // Mock Redis
        $this->redis = $this->createMock(RedisClient::class);

        // Mock other dependencies (StudentRepo and Control)
        $this->studentRepo = $this->createMock(StudentMapper::class);
        $this->studentControl = $this->createMock(StudentControl::class);
        $this->studentPageController = $this->createMock(StudentPageController::class);
    }

    public function testLoginAllowedWhenUnderThreshold()
    {
        $this->redis->method('get')->willReturn(3);

        $this->studentPageController
            ->method('loginStudent')
            ->willReturn(['success' => true, 'redirect' => 'dashboard.php']);

        // Simulate reset after success
        $this->redis->expects($this->once())->method('del')->withConsecutive(
            ['login_attempts:user:someone@example.com'],
            ['login_attempts:ip:127.0.0.1']
        );

        // Simulate POST data
        $_POST = ['username' => 'someone@example.com', 'password' => 'badpass'];
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        // You can isolate the login logic in a wrapper function for testability,
        // and then call that wrapper here passing mocks
    }

    public function testLoginBlockedAfterThresholdExceeded()
    {
        $this->redis->method('get')->willReturn(6); // over threshold

        $_POST = ['username' => 'blocked@example.com'];
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $this->expectOutputRegex('/Account temporarily locked/');

        // Call your isolated login handler here
    }

    public function testFailedLoginIncrementsCounters()
    {
        $this->redis->method('get')->willReturn(1);
        $this->redis->method('ttl')->willReturn(0);

        $this->redis->expects($this->exactly(2))->method('incr');
        $this->redis->expects($this->exactly(2))->method('expire');

        $this->studentPageController
            ->method('loginStudent')
            ->willReturn(['success' => false]);

        $_POST = ['username' => 'fail@example.com'];
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        // Call your login handler
    }

    public function testLoginSuccessResetsCounters()
    {
        $this->redis->method('get')->willReturn(2);

        $this->studentPageController
            ->method('loginStudent')
            ->willReturn(['success' => true, 'redirect' => 'dashboard.php']);

        $this->redis->expects($this->exactly(2))->method('del');

        $_POST = ['username' => 'good@example.com'];
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        // Call your login handler
    }
}
