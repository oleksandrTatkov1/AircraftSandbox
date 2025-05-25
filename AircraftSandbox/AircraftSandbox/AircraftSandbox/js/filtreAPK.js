document.addEventListener('DOMContentLoaded', () => {
  const filterItems = document.querySelectorAll('.apk-filter__list li');
  const apkGrid = document.querySelector('.apk-grid');
  const paginationContainer = document.querySelector('.pagination');

  let currentPage = 1;
  const cardsPerPage = 24;
  let currentFilter = 'all';
  let allCards = [];

  function getFilteredCards() {
    if (currentFilter === 'all') return allCards;
    return allCards.filter(card => card.classList.contains(currentFilter));
  }

  function paginate(cards) {
    const totalPages = Math.ceil(cards.length / cardsPerPage);
    const start = (currentPage - 1) * cardsPerPage;
    const end = start + cardsPerPage;
    const shownCards = cards.slice(start, end);

    apkGrid.innerHTML = '';
    paginationContainer.innerHTML = '';

    shownCards.forEach(card => {
      const clone = card.cloneNode(true);
      clone.classList.add('fade-in');
      apkGrid.appendChild(clone);
    });

    const prevBtn = document.createElement('button');
    prevBtn.textContent = '◀';
    prevBtn.className = 'pagination-btn';
    prevBtn.disabled = currentPage === 1;
    prevBtn.addEventListener('click', () => {
      currentPage--;
      paginate(getFilteredCards());
    });
    paginationContainer.appendChild(prevBtn);

    for (let i = 1; i <= totalPages; i++) {
      const btn = document.createElement('button');
      btn.textContent = i;
      btn.className = 'pagination-btn';
      if (i === currentPage) btn.classList.add('active');
      btn.addEventListener('click', () => {
        currentPage = i;
        paginate(getFilteredCards());
      });
      paginationContainer.appendChild(btn);
    }

    const nextBtn = document.createElement('button');
    nextBtn.textContent = '▶';
    nextBtn.className = 'pagination-btn';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.addEventListener('click', () => {
      currentPage++;
      paginate(getFilteredCards());
    });
    paginationContainer.appendChild(nextBtn);
  }

  // Завантаження APK-карток через PHP
  fetch('PHP/scripts/loadApk.php')
    .then(response => response.text())
    .then(html => {
      apkGrid.innerHTML = html;
      allCards = Array.from(apkGrid.querySelectorAll('.apk-card'));
      paginate(getFilteredCards());
    })
    .catch(error => {
      console.error('Помилка при завантаженні APK:', error);
    });

  // Обробка кліку по фільтрах
  filterItems.forEach(item => {
    item.addEventListener('click', () => {
      document.querySelector('.apk-filter__list li.active')?.classList.remove('active');
      item.classList.add('active');

      currentFilter = item.getAttribute('data-filter');
      currentPage = 1;
      paginate(getFilteredCards());
    });
  });
});