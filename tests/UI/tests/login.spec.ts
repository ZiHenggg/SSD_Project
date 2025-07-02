import { test, expect } from '@playwright/test'

/**
 * ✅ UI Test: Login Flow (Happy Path Only)
 *
 * Verifies the following:
 * 1. Login page loads
 * 2. Form submission with:
 *    - User with NO 2FA → redirects to `setup_2fa.php`
 *    - User WITH 2FA → redirects to `verify_2fa.php`
 */

test('Login redirects correctly based on 2FA state', async ({ page }) => {
  // 🚫 2FA not enabled → should go to setup_2fa
  await page.goto('http://localhost:8080/login.php');
  await page.fill('input[name="email"]', '1234567@sit.singaporetech.edu.sg');
  await page.fill('input[name="password"]', 'Password123!');
  await Promise.all([
    page.waitForNavigation(),
    page.click('button[type="submit"]'),
  ]);
  await expect(page).toHaveURL(/setup_2fa\.php/);

  // 🔁 Now test user WITH 2FA
  await page.goto('http://localhost:8080/login.php');
  await page.fill('input[name="email"]', '7654321@sit.singaporetech.edu.sg');
  await page.fill('input[name="password"]', 'Password123!');
  await Promise.all([
    page.waitForNavigation(),
    page.click('button[type="submit"]'),
  ]);
  await expect(page).toHaveURL(/verify_2fa\.php/);
});
