const searchMembers = document.getElementById('Search');

searchMembers.addEventListener('input', function () {
    searchValue = searchMembers.value;

    updateMembers();
});