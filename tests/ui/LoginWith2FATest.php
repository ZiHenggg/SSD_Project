<?php

/**
 * UI Test for Login and 2FA Flow:
 *
 * 1. ✅ Login with valid credentials → 2FA setup or verify
 * 2. ✅ Complete 2FA → dashboard
 * 3. ❌ Invalid login → error message
 * 4. ❌ Invalid 2FA → error message
 */

require 'vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use RobThree\Auth\TwoFactorAuth;

error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED); // suppress noise

$driver = RemoteWebDriver::create('http://localhost:4444/wd/hub', DesiredCapabilities::chrome());

// Use the fixed secret used by your test user (must match what's in DB)
$test2FASecret = 'S3CR3T4U53R';
$tfa = new TwoFactorAuth('SSD App');

try {
    // STEP 1: Open login page
    $driver->get('http://localhost:8080/login.php');

    // STEP 2: Submit valid login
    $driver->findElement(WebDriverBy::name('email'))->sendKeys('valid_email@sit.singaporetech.edu.sg');
    $driver->findElement(WebDriverBy::name('password'))->sendKeys('validPassword123');
    $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
    sleep(2);

    // STEP 3: Check current page after login
    $urlRaw = $driver->executeScript('return window.location.href');
    $url = is_array($urlRaw) ? '' : (string) $urlRaw;

    if (strpos($url, 'setup_2fa.php') !== false) {
        echo "✅ Redirected to 2FA setup page\n";

        try {
            $qrImage = $driver->findElement(WebDriverBy::cssSelector('img[src*="data:image"]'));
            if ($qrImage->isDisplayed()) {
                echo "✅ QR Code displayed for Google Authenticator\n";
            }
        } catch (Exception $e) {
            echo "❌ QR Code not found: " . $e->getMessage() . "\n";
        }

        $code = $tfa->getCode($test2FASecret); // Generate valid OTP
        $driver->findElement(WebDriverBy::name('code'))->sendKeys($code);
        $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
        sleep(2);

    } elseif (strpos($url, 'verify_2fa.php') !== false) {
        echo "📥 Redirected to 2FA verification page\n";

        $code = $tfa->getCode($test2FASecret); // Generate valid OTP
        $driver->findElement(WebDriverBy::name('code'))->sendKeys($code);
        $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
        sleep(2);
    }

    // STEP 4: Confirm dashboard redirection
    $urlRaw = $driver->executeScript('return window.location.href');
    $url = is_array($urlRaw) ? '' : (string) $urlRaw;

    if (strpos($url, 'dashboard.php') !== false) {
        echo "✅ 2FA verification successful, redirected to dashboard\n";
    } else {
        echo "❌ 2FA verification failed, not redirected to dashboard (URL: $url)\n";
    }

    // STEP 5: Invalid login
    $driver->get('http://localhost:8080/login.php');
    $driver->findElement(WebDriverBy::name('email'))->sendKeys('invalid_email@non-sitdomain.com');
    $driver->findElement(WebDriverBy::name('password'))->sendKeys('wrongPassword');
    $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
    sleep(2);

    try {
        $errorMessage = $driver->findElement(WebDriverBy::cssSelector('.alert-danger'));
        $text = $errorMessage->getText();
        $text = is_array($text) ? implode(', ', $text) : $text;
        echo "✅ Error message displayed: $text\n";
    } catch (Exception $e) {
        echo "❌ Error message not found: " . $e->getMessage() . "\n";
    }

    // STEP 6: Invalid 2FA test
    $driver->get('http://localhost:8080/login.php');
    $driver->findElement(WebDriverBy::name('email'))->sendKeys('valid_email@sit.singaporetech.edu.sg');
    $driver->findElement(WebDriverBy::name('password'))->sendKeys('validPassword123');
    $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
    sleep(2);

    $urlRaw = $driver->executeScript('return window.location.href');
    $url = is_array($urlRaw) ? '' : (string) $urlRaw;

    if (strpos($url, 'verify_2fa.php') !== false) {
        $driver->findElement(WebDriverBy::name('code'))->sendKeys('999999'); // Invalid OTP
        $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
        sleep(2);

        try {
            $errorMessage = $driver->findElement(WebDriverBy::cssSelector('.alert-danger'));
            $text = $errorMessage->getText();
            $text = is_array($text) ? implode(', ', $text) : $text;
            echo "✅ Error message displayed for invalid 2FA code: $text\n";
        } catch (Exception $e) {
            echo "❌ Error message for invalid 2FA code not found: " . $e->getMessage() . "\n";
        }
    } else {
        echo "⚠️ Skipped invalid 2FA test — not redirected to verify_2fa.php\n";
    }

} catch (Exception $e) {
    echo "❌ Test failed with exception: " . $e->getMessage() . "\n";
} finally {
    $driver->quit();
}
