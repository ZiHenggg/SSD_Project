import { test, expect } from '@playwright/test';

test('Login page loads correctly', async ({ page }) => {
  await page.goto('http://localhost:8080/login.php');
  await expect(page.getByRole('heading', { level: 2 })).toHaveText('Login');
  await expect(page.locator('form')).toBeVisible();
  await expect(page.getByRole('button')).toHaveText('Login');
});
