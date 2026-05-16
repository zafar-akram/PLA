const themeToggle = document.getElementById('themeToggle');
const html = document.documentElement;

const currentTheme = localStorage.getItem('theme') || 'light';
html.setAttribute('data-theme', currentTheme);

if (currentTheme === 'dark') {
    if (themeToggle) {
        themeToggle.classList.remove('bi-moon-fill');
        themeToggle.classList.add('bi-sun-fill');
    }
}

if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        const theme = html.getAttribute('data-theme');
        const newTheme = theme === 'light' ? 'dark' : 'light';

        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);

        if (newTheme === 'dark') {
            themeToggle.classList.remove('bi-moon-fill');
            themeToggle.classList.add('bi-sun-fill');
        } else {
            themeToggle.classList.remove('bi-sun-fill');
            themeToggle.classList.add('bi-moon-fill');
        }
    });
}