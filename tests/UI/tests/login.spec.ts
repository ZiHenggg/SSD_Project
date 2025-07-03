import { test, expect } from '@playwright/test'
import { authenticator } from 'otplib'

/**
 * ✅ UI Test: Login + 2FA Redirect and Verification
 *
 * Verifies:
 * 1. 2FA-enabled user logs in → redirected to /verify_2fa.php → enters OTP → dashboard
 * 2. Non-2FA user logs in → redirected to /setup_2fa.php
 */

test('Login and OTP behavior for 2FA and non-2FA users', async ({ page }) => {
  // 🔐 2FA-enabled user
  await page.goto('http://localhost:8080/login.php')
  await page.fill('input[name="email"]', '1234567@sit.singaporetech.edu.sg')
  await page.fill('input[name="password"]', 'groupmates12345678')
  await page.click('button[type="submit"]')

  // Wait for redirect to 2FA page
  await page.waitForURL(/verify_2fa\.php/)
  await expect(page).toHaveURL(/verify_2fa\.php/)

  // Generate valid OTP using known secret
  const otp = authenticator.generate('CGLX4LIL4VLVTSBE') // Replace with actual test user's secret
  await page.fill('input[name="code"]', otp)
  await page.click('button[type="submit"]')

  // Expect redirect to dashboard (or wherever 2FA success goes)
  await page.waitForURL(/dashboard\.php/)
  await expect(page).toHaveURL(/dashboard\.php/)

  await page.goto('http://localhost:8080/logout.php');

  // 🔓 Non-2FA user
  await page.goto('http://localhost:8080/login.php')
  await page.waitForSelector('input[name="email"]');
  await page.fill('input[name="email"]', '7654321@sit.singaporetech.edu.sg')
  await page.fill('input[name="password"]', 'groupmates12345678')
  await page.click('button[type="submit"]')

  await page.waitForURL(/setup_2fa\.php/)
  await expect(page).toHaveURL(/setup_2fa\.php/)
})
