const toggle = document.querySelector('[data-nav-toggle]');
const closeButton = document.querySelector('[data-nav-close]');
const navigation = document.querySelector('[data-mobile-nav]');
const overlay = document.querySelector('[data-nav-overlay]');

const focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

const setNavigationState = (isOpen, restoreFocus = true) => {
    if (!toggle || !navigation || !overlay) {
        return;
    }

    toggle.setAttribute('aria-expanded', String(isOpen));
    toggle.setAttribute('aria-label', isOpen ? 'Tutup navigasi' : 'Buka navigasi');
    navigation.classList.toggle('-translate-x-full', !isOpen);
    overlay.classList.toggle('hidden', !isOpen);
    document.body.classList.toggle('overflow-hidden', isOpen);
    navigation.setAttribute('aria-hidden', String(!isOpen));
    if (isOpen) {
        navigation.removeAttribute('inert');
    } else {
        navigation.setAttribute('inert', '');
    }

    if (isOpen) {
        closeButton?.focus();
    } else if (restoreFocus) {
        toggle.focus();
    }
};

toggle?.addEventListener('click', () => {
    setNavigationState(toggle.getAttribute('aria-expanded') !== 'true');
});

closeButton?.addEventListener('click', () => setNavigationState(false));
overlay?.addEventListener('click', () => setNavigationState(false));

document.addEventListener('keydown', (event) => {
    const isOpen = toggle?.getAttribute('aria-expanded') === 'true';

    if (event.key === 'Escape' && isOpen) {
        setNavigationState(false);
    }

    if (event.key !== 'Tab' || !isOpen || !navigation) {
        return;
    }

    const focusableElements = [...navigation.querySelectorAll(focusableSelector)];
    const firstElement = focusableElements[0];
    const lastElement = focusableElements.at(-1);

    if (!firstElement || !lastElement) {
        return;
    }

    if (event.shiftKey && document.activeElement === firstElement) {
        event.preventDefault();
        lastElement.focus();
    } else if (!event.shiftKey && document.activeElement === lastElement) {
        event.preventDefault();
        firstElement.focus();
    }
});

document.querySelectorAll('[data-submit-once]').forEach((form) => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('button[type="submit"]');
        if (!button) {
            return;
        }

        button.disabled = true;
        button.textContent = 'Memproses estimasi...';
    });
});

document.querySelectorAll('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});
