  document.addEventListener('DOMContentLoaded', () => {
    const apkGrid = document.querySelector('.apk-grid');
    const paginationContainer = document.querySelector('.pagination');
    const filterItems = document.querySelectorAll('.apk-filter__list li');

    let currentPage = 1;
    const cardsPerPage = 24;
    let currentFilter = 'all';
    let allCards = [];

    function getFilteredCards() {
      if (currentFilter === 'all') return allCards;
      return allCards.filter(card => card.classList.contains(currentFilter));
    }

    function bindCardClicks() {
      const cards = apkGrid.querySelectorAll('.apk-card');
      cards.forEach(card => {
        const id = card.getAttribute('data-id');
        if (!id) return;
        card.onclick = () => {
          console.log('👉 Клік на картку ID:', id);
          window.location.href = `Apkinfo.html?Id=${id}`;
        };
      });
    }

    function paginate(cards) {
      const totalPages = Math.ceil(cards.length / cardsPerPage);
      const start = (currentPage - 1) * cardsPerPage;
      const end = start + cardsPerPage;
      const shownCards = cards.slice(start, end);

      apkGrid.innerHTML = '';
      paginationContainer.innerHTML = '';

      shownCards.forEach(card => {
        apkGrid.insertAdjacentHTML('beforeend', card.outerHTML);
      });

      bindCardClicks();

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

    fetch('PHP/scripts/loadApk.php')
      .then(res => res.text())
      .then(html => {
        apkGrid.innerHTML = html;
        allCards = Array.from(apkGrid.querySelectorAll('.apk-card'));
        paginate(getFilteredCards());
      })
      .catch(err => console.error('❌ Завантаження APK:', err));

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