import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Theme (Tutora-like): 'light' | 'dark', default light, persists in localStorage.
window.ElpayTheme = {
    init() {
        const saved = localStorage.getItem('elpay-theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (saved === 'dark' || (!saved && prefersDark && localStorage.getItem('elpay-theme-set') === '1')) {
            document.documentElement.classList.add('dark');
        }
    },
    toggle() {
        const dark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('elpay-theme', dark ? 'dark' : 'light');
        localStorage.setItem('elpay-theme-set', '1');
    },
    get dark() {
        return document.documentElement.classList.contains('dark');
    },
};
window.ElpayTheme.init();
