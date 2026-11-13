(() => {
    const mode = document.getElementById('phase-scope');
    if (!mode) return;
    const pairs = document.getElementById('phase-pairs');
    let nextPair = 100000;
    const addPair = () => {
        pairs.insertAdjacentHTML('beforeend', document.getElementById('pair-template').innerHTML.replaceAll('NEWPAIR', String(nextPair++)));
    };
    const update = () => {
        document.querySelectorAll('[data-scope-mode]').forEach(panel => {
            const active = panel.dataset.scopeMode === mode.value;
            panel.hidden = !active;
            if (panel instanceof HTMLFieldSetElement) panel.disabled = !active;
            const count = panel.querySelector('.scope-selection-count');
            if (count) count.textContent = panel.querySelectorAll('input[type=checkbox]:checked').length + ' dipilih';
        });
        if (mode.value === 'gabungan' && !pairs.children.length) addPair();
        const complete = [...pairs.querySelectorAll('[data-pair-row]')].filter(row =>
            [...row.querySelectorAll('select')].every(select => select.value !== '')).length;
        document.getElementById('pair-count').textContent = complete + ' kombinasi lengkap';
    };
    mode.addEventListener('change', update);
    document.addEventListener('change', update);
    document.querySelector('.add-pair').addEventListener('click', () => { addPair(); update(); });
    document.addEventListener('click', event => {
        const remove = event.target.closest('.remove-pair');
        if (remove) { remove.closest('[data-pair-row]').remove(); update(); }
    });
    document.querySelectorAll('[data-filter-checklist]').forEach(input => input.addEventListener('input', () => {
        const query = input.value.toLocaleLowerCase();
        document.getElementById(input.dataset.filterChecklist).querySelectorAll('label').forEach(label => {
            label.hidden = !label.textContent.toLocaleLowerCase().includes(query);
        });
    }));
    const start = document.getElementById('phase-start'), end = document.getElementById('phase-end');
    start.addEventListener('change', () => { end.min = start.value || start.min; });
    update();
})();
