const buttons = document.querySelectorAll('.filter-btn');

buttons.forEach(button => {
    button.addEventListener('click', function () {
        selectedCategory = button.dataset.category;

        updateMembers();
    });
});