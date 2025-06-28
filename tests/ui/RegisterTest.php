<?php

/**
 * UI Test: Registration + OTP + Resend OTP
 *
 * ✅ Registration Form
 * - Fills student ID, name, SIT email, password
 * - Submits form and expects OTP page
 *
 * ✅ OTP Verification
 * - Enters simulated OTP (123456)
 * - Clicks "Verify" to complete registration
 *
 * ✅ OTP Resend
 * - Clicks "Resend OTP" button
 * - Confirms page still stays on OTP input
 *
 * 🛠 Modal or redirect is optional — modal appears before redirect to login.php
 * - If redirected to login → ✅
 * - Else, success modal must be visible
 */

require 'vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

// Connect to Selenium Chrome container
$driver = RemoteWebDriver::create('http://localhost:4444/wd/hub', DesiredCapabilities::chrome());

try {
    // STEP 1: Open registration page (back to localhost)
    $driver->get('http://host.docker.internal:8080/register.php');

    // STEP 2: Fill out the registration form
    $driver->findElement(WebDriverBy::id('studentId'))->sendKeys('1234567');
    $driver->findElement(WebDriverBy::id('studentName'))->sendKeys('John Test');
    $driver->findElement(WebDriverBy::id('email'))->sendKeys('john_test@sit.singaporetech.edu.sg');
    $driver->findElement(WebDriverBy::id('password'))->sendKeys('TestPassword123!');
    $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();

    // STEP 3: Wait for OTP input field to appear
    sleep(2);

    $otpInput = $driver->findElement(WebDriverBy::id('otp'));
    if ($otpInput) {
        echo "✅ OTP input is visible — form step success.\n";
    }

    // STEP 4: Test Resend OTP
    $driver->findElement(WebDriverBy::cssSelector('form[action="process_register.php"] input[name="resend_otp"] + button'))->click();
    echo "🔁 Resend OTP button clicked.\n";

    sleep(1); // Let page reload with message

    // STEP 5: Re-enter OTP after resend
    $driver->findElement(WebDriverBy::id('otp'))->clear();
    $driver->findElement(WebDriverBy::id('otp'))->sendKeys('123456'); // Simulated OTP
    $driver->findElement(WebDriverBy::cssSelector('button.btn-success'))->click();

    // STEP 6: Wait for success modal or login redirect
    sleep(5);

    $currentUrlRaw = $driver->getCurrentURL();
    $currentUrl = is_array($currentUrlRaw) && isset($currentUrlRaw['value'])
        ? $currentUrlRaw['value']
        : (string) $currentUrlRaw;

    // Try to locate success modal
    try {
        $modal = $driver->findElement(WebDriverBy::id('registerSuccessModal'));
        if ($modal && $modal->isDisplayed()) {
            echo "✅ Registration completed → success modal is visible.\n";
        }
    } catch (Exception $e) {
        // Modal not found, fallback to URL check
        if (strpos($currentUrl, 'login.php') !== false) {
            echo "✅ Registration completed → redirected to login.\n";
        } else {
            echo "❌ No modal or redirect found. URL: $currentUrl\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Test failed with exception: " . $e->getMessage() . "\n";
} finally {
    $driver->quit();
}
