const themeToggles = document.querySelectorAll('.theme-toggle, #themeToggle');
const html = document.documentElement;

const currentTheme = localStorage.getItem('theme') || 'light';
html.setAttribute('data-theme', currentTheme);

function setThemeIcon(toggle, theme) {
    const icon = toggle.matches('i') ? toggle : toggle.querySelector('i');
    if (!icon) return;

    if (theme === 'dark') {
        icon.classList.remove('bi-moon-fill');
        icon.classList.add('bi-sun-fill');
    } else {
        icon.classList.remove('bi-sun-fill');
        icon.classList.add('bi-moon-fill');
    }
}

themeToggles.forEach((themeToggle) => {
    setThemeIcon(themeToggle, currentTheme);

    themeToggle.addEventListener('click', () => {
        const theme = html.getAttribute('data-theme');
        const newTheme = theme === 'light' ? 'dark' : 'light';

        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);

        themeToggles.forEach((toggle) => setThemeIcon(toggle, newTheme));
    });
});
