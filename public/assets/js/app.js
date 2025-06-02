const switchLang = document.querySelector('.switch_lang');
const selectedText = switchLang.querySelector('div:first-child');
const options = switchLang.querySelector('.options');
const arrow = switchLang.querySelector('.arrow');
const optionItems = options.querySelectorAll('div');
switchLang.addEventListener('click', function () {
    options.classList.toggle('show');
});
const langMap = {
    en: 'en',
    ar: 'ar',
    kur: 'kur'
};

optionItems.forEach(item => {
    item.addEventListener('click', function (e) {
        e.stopPropagation();
        const selectedLang = this.textContent.trim().toLowerCase();
        selectedText.textContent = selectedLang;
        window.location.href = `/${langMap[selectedLang]}`;
    });
});

document.addEventListener('click', function (e) {
    if (!switchLang.contains(e.target)) {
        options.classList.remove('show');

    }
});
