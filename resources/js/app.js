// Меню на телефоне, фильтр и просмотр фото в галерее. Без библиотек.

const header = document.querySelector('.header');
const burger = document.querySelector('[data-burger]');

if (header && burger) {
    burger.addEventListener('click', () => {
        const open = header.classList.toggle('is-open');
        burger.setAttribute('aria-expanded', String(open));
    });
    header.querySelectorAll('.nav a').forEach((link) => {
        link.addEventListener('click', () => header.classList.remove('is-open'));
    });
}

const gallery = document.querySelector('[data-gallery]');

if (gallery) {
    const figures = Array.from(gallery.querySelectorAll('figure'));
    const buttons = document.querySelectorAll('[data-filter]');
    let visible = [];

    const apply = (filter) => {
        visible = [];
        figures.forEach((figure, index) => {
            const on = filter === 'all' || figure.dataset.category === filter || figure.dataset.place === filter;
            figure.hidden = !on;
            if (on) {
                visible.push(index);
            }
        });
        buttons.forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.filter === filter)));
    };

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const filter = button.dataset.filter;
            apply(filter);
            history.replaceState(null, '', filter === 'all' ? location.pathname : '#' + encodeURIComponent(filter));
        });
    });

    const fromHash = decodeURIComponent(location.hash.slice(1));
    const known = Array.from(buttons).some((button) => button.dataset.filter === fromHash);
    apply(known ? fromHash : 'all');

    const box = document.querySelector('[data-lightbox]');
    const image = box.querySelector('img');
    const video = box.querySelector('video');
    const caption = box.querySelector('[data-caption]');
    let position = 0;

    const show = (next) => {
        position = (next + visible.length) % visible.length;
        const figure = figures[visible[position]];
        const isVideo = figure.dataset.kind === 'video';
        video.pause();
        image.hidden = isVideo;
        video.hidden = !isVideo;
        if (isVideo) {
            image.removeAttribute('src');
            video.src = figure.dataset.full;
            video.play().catch(() => {});
        } else {
            video.removeAttribute('src');
            image.src = figure.dataset.full;
            image.alt = figure.dataset.caption;
        }
        caption.textContent = figure.dataset.caption;
        const small = document.createElement('small');
        small.textContent = `${figure.dataset.meta} · ${position + 1} из ${visible.length}`;
        caption.appendChild(small);
    };

    const open = (index) => {
        box.hidden = false;
        document.body.style.overflow = 'hidden';
        show(visible.indexOf(index));
        box.querySelector('[data-close]').focus();
    };

    const close = () => {
        video.pause();
        box.hidden = true;
        document.body.style.overflow = '';
    };

    figures.forEach((figure, index) => {
        figure.querySelector('button').addEventListener('click', () => open(index));
    });
    box.querySelector('[data-close]').addEventListener('click', close);
    box.querySelector('[data-prev]').addEventListener('click', () => show(position - 1));
    box.querySelector('[data-next]').addEventListener('click', () => show(position + 1));
    box.addEventListener('click', (event) => {
        if (event.target === box) {
            close();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (box.hidden) {
            return;
        }
        if (event.key === 'Escape') {
            close();
        } else if (event.key === 'ArrowLeft') {
            show(position - 1);
        } else if (event.key === 'ArrowRight') {
            show(position + 1);
        }
    });
}

// Калькулятор «Предварительный расчёт»: площадь × сумма отмеченных ставок за м².
// Считает в копейках целыми числами, как сервер (App\Support\Estimate).
// Здесь только живой показ; в заявку итог пересчитывает сервер по ставкам из базы.
const calculator = document.querySelector('[data-calculator]');

if (calculator) {
    const area = calculator.querySelector('[data-calc-area]');
    const rates = Array.from(calculator.querySelectorAll('[data-calc-rate]'));
    const total = calculator.querySelector('[data-calc-total]');

    // Тот же вид, что у RubleFormatter: «120 000 ₽» с неразрывными пробелами.
    const rubles = (kopecks) => {
        const whole = Math.floor(kopecks / 100).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        const rest = kopecks % 100;
        return whole + (rest > 0 ? ',' + String(rest).padStart(2, '0') : '') + ' ₽';
    };

    const update = () => {
        const m2 = Number.parseInt(area.value, 10);
        const perM2 = rates.filter((rate) => rate.checked).reduce((sum, rate) => sum + Number.parseInt(rate.dataset.calcRate, 10), 0);
        total.textContent = Number.isInteger(m2) && m2 >= 1 && m2 <= 100000 && perM2 > 0 ? 'от ' + rubles(m2 * perM2) : '—';
    };

    area.addEventListener('input', update);
    rates.forEach((rate) => rate.addEventListener('change', update));
    update();
}
