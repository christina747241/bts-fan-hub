const membersContainer = document.querySelector('.members__cards');

let allMembers = [];
let searchValue = '';
let selectedCategory = 'all';

function renderCards(members) {
    membersContainer.innerHTML = '';

    members.forEach(member => {
        const card = document.createElement('figure');

        card.classList.add('member__card');

        card.innerHTML = `
            <div class="member">
                <figcaption>${member.nickname}</figcaption>
                <p>${member.name} is the ${member.role} of BTS.</p>
                <img src="${member.img}" alt="${member.name}">
            </div>
        `;

        membersContainer.append(card);
    });
}

function updateMembers() {
    const filteredMembers = allMembers.filter(member => {

        const matchesCategory =
            selectedCategory === 'all' ||
            member.category.includes(selectedCategory);

        const search = searchValue.toLowerCase().trim();

        const matchesSearch =
            member.nickname.toLowerCase().includes(search) ||
            member.name.toLowerCase().includes(search);

        return matchesCategory && matchesSearch;
    });

    renderCards(filteredMembers);
}

fetch("./js/data/members.json")
    .then(response => response.json())
    .then(members => {
        allMembers = members;
        renderCards(allMembers);
    })
    .catch(error => {
        console.error('Failed to load members:', error);
    });