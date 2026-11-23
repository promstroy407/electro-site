@php /** @var int $latestId */ @endphp
<div hidden data-lead-alert
     data-latest="{{ $latestId }}"
     data-url="{{ route('admin.leads.pulse') }}"
     data-leads-url="{{ \App\Filament\Resources\Leads\LeadResource::getUrl() }}"></div>
<script>
    // Новая заявка: звуковой сигнал, сообщение в админке и в Windows, точка в заголовке вкладки.
    // Браузер разрешает звук только после первого щелчка или нажатия клавиши на странице.
    (() => {
        const el = document.querySelector('[data-lead-alert]');
        if (!el || window.leadAlertStarted) return;
        window.leadAlertStarted = true;

        const key = 'leadAlertLastId';
        const read = () => { try { return Number(localStorage.getItem(key)); } catch (e) { return 0; } };
        const save = (id) => { try { localStorage.setItem(key, String(id)); } catch (e) {} };
        const serverLatest = Number(el.dataset.latest);
        let last = read() > 0 ? read() : serverLatest;

        let audio = null;
        const unlock = () => {
            try {
                audio = audio || new (window.AudioContext || window.webkitAudioContext)();
                audio.resume();
            } catch (e) {}
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        };
        document.addEventListener('pointerdown', unlock);
        document.addEventListener('keydown', unlock);

        const beep = () => {
            if (!audio) return;
            const t = audio.currentTime;
            [[0, 880], [0.25, 880], [0.5, 1320]].forEach(([delay, freq]) => {
                const osc = audio.createOscillator();
                const gain = audio.createGain();
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0.0001, t + delay);
                gain.gain.exponentialRampToValueAtTime(0.5, t + delay + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, t + delay + 0.22);
                osc.connect(gain).connect(audio.destination);
                osc.start(t + delay);
                osc.stop(t + delay + 0.25);
            });
        };

        const title = document.title;
        const alertAbout = (count) => {
            const text = count === 1 ? 'Пришла новая заявка' : 'Новых заявок: ' + count;
            beep();
            document.title = '● ' + text + ' — ' + title;
            if (window.FilamentNotification) {
                new window.FilamentNotification().title(text).body('Откройте раздел «Заявки».').success().persistent().send();
            }
            if ('Notification' in window && Notification.permission === 'granted' && document.hidden) {
                const n = new Notification(text, { body: 'Откройте раздел «Заявки» в админке.' });
                n.onclick = () => { window.focus(); location.href = el.dataset.leadsUrl; };
            }
        };

        const check = async () => {
            try {
                const response = await fetch(el.dataset.url + '?after=' + last, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                if (!response.ok) return;
                const data = await response.json();
                if (data.new > 0) alertAbout(data.new);
                last = Math.max(last, data.latest);
                save(last);
            } catch (e) {}
        };

        document.addEventListener('visibilitychange', () => { if (!document.hidden) document.title = title; });
        check();
        setInterval(check, 15000);
    })();
</script>
