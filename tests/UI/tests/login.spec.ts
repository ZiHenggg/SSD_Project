import { test, expect } from '@playwright/test';

test('Login page loads', async ({ page }) => {
  await page.goto('http://web/login.php');
  await expect(page).toHaveTitle(/login/i); // Adjust to match your login page title
});