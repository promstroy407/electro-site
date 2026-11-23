// Скриншоты страниц из пробного запуска в CI: по ним проверяем внешний
// вид до слияния. Сайт отвечает на https://localhost (сертификат Caddy
// внутренний, поэтому ошибки сертификата игнорируем).
import { chromium } from 'playwright';
import { mkdirSync } from 'node:fs';

const base = 'https://localhost';
const out = 'screenshots';
const pages = [
    ['glavnaya', '/'],
    ['subpodryad', '/subpodryad'],
    ['dlya-doma', '/dlya-doma'],
    ['foto', '/foto'],
    ['partnery', '/partnery'],
    ['stati', '/stati'],
    ['statya', '/stati/kak-vybrat-kabel'],
    ['statya-ceny', '/stati/skolko-stoit-elektromontazh-v-kvartire'],
    ['uslugi', '/uslugi'],
    ['usluga', '/uslugi/elektromontazh'],
    ['ceny', '/ceny'],
    ['kalkulyator', '/kalkulyator'],
];

mkdirSync(`${out}/jpg`, { recursive: true });
const browser = await chromium.launch();

for (const [width, height, suffix] of [[1440, 900, 'pk'], [390, 844, 'telefon']]) {
    const context = await browser.newContext({ viewport: { width, height }, ignoreHTTPSErrors: true, deviceScaleFactor: 1 });
    const page = await context.newPage();
    for (const [name, path] of pages) {
        await page.goto(base + path, { waitUntil: 'networkidle' });
        // Картинки с loading=lazy грузим принудительно, иначе на полном скриншоте пустые места.
        await page.evaluate(() => document.querySelectorAll('img[loading="lazy"]').forEach((img) => { img.loading = 'eager'; }));
        await page.waitForLoadState('networkidle');
        const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
        if (overflow > 0) {
            console.log(`ГОРИЗОНТАЛЬНАЯ ПРОКРУТКА: ${name} ${suffix} на ${overflow}px`);
        }
        await page.screenshot({ path: `${out}/${name}-${suffix}.png`, fullPage: true });
        // Сжатая копия — для проверки по журналу CI (артефакты не всем доступны).
        await page.screenshot({ path: `${out}/jpg/${name}-${suffix}.jpg`, fullPage: true, type: 'jpeg', quality: 45 });
    }
    await context.close();
}

await browser.close();
