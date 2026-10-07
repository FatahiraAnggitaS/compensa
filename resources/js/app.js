const toggle = document.querySelector('[data-nav-toggle]');
const closeButton = document.querySelector('[data-nav-close]');
const navigation = document.querySelector('[data-mobile-nav]');
const overlay = document.querySelector('[data-nav-overlay]');

const setNavigationState = (isOpen) => {
    if (!toggle || !navigation || !overlay) {
        return;
    }

    toggle.setAttribute('aria-expanded', String(isOpen));
    toggle.setAttribute('aria-label', isOpen ? 'Tutup navigasi' : 'Buka navigasi');
    navigation.classList.toggle('-translate-x-full', !isOpen);
    overlay.classList.toggle('hidden', !isOpen);
    document.body.classList.toggle('overflow-hidden', isOpen);

    if (isOpen) {
        closeButton?.focus();
    } else {
        toggle.focus();
    }
};

toggle?.addEventListener('click', () => {
    setNavigationState(toggle.getAttribute('aria-expanded') !== 'true');
});

closeButton?.addEventListener('click', () => setNavigationState(false));
overlay?.addEventListener('click', () => setNavigationState(false));

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && toggle?.getAttribute('aria-expanded') === 'true') {
        setNavigationState(false);
    }
});

document.querySelectorAll('[data-scaffold-form]').forEach((form) => {
    form.addEventListener('submit', (event) => event.preventDefault());
});
