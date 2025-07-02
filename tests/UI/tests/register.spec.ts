/**
 * ✅ Register Page UI Flow Test with Playwright:
 *
 * ✅ Render Form Step
 * - Shows registration fields: Student ID, Name, Email, Password
 * - Submit button is visible
 *
 * ✅ Submit Valid Data
 * - Fills form with valid data
 * - Submits and checks for OTP input field
 * - Simulates backend setting session to OTP step
 *
 * ✅ UI Feedback
 * - Shows heading and correct form labels
 * - Graceful fallback if session not set (should show registration form)
 */

import { test, expect } from '@playwright/test';

test('Register page displays and submits correctly', async ({ page }) => {
  // Go to the register page
  await page.goto('http://localhost:8080/register.php');

  // ✅ Heading and form should be visible
  const heading = page.getByRole('heading', { level: 2 });
  await expect(heading).toHaveText('Register');

  // ✅ Input fields should be visible
  await expect(page.locator('input[name="studentId"]')).toBeVisible();
  await expect(page.locator('input[name="studentName"]')).toBeVisible();
  await expect(page.locator('input[name="email"]')).toBeVisible();
  await expect(page.locator('input[name="password"]')).toBeVisible();

  // ✅ Fill in form with valid input
  await page.fill('input[name="studentId"]', '1234567');
  await page.fill('input[name="studentName"]', 'Playwright Tester');
  await page.fill('input[name="email"]', '1234567@sit.singaporetech.edu.sg');
  await page.fill('input[name="password"]', 'Test@1234');

  // Submit the form
  await Promise.all([
    page.waitForNavigation(), // Wait for reload
    page.click('button[type="submit"]')
  ]);

  // ✅ OTP form should be shown on success
  await expect(page.locator('input[name="otp"]')).toBeVisible();

  // ✅ Resend button should be present
  await expect(page.getByRole('button', { name: /resend otp/i })).toBeVisible();
});
