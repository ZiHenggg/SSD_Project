import { test, expect } from '@playwright/test';

test('Login page loads', async ({ page }) => {
  await page.goto('http://localhost:8080/login.php');
  await expect(page).toHaveTitle(/login/i); // Adjust to match your login page title
});