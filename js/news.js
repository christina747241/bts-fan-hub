
document.addEventListener('DOMContentLoaded', () => {
    const newsContainer = document.querySelector('.news__results');
    const searchInput = document.getElementById('search');
    const sortSelect = document.getElementById('sort');
    const newsForm = document.getElementById('newsForm');

    // Проверяем наличие элементов поиска и новостей
    if (!newsContainer || !searchInput || !sortSelect) {
        console.error('News search: required elements not found.');
        return;
    }

    // Получаем существующие карточки, созданные PHP
    const allNewsCards = Array.from(
        newsContainer.querySelectorAll('.news__card')
    );

    // Если новостей пока нет, завершать работу поиска необязательно
    if (allNewsCards.length === 0) {
        return;
    }

    // Сообщение, когда ничего не найдено
    let emptyMessage = document.querySelector('.news__empty');

    if (!emptyMessage) {
        emptyMessage = document.createElement('p');
        emptyMessage.className = 'news__empty';
        emptyMessage.textContent = 'No news matches your search.';
        newsContainer.after(emptyMessage);
    }

    emptyMessage.style.display = 'none';

    // Не перезагружаем страницу при нажатии Enter
    if (newsForm) {
        newsForm.addEventListener('submit', (event) => {
            event.preventDefault();
        });
    }

    function updateNews() {
        const searchValue = searchInput.value
            .toLowerCase()
            .trim();

        // Ищем по заголовку и описанию
        const filteredNews = allNewsCards.filter((card) => {
            const title = card.querySelector(
                '.news__data h3'
            )?.textContent ?? '';

            const description = card.querySelector(
                '.news__data p'
            )?.textContent ?? '';

            return (
                title.toLowerCase().includes(searchValue) ||
                description.toLowerCase().includes(searchValue)
            );
        });

        // Сортируем по дате публикации, а не по дате комментария
        filteredNews.sort((newsA, newsB) => {
            const dateA = new Date(
                newsA.querySelector('.news__data time')?.dateTime ?? ''
            ).getTime();

            const dateB = new Date(
                newsB.querySelector('.news__data time')?.dateTime ?? ''
            ).getTime();

            const validDateA = Number.isNaN(dateA) ? 0 : dateA;
            const validDateB = Number.isNaN(dateB) ? 0 : dateB;

            if (sortSelect.value === 'oldest') {
                return validDateA - validDateB;
            }

            return validDateB - validDateA;
        });

        const visibleNews = new Set(filteredNews);

        // Скрываем карточки стилем, чтобы CSS не мешал поиску
        allNewsCards.forEach((card) => {
            card.style.display = visibleNews.has(card) ? '' : 'none';
        });

        // Расставляем найденные карточки в нужном порядке
        filteredNews.forEach((card) => {
            newsContainer.appendChild(card);
        });

        // Сообщаем, если совпадений нет
        emptyMessage.style.display =
            filteredNews.length === 0 ? 'block' : 'none';
    }

    // Поиск по мере ввода текста
    searchInput.addEventListener('input', updateNews);

    // Сортировка при смене значения
    sortSelect.addEventListener('change', updateNews);

    // Начальное отображение
    updateNews();

    console.log('News search initialized:', allNewsCards.length);
});