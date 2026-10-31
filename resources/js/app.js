document.querySelectorAll('select[id^="kind-"]').forEach((kindSelect) => {
    const textId = kindSelect.id.replace('kind-', 'text-');
    const textField = document.getElementById(textId);

    if (!textField) return;

    const syncLimit = () => {
        textField.maxLength = kindSelect.value === 'quote' ? 155 : 2000;
    };

    kindSelect.addEventListener('change', syncLimit);
    syncLimit();
});

const revealItems = document.querySelectorAll('[data-reveal], .wasl-hero-brand, .surface-card, .auth-form, .page-intro');
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (revealItems.length && !reduceMotion && 'IntersectionObserver' in window) {
    document.body.classList.add('reveal-ready');

    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;

            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.14, rootMargin: '0px 0px -32px 0px' });

    revealItems.forEach((item, index) => {
        if (item.classList.contains('surface-card') && !item.style.getPropertyValue('--reveal-delay')) {
            item.style.setProperty('--reveal-delay', `${(index % 4) * 65}ms`);
        }

        revealObserver.observe(item);
    });
} else {
    revealItems.forEach((item) => item.classList.add('is-visible'));
}
