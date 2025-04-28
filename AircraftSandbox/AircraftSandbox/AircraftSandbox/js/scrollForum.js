// Додаємо класи чергування: .from-right, .from-left
document.addEventListener('DOMContentLoaded', () => {
    const scrollSections = document.querySelectorAll('.scroll-section');
    scrollSections.forEach((el, index) => {
        el.classList.add(index % 2 === 0 ? 'from-right' : 'from-left');
    });
});

// Перевіряє, чи елемент у межах вікна перегляду
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.bottom >= 0
    );
}

// Показує або приховує елементи при скролі
function toggleVisibility() {
    const elements = document.querySelectorAll('.scroll-section');
    elements.forEach(el => {
        if (isInViewport(el)) {
            el.classList.add('show');
        } else {
            el.classList.remove('show');
        }
    });
}

// Обробники подій
window.addEventListener('scroll', toggleVisibility);
window.addEventListener('load', toggleVisibility);

// Debug
console.log(document.querySelectorAll('.scroll-section'));


document.addEventListener('click', (e) => {
    document.querySelectorAll('.post__menu').forEach(menu => {
        const dropdown = menu.querySelector('.post__dropdown');
        if (menu.contains(e.target)) {
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        } else {
            dropdown.style.display = 'none';
        }
    });
});