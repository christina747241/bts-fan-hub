
const THEME_KEY = 'bts-fan-hub-theme';

function initTheme() {
    const themeButton = document.querySelector('.themeButton');
    const icon = themeButton?.querySelector('.themeButton-icon');

    // Получаем сохранённую тему.
    const savedTheme = localStorage.getItem(THEME_KEY);

    // Если выбор ещё не сделан, используем тёмную тему.
    function applyTheme(theme) {
        const isLight = theme === 'light';

        document.body.classList.toggle('light-theme', isLight);

        if (icon) {
            icon.textContent = isLight ? '☾' : '☀';
        }
    }

    applyTheme(savedTheme === 'light' ? 'light' : 'dark');

    // Если на странице есть переключатель, подключаем его.
    if (themeButton) {
        themeButton.addEventListener('click', function () {
            const isLight =
                !document.body.classList.contains('light-theme');

            const newTheme = isLight ? 'light' : 'dark';

            applyTheme(newTheme);

            localStorage.setItem(THEME_KEY, newTheme);
        });
    }
}

// Ждём готовности HTML, чтобы элементы страницы уже существовали.
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTheme);
} else {
    initTheme();
}