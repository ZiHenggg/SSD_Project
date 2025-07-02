import { test, expect } from '@playwright/test';

test('Login page loads correctly', async ({ page }) => {
  const response = await page.goto('http://localhost:8080/login.php');
  console.log(await page.title());
  console.log(await page.content());  // 🔍 see what’s actually rendered in CI

  const heading = page.getByRole('heading', { level: 2 });
  await expect(heading).toHaveText('Login');
});
