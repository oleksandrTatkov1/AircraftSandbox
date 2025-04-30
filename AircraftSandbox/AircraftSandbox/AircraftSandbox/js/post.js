document.addEventListener('DOMContentLoaded', () => {
    const postData = {
      title: 'MLM SUPREMACY',
      image: 'img/post-example.jpg',
      text: 'We found a bug in the game we need company to fix it... SkyTechDev fix it quickly!',
      likes: 12,
      dislikes: 2,
      comments: [
        {
          avatar: 'img/avatar1.png',
          user: 'DogPower228',
          text: 'I think this true. SkyTechDev fix it.'
        },
        {
          avatar: 'img/avatar2.png',
          user: 'StarikDomPonik',
          text: 'I don’t think so. SkyTechDev can’t fix it so fast as u want.'
        },
        {
          avatar: 'img/avatar3.png',
          user: 'GloryToLvL',
          text: '@StarikDomPonik it agrees with you'
        },
        {
          avatar: 'img/avatar4.png',
          user: 'LybitelPonickovSMaslom',
          text: 'I need more donuts. SKYTECHDEV I NEED DONUTS IN YOUR GAME'
        }
      ]
    };
  
    document.getElementById('post-title').textContent = postData.title;
    document.getElementById('post-image').src = postData.image;
    document.getElementById('post-text').textContent = postData.text;
    document.getElementById('like-count').textContent = postData.likes;
    document.getElementById('dislike-count').textContent = postData.dislikes;
  
    const commentsContainer = document.getElementById('comments');
    postData.comments.forEach(comment => {
      const commentEl = document.createElement('div');
      commentEl.className = 'comment';
      commentEl.innerHTML = `
        <img class="comment__avatar" src="${comment.avatar}" alt="avatar">
        <div>
          <p class="comment__user">${comment.user}</p>
          <p class="comment__text">${comment.text}</p>
        </div>
      `;
      commentsContainer.appendChild(commentEl);
    });
  });
  