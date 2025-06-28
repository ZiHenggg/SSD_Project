<?php
require 'vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

// Connect to Selenium Chrome container
$driver = RemoteWebDriver::create('http://localhost:4444/wd/hub', DesiredCapabilities::chrome());

try {
    // STEP 1: Open registration page
    $driver->get('http://localhost:8080/register.php');

    // STEP 2: Fill out the registration form
    $driver->findElement(WebDriverBy::id('studentId'))->sendKeys('1234567');
    $driver->findElement(WebDriverBy::id('studentName'))->sendKeys('John Test');
    $driver->findElement(WebDriverBy::id('email'))->sendKeys('john_test@sit.singaporetech.edu.sg');
    $driver->findElement(WebDriverBy::id('password'))->sendKeys('TestPassword123!');
    $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();

    // STEP 3: Wait for OTP page
    sleep(2); // Let PHP session redirect and render new state

    $otpInput = $driver->findElement(WebDriverBy::id('otp'));
    if ($otpInput) {
        echo "✅ OTP input is visible — form step success.\n";
    }

    // STEP 4: Enter OTP (we simulate with correct format, real value mocked)
    $driver->findElement(WebDriverBy::id('otp'))->sendKeys('123456'); // Adjust if mocking OTP
    $driver->findElement(WebDriverBy::cssSelector('button.btn-success'))->click();

    // STEP 5: Wait for success modal or redirect
    sleep(2);

    $currentUrlRaw = $driver->getCurrentURL();
    $currentUrl = is_array($currentUrlRaw) && isset($currentUrlRaw['value']) 
        ? $currentUrlRaw['value'] 
        : (string) $currentUrlRaw;

    if (strpos($currentUrl, 'login.php') !== false) {
        echo "✅ Registration completed → redirected to login.\n";
    } else {
        echo "❌ Not redirected yet → check OTP or modal logic. Current URL: $currentUrl\n";
    }

} catch (Exception $e) {
    echo "❌ Test failed with exception: " . $e->getMessage() . "\n";
} finally {
    $driver->quit();
}
