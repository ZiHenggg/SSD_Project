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

// Connect to Selenium WebDriver
$driver = RemoteWebDriver::create('http://localhost:4444/wd/hub', DesiredCapabilities::chrome());

try {
    // STEP 1: Open the login page
    $driver->get('http://localhost:8080/login.php');
    
    // --- Login Flow ---
    $driver->findElement(WebDriverBy::name('email'))->sendKeys('valid_email@sit.singaporetech.edu.sg');
    $driver->findElement(WebDriverBy::name('password'))->sendKeys('validPassword123');
    $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
    
    sleep(2); // Wait for redirection

    $urlRaw = $driver->getCurrentURL();
    $url = is_array($urlRaw) && isset($urlRaw['value']) ? $urlRaw['value'] : (string) $urlRaw;

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

        // STEP 4: Enter OTP for 2FA setup
        $driver->findElement(WebDriverBy::name('code'))->sendKeys('123456'); // Simulated OTP
        $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
        sleep(2);
    }

    // Dashboard redirect case (already has 2FA)
    $urlRaw = $driver->getCurrentURL();
    $url = is_array($urlRaw) && isset($urlRaw['value']) ? $urlRaw['value'] : (string) $urlRaw;

    if (strpos($url, 'dashboard.php') !== false) {
        echo "✅ Already logged in and redirected to the dashboard\n";
    }

    // --- 2FA Verification Flow ---
    $driver->get('http://localhost:8080/verify_2fa.php');
    $driver->findElement(WebDriverBy::name('code'))->sendKeys('123456'); // Simulated OTP
    $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
    sleep(2);

    $urlRaw = $driver->getCurrentURL();
    $url = is_array($urlRaw) && isset($urlRaw['value']) ? $urlRaw['value'] : (string) $urlRaw;

    if (strpos($url, 'dashboard.php') !== false) {
        echo "✅ 2FA verification successful, redirected to dashboard\n";
    } else {
        echo "❌ 2FA verification failed, not redirected to dashboard (URL: $url)\n";
    }

    // --- Negative Test for Invalid Login ---
    $driver->get('http://localhost:8080/login.php');
    $driver->findElement(WebDriverBy::name('email'))->sendKeys('invalid_email@non-sitdomain.com');
    $driver->findElement(WebDriverBy::name('password'))->sendKeys('wrongPassword');
    $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
    sleep(2);

    try {
        $errorMessage = $driver->findElement(WebDriverBy::cssSelector('.alert-danger'));
        $text = is_array($errorMessage->getText()) ? implode(', ', $errorMessage->getText()) : $errorMessage->getText();
        echo "✅ Error message displayed: $text\n";
    } catch (Exception $e) {
        echo "❌ Error message not found: " . $e->getMessage() . "\n";
    }

    // --- Negative Test for Invalid 2FA Code ---
    $driver->get('http://localhost:8080/verify_2fa.php');
    $driver->findElement(WebDriverBy::name('code'))->sendKeys('999999');
    $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
    sleep(2);

    try {
        $errorMessage = $driver->findElement(WebDriverBy::cssSelector('.alert-danger'));
        $text = is_array($errorMessage->getText()) ? implode(', ', $errorMessage->getText()) : $errorMessage->getText();
        echo "✅ Error message displayed for invalid 2FA code: $text\n";
    } catch (Exception $e) {
        echo "❌ Error message for invalid 2FA code not found: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "❌ Test failed with exception: " . $e->getMessage() . "\n";
} finally {
    $driver->quit();
}

?>
