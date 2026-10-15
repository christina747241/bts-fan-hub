const btn = document.querySelector('.facts__btn');
const fact = document.querySelector('.facts__list');

const facts = [
    "BTS is the first K-pop group to reach number one on the Billboard Hot 100 chart.",
    "The name 'BTS' stands for 'Bangtan Sonyeondan', which translates to 'Bulletproof Boy Scouts' in English.",
    "BTS has a massive global fanbase known as the 'ARMY'.",
    "The group debuted on June 13, 2013, under Big Hit Entertainment.",
    "BTS has released multiple albums that have topped charts worldwide, including 'Love Yourself: Tear' and 'Map of the Soul: 7'.",
    "BTS members are known for their involvement in songwriting and producing their own music.",
    "The group has been recognized for their philanthropic efforts, including campaigns against violence and promoting mental health awareness.",
    "BTS has performed at major international events, including the Grammy Awards and the United Nations General Assembly.",
    "The members of BTS are RM, Jin, Suga, J-Hope, Jimin, V, and Jungkook.",
    "BTS has a strong presence on social media platforms, with millions of followers on Twitter, Instagram, and YouTube."
];

btn.addEventListener('click', function(){
    const randomIndex = Math.floor(Math.random() * facts.length);
    fact.textContent = facts[randomIndex];
})