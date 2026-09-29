// JavaScript, app-launcher.js; browser; filter applications already authorized by the server.
(() => {
    const search_input = document.getElementById('appSearch');
    if (!search_input) return;
    const cards = Array.from(document.querySelectorAll('[data-app-card]'));
    const normalize = value => value.toLocaleLowerCase('id').trim();
    const update_results = () => {
        const query = normalize(search_input.value);
        let count = 0;
        cards.forEach(card => {
            card.hidden = !normalize(card.dataset.search).includes(query);
            if (!card.hidden) count++;
        });
        document.getElementById('appSearchCount').textContent = `${count} dari ${cards.length} aplikasi ditampilkan.`;
        document.getElementById('appSearchEmpty').hidden = count !== 0;
    };
    document.getElementById('appSearchControls').hidden = false;
    search_input.addEventListener('input', update_results);
    document.getElementById('clearAppSearch').addEventListener('click', () => {
        search_input.value = '';
        update_results();
        search_input.focus();
    });
})();
