document.getElementById("filter-form").addEventListener("submit", function(e) {
    e.preventDefault();
  
    const cityInput = document.getElementById("filter-city").value.toLowerCase();
    const cargoInput = document.getElementById("filter-cargo").value.toLowerCase();
    const weightInput = parseFloat(document.getElementById("filter-weight").value);
    const timeInput = parseFloat(document.getElementById("filter-time").value);
  
    const routes = document.querySelectorAll(".route-card");
  
    routes.forEach(route => {
      const city = route.dataset.city.toLowerCase();
      const cargo = route.dataset.cargo.toLowerCase();
      const weight = parseFloat(route.dataset.weight);
      const time = parseFloat(route.dataset.time);
  
      const matchCity = city.includes(cityInput) || cityInput === "";
      const matchCargo = cargo.includes(cargoInput) || cargoInput === "";
      const matchWeight = isNaN(weightInput) || weight <= weightInput;
      const matchTime = isNaN(timeInput) || time <= timeInput;
  
      if (matchCity && matchCargo && matchWeight && matchTime) {
        route.style.display = "block";
      } else {
        route.style.display = "none";
      }
    });
  });


  const routeCards = document.querySelectorAll('.route-card');

routeCards.forEach(card => {
  const btn = card.querySelector('.route-card__accept');

  btn.addEventListener('click', () => {
    const data = {
      city: card.dataset.city,
      cargo: card.dataset.cargo,
      weight: card.dataset.weight,
      time: card.dataset.time,
      title: card.querySelector('h4')?.textContent || '',
      summary: card.querySelector('p')?.textContent || '',
    };

    localStorage.setItem('selectedRoute', JSON.stringify(data));
    window.location.href = 'flight-details.html';
  });
});

  