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
  // 🟢 Go to register page
  await page.goto('http://localhost:8080/register.php');

  // 🟢 Ensure form is visible
  await expect(page.locator('form')).toBeVisible();
  await expect(page.getByRole('heading', { level: 2 })).toHaveText('Register');

  // 📝 Fill in form (dummy data, adjust if needed)
  await page.fill('input[name="studentId"]', '1234567');
  await page.fill('input[name="studentName"]', 'UI Test Student');
  await page.fill('input[name="email"]', '1234567@sit.singaporetech.edu.sg');
  await page.fill('input[name="password"]', 'testing123');

  // 🚀 Submit form
  await page.getByRole('button', { name: 'Register' }).click();

  // 🟢 OTP form should appear (if step = 'otp' is triggered)
  await expect(page.locator('input[name="otp"]')).toBeVisible();
  await expect(page.getByRole('button', { name: /resend otp/i })).toBeVisible();
});
