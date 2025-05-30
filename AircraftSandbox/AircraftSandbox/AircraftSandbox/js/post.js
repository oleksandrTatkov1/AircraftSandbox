document.addEventListener("DOMContentLoaded", () => {
    const urlParams = new URLSearchParams(window.location.search);
    const postId = urlParams.get("id");

    if (!postId) {
        console.error("❌ ID поста не знайдено в URL");
        return;
    }

    fetch(`/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/PHP/scripts/loadPostById.php?id=${postId}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                console.error("❌ Помилка завантаження поста:", data.error);
                return;
            }

            // Підставляємо вміст
            document.getElementById("post-title").textContent = data.Header;
            document.getElementById("post-image").src = data.ImagePath;
            document.getElementById("post-text").textContent = data.Content;
            document.getElementById("like-count").textContent = data.LikesCount;
            document.getElementById("dislike-count").textContent = data.DislikesCount;

            // Лайки/дизлайки
            const likeBtn = document.querySelector(".post-thread__like");
            const dislikeBtn = document.querySelector(".post-thread__dislike");

            likeBtn.addEventListener("click", () => {
                updateReaction(postId, "like");
            });

            dislikeBtn.addEventListener("click", () => {
                updateReaction(postId, "dislike");
            });
        })
        .catch(err => {
            console.error("❌ Fetch помилка:", err);
        });

    function updateReaction(postId, action) {
        fetch('/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/PHP/scripts/updateLikes.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `postId=${postId}&action=${action}`
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                console.error("❌ Сервер не прийняв лайк/дизлайк:", data.message);
                return;
            }

            document.getElementById('like-count').textContent = data.likesCount;
            document.getElementById('dislike-count').textContent = data.dislikesCount;
        })
        .catch(err => {
            console.error("❌ Помилка реакції:", err);
        });
    }
});