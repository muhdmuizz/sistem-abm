(() => {
    const form = document.querySelector('[data-program-form]');
    if (!form) return;
    const status = form.querySelector('[data-program-save-status]');
    const dirty = () => {
        if (status) status.textContent = 'Perubahan belum disimpan.';
        form.dispatchEvent(new Event('input', { bubbles: true }));
    };
    const teras = form.querySelector('#program-teras');
    const strategy = form.querySelector('#program-strategy');
    const initiative = form.querySelector('#program-initiative');
    function filter(select, parent, reset = false) {
        if (reset) select.value = '';
        for (const option of select.options) {
            const invalid = !!option.value && option.dataset.parent !== parent.value;
            option.hidden = invalid;
            option.disabled = invalid;
        }
        if (select.selectedOptions[0]?.disabled) select.value = '';
        select.disabled = !parent.value;
    }
    teras.addEventListener('change', () => { filter(strategy, teras, true); filter(initiative, strategy, true); });
    strategy.addEventListener('change', () => filter(initiative, strategy, true));
    filter(strategy, teras); filter(initiative, strategy);
    const showObjectiveDescriptions = () => {
        form.querySelectorAll('[data-objective-description]').forEach(description => {
            const select = document.getElementById(description.dataset.objectiveDescription);
            description.textContent = select.value ? select.selectedOptions[0].textContent : '';
        });
    };
    form.addEventListener('change', showObjectiveDescriptions);
    showObjectiveDescriptions();
    const mode = form.querySelector('#program-kaedah');
    const packageFields = form.querySelector('[data-program-package]');
    let previousMode = mode.value;
    function packageState() {
        packageFields.hidden = mode.value !== 'Pakej';
        packageFields.disabled = mode.value !== 'Pakej';
    }
    mode.addEventListener('change', () => {
        const checked = [...packageFields.querySelectorAll('input:checked')];
        if (mode.value !== 'Pakej' && checked.length) {
            if (!window.confirm('Komponen Pakej akan dikosongkan dalam draf apabila disimpan. Teruskan?')) {
                mode.value = previousMode; return;
            }
            checked.forEach(input => { input.checked = false; });
        }
        previousMode = mode.value; packageState();
    });
    packageState();
    const place = form.querySelector('#program-place');
    const placeInput = form.querySelector('#program-place-search');
    place?.addEventListener('change', dirty);
    let nextRow = 1000000;
    function classify(row) {
        const category = row.querySelector('[data-person-category]').value;
        const classification = row.querySelector('[data-person-class]');
        const note = row.querySelector('[data-person-note]');
        const other = category === 'Lain-lain';
        classification.disabled = other;
        classification.required = !other;
        classification.closest('label').hidden = other;
        note.disabled = !other; note.required = other;
        row.querySelector('[data-note-label]').hidden = !other;
        const min = ['Pegawai', 'Guru'].includes(category) ? 9 : 1;
        const max = category === 'AKP' ? 8 : category.startsWith('Murid') ? 6 : null;
        classification.min = String(min);
        if (max) classification.max = String(max); else classification.removeAttribute('max');
        row.querySelector('[data-class-label]').textContent = category === 'Murid Rendah' ? 'Tahun' : category === 'Murid Menengah' ? 'Tingkatan' : 'Gred';
        row.querySelector('[data-class-help]').textContent = max ? `${min}–${max}` : '9 dan ke atas';
    }
    function refresh(group) {
        const rows = [...group.querySelectorAll('[data-person-row]')];
        let total = 0n, valid = true;
        rows.forEach((row, index) => {
            classify(row);
            for (const input of row.querySelectorAll('[name]')) {
                input.name = input.name.replace(/\[\d+\]/, `[${index}]`);
            }
            const value = row.querySelector('[data-person-count]').value;
            if (/^[1-9]\d*$/.test(value)) total += BigInt(value); else valid = false;
        });
        group.querySelector('[data-person-total]').textContent = valid ? total.toString() : 'Semak bilangan';
        group.querySelector('[data-no-person]').hidden = rows.length > 0;
    }
    form.querySelectorAll('[data-person-group]').forEach(group => {
        refresh(group);
        group.addEventListener('click', event => {
            if (event.target.closest('[data-add-person]')) {
                group.querySelector('[data-person-rows]').insertAdjacentHTML('beforeend', group.querySelector('template').innerHTML.replaceAll('NEWROW', String(nextRow++)));
                refresh(group); dirty();
                group.querySelector('[data-person-row]:last-child select').focus();
            }
            const remove = event.target.closest('[data-remove-person]');
            if (remove) {
                remove.closest('[data-person-row]').remove(); refresh(group); dirty();
                group.querySelector('[data-add-person]')?.focus();
            }
        });
        group.addEventListener('input', () => refresh(group));
        group.addEventListener('change', event => {
            if (event.target.matches('[data-person-category]')) {
                const row = event.target.closest('[data-person-row]');
                row.querySelector('[data-person-class]').value = '';
                row.querySelector('[data-person-note]').value = '';
            }
            refresh(group);
        });
    });
    form.addEventListener('input', () => { if(status) status.textContent = 'Perubahan belum disimpan.'; });
    form.addEventListener('submit', event => { if (status && !event.defaultPrevented) status.textContent = 'Menyimpan draf…'; });
    const errors = document.querySelector('[data-program-errors]');
    if (errors) {
        if (status) dirty();
        const messages = JSON.parse(errors.dataset.messages || '{}');
        let number = 0;
        for (let [key, values] of Object.entries(messages)) {
            key = key.replace(/^rekod\./, '');
            const parts = key.split('.');
            const name = parts.shift() + parts.map(part => `[${part}]`).join('');
            const target = [...form.elements].find(input => input.name === name || input.name.startsWith(name + '['));
            if (!target) continue;
            const message = document.createElement('span');
            message.className = 'program-field-error'; message.id = `program-error-${number++}`;
            message.textContent = values.join(' ');
            const control = target === place && placeInput ? placeInput : target;
            control.setAttribute('aria-invalid', 'true'); control.setAttribute('aria-describedby', message.id);
            if (control === placeInput) placeInput.after(message);
            else control.closest('label')?.append(message);
        }
        errors.focus();
    }
})();
