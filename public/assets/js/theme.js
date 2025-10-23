/**
 * Theme Management System
 * Handles light/dark mode switching with localStorage persistence
 */

class ThemeManager {
    constructor() {
        this.themeKey = 'preferred-theme';
        this.themeAttribute = 'data-theme';
        this.init();
    }

    /**
     * Initialize theme system
     */
    init() {
        // Check for saved theme preference or default to light mode
        const savedTheme = localStorage.getItem(this.themeKey);
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        // Apply theme based on preference or system default
        const theme = savedTheme || (systemPrefersDark ? 'dark' : 'light');
        this.applyTheme(theme);
        
        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem(this.themeKey)) {
                this.applyTheme(e.matches ? 'dark' : 'light');
            }
        });
    }

    /**
     * Apply theme to document
     * @param {string} theme - 'light' or 'dark'
     */
    applyTheme(theme) {
        document.documentElement.setAttribute(this.themeAttribute, theme);
        localStorage.setItem(this.themeKey, theme);
        this.updateToggleButtons(theme);
    }

    /**
     * Toggle between light and dark themes
     */
    toggleTheme() {
        const currentTheme = document.documentElement.getAttribute(this.themeAttribute) || 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme(newTheme);
    }

    /**
     * Update theme toggle buttons appearance
     * @param {string} theme - Current theme
     */
    updateToggleButtons(theme) {
        const buttons = document.querySelectorAll('.theme-toggle');
        buttons.forEach(button => {
            const icon = button.querySelector('i');
            if (icon) {
                if (theme === 'dark') {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                } else {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                }
            }
            
            // Update button text if it exists
            const text = button.querySelector('.theme-text');
            if (text) {
                text.textContent = theme === 'dark' ? 'Light Mode' : 'Dark Mode';
            }
        });
    }

    /**
     * Get current theme
     * @returns {string} Current theme
     */
    getCurrentTheme() {
        return document.documentElement.getAttribute(this.themeAttribute) || 'light';
    }
}

// Initialize theme manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
    
    // Add event listeners to theme toggle buttons
    document.querySelectorAll('.theme-toggle').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            window.themeManager.toggleTheme();
        });
    });
});