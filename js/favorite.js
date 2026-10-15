document.addEventListener('DOMContentLoaded', () => {
    const config = window.favoriteConfig;

    if (!config) {
        return;
    }

    const albums = document.querySelectorAll('.album[data-album]');
    const counter = document.querySelector('.favorites__count');

    // Загружаем список избранного из PHP
    const favorites = new Set(config.favoriteAlbums);

    // Обновляем внешний вид сердечка
    function updateButton(album, isFavorite) {
        const button = album.querySelector('.btn__favorite');

        if (!button) {
            return;
        }

        button.classList.toggle('is-favorite', isFavorite);
        button.setAttribute('aria-pressed', String(isFavorite));
    }

    // Обновляем число избранных альбомов
    function updateCounter(count) {
        if (counter && config.loggedIn) {
            counter.textContent = `Favorites: ${count}`;
        }
    }

    // Показываем ранее сохранённые лайки
    albums.forEach((album) => {
        const key = album.dataset.album;

        updateButton(album, favorites.has(key));

        const button = album.querySelector('.btn__favorite');

        if (!button) {
            return;
        }

        button.type = 'button';

        button.addEventListener('click', async () => {
            // Гостю нужно войти в аккаунт
            if (!config.loggedIn) {
                window.location.href = 'login.php';
                return;
            }

            button.disabled = true;

            try {
                const response = await fetch('favorite-toggle.php', {
                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json'
                    },

                    body: JSON.stringify({
                        album_key: key,
                        csrf_token: config.csrfToken
                    })
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(
                        result.message || 'Could not save favorite.'
                    );
                }

                // Синхронизируем состояние с ответом сервера
                if (result.is_favorite) {
                    favorites.add(key);
                } else {
                    favorites.delete(key);
                }

                updateButton(album, result.is_favorite);
                updateCounter(result.count);

            } catch (error) {
                console.error(error);

                alert(
                    'Could not save your favorite album. Please try again.'
                );

            } finally {
                button.disabled = false;
            }
        });
    });

    updateCounter(favorites.size);
});