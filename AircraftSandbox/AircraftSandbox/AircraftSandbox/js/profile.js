// document.querySelectorAll('.tab-btn').forEach(button => {
//     button.addEventListener('click', () => {
//       document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
//       button.classList.add('active');
  
//       document.querySelectorAll('.profile__content').forEach(section => {
//         section.classList.add('hidden');
//       });
  
//       const targetId = button.getAttribute('data-tab');
//       document.getElementById(targetId).classList.remove('hidden');
//     });
//   });
const tabs = document.querySelectorAll('.profile-card__tab');
const sections = document.querySelectorAll('.profile-card__content');
const indicator = document.getElementById('tab-indicator');

tabs.forEach((tab, index) => {
  tab.addEventListener('click', () => {
    // Активна кнопка
    tabs.forEach(btn => btn.classList.remove('active'));
    tab.classList.add('active');

    // Змінюємо позицію індикатора
    indicator.style.transform = `translateX(${index * 100}%)`;

    // Приховуємо всі секції
    sections.forEach(sec => {
      sec.classList.add('hidden');
    });

    const targetId = tab.getAttribute('data-tab');
    const target = document.getElementById(targetId);

    // Показуємо нову секцію
    setTimeout(() => {
      target.classList.remove('hidden');

      // Якщо це вкладка "Відповіді", завантажуємо відповіді
      if (targetId === 'replies') {
        const answersContainer = target.querySelector('.profile-card__answer');
        if (answersContainer) {
          answersContainer.innerHTML = '<p>Завантаження відповідей...</p>';

          fetch('/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/PHP/scripts/loadUserComments.php')
            .then(response => {
              if (!response.ok) {
                throw new Error('Не вдалося завантажити відповіді');
              }
              return response.text();
            })
            .then(html => {
              answersContainer.innerHTML = html;
            })
            .catch(error => {
              console.error('❌ Помилка при завантаженні відповідей:', error);
              answersContainer.innerHTML = '<p>Не вдалося завантажити відповіді.</p>';
            });
        }
      }
    }, 50);
  });
});
