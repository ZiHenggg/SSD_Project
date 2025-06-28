<?php

/**
 * UI Test for Login and 2FA Flow
 * Covers:
 * ✅ Login with valid creds → 2FA setup or verify
 * ✅ Enter valid OTP → dashboard
 * ❌ Invalid login → error
 * ❌ Invalid OTP → error
 */

require 'vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use RobThree\Auth\TwoFactorAuth;

error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED); // suppress noise

$driver = RemoteWebDriver::create('http://localhost:4444/wd/hub', DesiredCapabilities::chrome());
$tfa = new TwoFactorAuth('SSD App');

// 🔐 Your test user secret (must match DB)
$test2FASecret = 'S3CR3T4U53R';

try {
    // ==== STEP 1: Login ====
    $driver->get('http://localhost:8080/login.php');
    $driver->findElement(WebDriverBy::name('email'))->sendKeys('valid_email@sit.singaporetech.edu.sg');
    $driver->findElement(WebDriverBy::name('password'))->sendKeys('validPassword123');
    $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();

    // Wait for next page (setup or verify)
    $driver->wait(5)->until(
        WebDriverExpectedCondition::or_(
            WebDriverExpectedCondition::urlContains('setup_2fa.php'),
            WebDriverExpectedCondition::urlContains('verify_2fa.php'),
            WebDriverExpectedCondition::urlContains('dashboard.php')
        )
    );

    $url = $driver->getCurrentURL();

    if (strpos($url, 'setup_2fa.php') !== false) {
        echo "✅ Redirected to 2FA setup\n";

        // QR Check (optional)
        try {
            $qrImage = $driver->findElement(WebDriverBy::cssSelector('img[src*="data:image"]'));
            if ($qrImage->isDisplayed()) echo "✅ QR Code is visible\n";
        } catch (Exception $e) {
            echo "⚠️ QR not found: " . $e->getMessage() . "\n";
        }

        // Enter valid 2FA code to complete setup
        $code = $tfa->getCode($test2FASecret);
        $driver->findElement(WebDriverBy::name('code'))->sendKeys($code);
        $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
        $driver->wait(5)->until(WebDriverExpectedCondition::urlContains('dashboard.php'));
        echo "✅ 2FA setup and login successful\n";

    } elseif (strpos($url, 'verify_2fa.php') !== false) {
        echo "📥 Reached 2FA verification\n";
        $code = $tfa->getCode($test2FASecret);
        $driver->findElement(WebDriverBy::name('code'))->sendKeys($code);
        $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
        $driver->wait(5)->until(WebDriverExpectedCondition::urlContains('dashboard.php'));
        echo "✅ 2FA verification successful, redirected to dashboard\n";

    } elseif (strpos($url, 'dashboard.php') !== false) {
        echo "✅ Already logged in, redirected to dashboard\n";
    } else {
        echo "❌ Unexpected redirect after login: $url\n";
    }

    // ==== STEP 2: Invalid login ====
    $driver->get('http://localhost:8080/login.php');
    $driver->findElement(WebDriverBy::name('email'))->sendKeys('invalid_email@non-sitdomain.com');
    $driver->findElement(WebDriverBy::name('password'))->sendKeys('wrongPassword');
    $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
    sleep(1);

    try {
        $msg = $driver->findElement(WebDriverBy::cssSelector('.alert-danger'))->getText();
        echo "✅ Error shown for invalid login: $msg\n";
    } catch (Exception $e) {
        echo "❌ No error shown for invalid login\n";
    }

    // ==== STEP 3: Invalid 2FA ====
    $driver->get('http://localhost:8080/login.php');
    $driver->findElement(WebDriverBy::name('email'))->sendKeys('valid_email@sit.singaporetech.edu.sg');
    $driver->findElement(WebDriverBy::name('password'))->sendKeys('validPassword123');
    $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();

    $driver->wait(5)->until(WebDriverExpectedCondition::urlContains('verify_2fa.php'));
    $driver->findElement(WebDriverBy::name('code'))->sendKeys('999999');
    $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
    sleep(1);

    try {
        $msg = $driver->findElement(WebDriverBy::cssSelector('.alert-danger'))->getText();
        echo "✅ Error shown for invalid 2FA: $msg\n";
    } catch (Exception $e) {
        echo "❌ No error shown for invalid 2FA\n";
    }

} catch (Exception $e) {
    echo "❌ Test crashed: " . $e->getMessage() . "\n";
} finally {
    $driver->quit();
}
