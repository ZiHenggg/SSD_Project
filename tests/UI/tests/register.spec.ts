import { test, expect } from '@playwright/test';

/**
 * ✅ UI Test: Registration Flow (Happy Path Only)
 *
 * Verifies the following in-browser flow:
 *
 * 1. Loads register page successfully
 * 2. Fills and submits form
 * 3. Verifies OTP input is shown after submission
 *
 * Does NOT:
 * - Verify backend OTP logic (covered in PHPUnit)
 * - Submit OTP or complete registration
 */

test('Register page displays and submits correctly', async ({ page }) => {
  await page.goto('http://localhost:8080/register.php');

  // Fill in registration form
  await page.fill('input[name="studentId"]', '1234567');
  await page.fill('input[name="studentName"]', 'Test User');
  await page.fill('input[name="email"]', '1234567@sit.singaporetech.edu.sg');
  await page.fill('input[name="password"]', 'Password123!');
  
  // Submit form and wait for redirect to reload page
  await Promise.all([
    page.waitForNavigation(), // wait for redirect after form POST
    page.click('button[type="submit"]'),
  ]);

  // ✅ OTP form should now be visible
  await expect(page.locator('input[name="otp"]')).toBeVisible();
  await expect(page.getByRole('button', { name: /resend otp/i })).toBeVisible();
});

