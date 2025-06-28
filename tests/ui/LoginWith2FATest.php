<?php

/**
 * UI Test for Login and 2FA Flow:
 *
 * 1. **Login Flow:**
 *    ✅ Enter valid email and password → Verify successful login and redirect to 2FA setup or dashboard.
 *    ✅ Enter invalid email or password → Verify error message.
 *
 * 2. **2FA Setup Flow:**
 *    ✅ Check for the QR Code generated for Google Authenticator.
 *    ✅ Simulate entering a valid OTP for 2FA setup → Verify redirection to 2FA verification page.
 *
 * 3. **2FA Verification Flow:**
 *    ✅ Simulate entering a valid 6-digit OTP → Verify successful login and redirect to the dashboard.
 *    ❌ Simulate entering an invalid 6-digit OTP → Verify error message.
 */

require 'vendor/autoload.php';
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverWait;
use Facebook\WebDriver\WebDriverExpectedCondition;

// Connect to Selenium WebDriver
$driver = RemoteWebDriver::create('http://localhost:4444/wd/hub', DesiredCapabilities::chrome());

try {
    // STEP 1: Open the login page
    $driver->get('http://localhost:8080/login.php');
    
    // --- Login Flow ---
    // STEP 2: Enter valid email and password
    $driver->findElement(WebDriverBy::name('email'))->sendKeys('valid_email@sit.singaporetech.edu.sg');
    $driver->findElement(WebDriverBy::name('password'))->sendKeys('validPassword123');
    $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
    
    // Wait for redirection
    $wait = new WebDriverWait($driver, 10);  // 10 seconds timeout
    $wait->until(WebDriverExpectedCondition::urlContains('setup_2fa.php'));  // wait until the URL contains 'setup_2fa.php'

    // STEP 3: Check if redirected to the 2FA setup page
    $url = $driver->getCurrentURL();
    if (is_string($url) && strpos($url, 'setup_2fa.php') !== false) {
        echo "✅ Redirected to 2FA setup page\n";
        
        // Check for QR code visibility
        try {
            $qrImage = $wait->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('img[src*="data:image"]'))
            );
            echo "✅ QR Code displayed for Google Authenticator\n";
        } catch (Exception $e) {
            echo "❌ QR Code not found: " . $e->getMessage() . "\n";
        }

        // STEP 4: Enter OTP for 2FA setup
        $driver->findElement(WebDriverBy::name('code'))->sendKeys('123456'); // Simulate OTP
        $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();

        // Wait for redirection to verify 2FA
        $wait->until(WebDriverExpectedCondition::urlContains('verify_2fa.php'));
    }

    // --- 2FA Verification Flow ---
    // STEP 5: Simulate entering the correct OTP for verification
    $driver->get('http://localhost:8080/verify_2fa.php');
    $driver->findElement(WebDriverBy::name('code'))->sendKeys('123456'); // Simulate OTP
    $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
    
    // STEP 6: Verify successful redirection to dashboard
    $wait->until(WebDriverExpectedCondition::urlContains('dashboard.php'));
    $url = $driver->getCurrentURL();
    if (strpos($url, 'dashboard.php') !== false) {
        echo "✅ 2FA verification successful, redirected to dashboard\n";
    } else {
        echo "❌ 2FA verification failed, not redirected to dashboard.\n";
    }

    // --- Negative Test for Invalid Login ---
    // STEP 7: Invalid email and password (invalid domain)
    $driver->get('http://localhost:8080/login.php');
    $driver->findElement(WebDriverBy::name('email'))->sendKeys('invalid_email@non-sitdomain.com'); // Invalid email domain
    $driver->findElement(WebDriverBy::name('password'))->sendKeys('wrongPassword');
    $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();

    // Wait for error message
    $errorMessage = $wait->until(
        WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('.alert-danger'))
    );
    echo "✅ Error message displayed: " . $errorMessage->getText() . "\n";

    // --- Negative Test for Invalid 2FA Code ---
    // STEP 8: Invalid 2FA code
    $driver->get('http://localhost:8080/verify_2fa.php');
    $driver->findElement(WebDriverBy::name('code'))->sendKeys('999999'); // Invalid OTP
    $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
    
    // Wait for error message for invalid OTP
    $errorMessage = $wait->until(
        WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('.alert-danger'))
    );
    echo "✅ Error message displayed for invalid 2FA code: " . $errorMessage->getText() . "\n";

} catch (Exception $e) {
    echo "❌ Test failed with exception: " . $e->getMessage() . "\n";
} finally {
    $driver->quit();
}

?>
