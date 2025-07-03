import { test, expect } from '@playwright/test';

/**
 * ✅ UI Test: Login Redirect Behavior
 *
 * Verifies login redirects based on user’s 2FA state:
 *
 * 1. User without 2FA is redirected to /setup_2fa.php
 * 2. User with 2FA is redirected to /verify_2fa.php
 */

test('Login redirects correctly based on 2FA state', async ({ page }) => {
  // 🔐 Test user WITHOUT 2FA
  await page.goto('http://localhost:8080/login.php');
  await page.fill('input[name="email"]', '1234567@sit.singaporetech.edu.sg');
  await page.fill('input[name="password"]', 'groupmates12345678');
  await Promise.all([
    page.waitForNavigation(),
    page.click('button[type="submit"]'),
  ]);
  await expect(page).toHaveURL(/setup_2fa\.php/);

  // 🔐 Test user WITH 2FA enabled
  await page.goto('http://localhost:8080/login.php');
  await page.fill('input[name="email"]', '7654321@sit.singaporetech.edu.sg');
  await page.fill('input[name="password"]', 'groupmates12345678');
  await Promise.all([
    page.waitForNavigation(),
    page.click('button[type="submit"]'),
  ]);
  await expect(page).toHaveURL(/verify_2fa\.php/);
});
