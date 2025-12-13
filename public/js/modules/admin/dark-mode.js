// Simple Dark Mode Toggle
class DarkMode {
    constructor() {
        this.themeKey = 'theme';
        this.currentTheme = localStorage.getItem(this.themeKey) || 'light';
        this.init();
    }

    init() {
        this.setTheme(this.currentTheme);
        this.createToggle();
        this.bindEvents();
    }

    setTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        this.currentTheme = theme;
        localStorage.setItem(this.themeKey, theme);
        this.updateToggle(theme);
    }

    createToggle() {
        if (document.querySelector('.theme-toggle')) return;

        const toggle = document.createElement('button');
        toggle.className = 'theme-toggle';
        toggle.innerHTML = this.currentTheme === 'dark' ? '☀️' : '🌙';
        toggle.title = this.currentTheme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';

        document.body.appendChild(toggle);
    }

    updateToggle(theme) {
        const toggle = document.querySelector('.theme-toggle');
        if (toggle) {
            toggle.innerHTML = theme === 'dark' ? '☀️' : '🌙';
            toggle.title = theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
        }
    }

    bindEvents() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('theme-toggle')) {
                const newTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
                this.setTheme(newTheme);
            }
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new DarkMode();
});