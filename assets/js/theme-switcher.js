document.addEventListener('DOMContentLoaded', () => {
  const body = document.body;
  const toggle = document.getElementById('themeToggle');
  const menu = document.getElementById('themeMenu');
  const options = document.querySelectorAll('[data-theme-value]');
  const storageKey = 'cusit-portal-theme-v2';
  const validThemes = ['default', 'emerald', 'sunset'];

  if (!body || !toggle || !menu || !options.length) {
    return;
  }

  const applyTheme = (theme) => {
    const selectedTheme = validThemes.includes(theme) ? theme : 'default';
    body.classList.remove('theme-emerald', 'theme-sunset');
    if (selectedTheme !== 'default') {
      body.classList.add(`theme-${selectedTheme}`);
    }

    options.forEach((option) => {
      option.classList.toggle('is-active', option.getAttribute('data-theme-value') === selectedTheme);
    });

    window.localStorage.setItem(storageKey, selectedTheme);
  };

  const setMenuOpen = (open) => {
    menu.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  };

  const savedTheme = window.localStorage.getItem(storageKey) || 'default';
  applyTheme(savedTheme);

  toggle.addEventListener('click', (event) => {
    event.stopPropagation();
    setMenuOpen(!menu.classList.contains('is-open'));
  });

  options.forEach((option) => {
    option.addEventListener('click', () => {
      applyTheme(option.getAttribute('data-theme-value') || 'default');
      setMenuOpen(false);
    });
  });

  document.addEventListener('click', (event) => {
    if (!menu.contains(event.target) && !toggle.contains(event.target)) {
      setMenuOpen(false);
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      setMenuOpen(false);
    }
  });
});
