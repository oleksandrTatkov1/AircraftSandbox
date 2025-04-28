const swipeR = new Swiper('.newsSection__newsSwiper',{
    navigation:{
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev'
    },

    simulateTouch: false,
    slidesPerView: 3,
    spaceBettwen: 40,
    breakpoints:{
        1280:{
            slidesPerView: 3,
        },
        540:{
            slidesPerView: 2,
        },
        320:{
            slidesPerView: 1,
        },
    }
});

const menuBtn = document.querySelector('.menu-btn');
const menu = document.querySelector('.header__menu');

menuBtn.addEventListener('click', () => {
  menu.classList.toggle('menu--active');
  menuBtn.classList.toggle('active');
});


document.addEventListener('DOMContentLoaded', () => {
    const authBtn = document.getElementById('auth-button');
    const userProfile = document.getElementById('user-profile');
  
    const updateAuthUI = () => {
      const isLoggedIn = localStorage.getItem('loggedIn') === 'true';
  
      if (isLoggedIn) {
        authBtn.style.display = 'none';
        userProfile.style.display = 'block';
      } else {
        authBtn.style.display = 'block';
        userProfile.style.display = 'none';
      }
    };
  
    authBtn.addEventListener('click', () => {
      localStorage.setItem('loggedIn', 'true');
      updateAuthUI();
    });
  
    userProfile.addEventListener('click', () => {
      localStorage.setItem('loggedIn', 'false');
      updateAuthUI();
    });
  
    updateAuthUI();
  });
  

