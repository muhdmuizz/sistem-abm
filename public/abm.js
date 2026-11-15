(() => {
    const toggle = document.getElementById('sidebar-toggle');
    const apply = (collapsed) => {
        document.body.classList.toggle('sidebar-collapsed', collapsed);
        toggle?.setAttribute('aria-expanded', String(!collapsed));
    };
    try { apply(localStorage.getItem('abm.sidebar.collapsed') === 'yes'); } catch (_) {}
    toggle?.addEventListener('click', () => {
        const collapsed = !document.body.classList.contains('sidebar-collapsed');
        apply(collapsed);
        try { localStorage.setItem('abm.sidebar.collapsed', collapsed ? 'yes' : 'no'); } catch (_) {}
    });
    const navigation = document.getElementById('mobile-navigation');
    document.getElementById('mobile-open')?.addEventListener('click', () => navigation.showModal());
    document.getElementById('mobile-close')?.addEventListener('click', () => navigation.close());
    const refreshSelectors = (clearInvalid = false) => {
        const identityPtj = document.getElementById('identity-ptj')?.value;
        const identityWorkplace = document.getElementById('identity-workplace');
        if (identityWorkplace) {
            for (const option of identityWorkplace.options) {
                const invalid = !!option.value && option.dataset.parent !== identityPtj;
                option.hidden = invalid;
                option.disabled = invalid;
            }
            if (clearInvalid && identityWorkplace.selectedOptions[0]?.disabled) identityWorkplace.value = '';
        }
        document.querySelectorAll('[data-own-workplace]').forEach(select => {
            let selectedInvalid = false;
            for (const option of select.options) {
                const scopePtj = select.closest('[data-assignment-row]').querySelector('select[name$="[ptj_id]"]')?.value;
                const invalid = !!option.value && (option.value !== identityWorkplace?.value || option.dataset.parent !== scopePtj);
                if (invalid && option.selected) selectedInvalid = true;
                option.hidden = invalid && !option.selected;
                option.disabled = invalid && !option.selected;
            }
            // Never turn a withdrawn own-workplace restriction into an all-workplaces grant.
            select.setCustomValidity(selectedInvalid ? 'Pilih semula skop Tempat Bertugas selepas perubahan organisasi.' : '');
        });
        document.querySelectorAll('.role-group').forEach(group => {
            const enabled = group.querySelector('.role-enabled').checked;
            const count = group.querySelectorAll('[data-assignment-row] input[type=checkbox]:checked').length;
            group.querySelector('.assignment-count').textContent = enabled ? (group.dataset.role === 'superadmin' ? 'Seluruh sistem' : count + ' dipilih') : 'Tidak diberi';
        });
    };
    let nextRow = 100000;
    document.addEventListener('click', event => {
        const add = event.target.closest('.add-assignment');
        if (add) {
            const group = add.closest('.role-group');
            const html = group.querySelector('template').innerHTML.replaceAll('NEWROW', String(nextRow++));
            group.querySelector('.assignment-rows').insertAdjacentHTML('beforeend', html);
            refreshSelectors();
        }
        const remove = event.target.closest('.remove-assignment');
        if (remove) {
            remove.closest('[data-assignment-row]').remove();
            refreshSelectors();
        }
    });
    document.addEventListener('change', event => {
        if (event.target.matches('#identity-ptj, #identity-workplace, select[name$="[ptj_id]"]')) refreshSelectors(true);
        else refreshSelectors();
    });
    document.addEventListener('input', event => {
        const id = event.target.dataset.searchSelect;
        if (!id) return;
        const select = document.getElementById(id);
        const query = event.target.value.toLocaleLowerCase();
        for (const option of select.options) {
            const invalidParent = id === 'identity-workplace' && !!option.value && option.dataset.parent !== document.getElementById('identity-ptj')?.value;
            option.hidden = invalidParent || (!!option.value && !option.selected && !option.text.toLocaleLowerCase().includes(query));
            option.disabled = invalidParent;
        }
    });
    refreshSelectors();
    const dirtyForms = new Set();
    document.querySelectorAll('[data-track-unsaved]').forEach(form => {
        form.addEventListener('input', () => { dirtyForms.add(form); });
        form.addEventListener('change', () => { dirtyForms.add(form); });
        form.addEventListener('click', event => { if (event.target.closest('.add-assignment, .remove-assignment, .add-pair, .remove-pair')) dirtyForms.add(form); });
    });
    window.addEventListener('beforeunload', event => {
        if (dirtyForms.size) { event.preventDefault(); event.returnValue = ''; }
    });
    document.querySelectorAll('form').forEach(form => form.addEventListener('submit', event => {
        if ([...dirtyForms].some(other => other !== form) && !window.confirm('Terdapat perubahan lain yang belum disimpan. Teruskan tanpa menyimpan perubahan lain?')) { event.preventDefault(); return; }
        if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) { event.preventDefault(); return; }
        if (form.dataset.submitting === 'yes') { event.preventDefault(); return; }
        form.dataset.submitting = 'yes';
        dirtyForms.clear();
        form.querySelectorAll('button[type=submit]').forEach(button => { button.disabled = true; button.setAttribute('aria-busy', 'true'); });
    }));
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('form').forEach(form => {
            delete form.dataset.submitting;
            form.querySelectorAll('button[type=submit]').forEach(button => { button.disabled = false; button.removeAttribute('aria-busy'); });
        });
    });
    // Progressive enhancement: the scoped native select remains usable without JavaScript.
    document.querySelectorAll('[data-ptj-combobox]').forEach(root => {
        const select = root.querySelector('select');
        if (!select || !select.options.length) return;
        const choices = [...select.options].filter(option => option.value && !option.disabled).map(option => ({ value: option.value, label: option.text }));
        const shell = document.createElement('div'); shell.className = 'ptj-picker';
        const input = document.createElement('input'); input.id = select.id + '-search'; input.type = 'text'; input.autocomplete = 'off'; input.placeholder = 'Pilih atau cari PTJ'; input.required = select.required;
        input.setAttribute('role', 'combobox'); input.setAttribute('aria-autocomplete', 'list'); input.setAttribute('aria-expanded', 'false'); input.setAttribute('aria-haspopup', 'listbox');
        const button = document.createElement('button'); button.type = 'button'; button.className = 'ptj-picker-toggle'; button.setAttribute('aria-label', 'Buka senarai PTJ'); button.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
        const list = document.createElement('ul'); list.id = select.id + '-options'; list.className = 'ptj-options'; list.setAttribute('role', 'listbox'); list.setAttribute('aria-label', 'Pilihan PTJ'); list.hidden = true; input.setAttribute('aria-controls', list.id);
        const status = document.createElement('span'); status.className = 'sr-only'; status.setAttribute('role', 'status'); status.setAttribute('aria-live', 'polite');
        shell.append(input, button, list, status); root.append(shell);
        const label = document.querySelector('label[for="' + select.id + '"]'); if (label) label.htmlFor = input.id;
        let filtered = choices, active = -1, query = '';
        function selectedLabel() { return choices.find(choice => choice.value === select.value)?.label || ''; }
        input.value = selectedLabel(); select.hidden = true; select.required = false;
        function close() { list.hidden = true; input.setAttribute('aria-expanded', 'false'); input.removeAttribute('aria-activedescendant'); active = -1; }
        function highlight(index) {
            active = index;
            [...list.querySelectorAll('[role=option]')].forEach((option, i) => { option.classList.toggle('is-active', i === active); });
            const option = list.querySelectorAll('[role=option]')[active];
            if (option) { input.setAttribute('aria-activedescendant', option.id); option.scrollIntoView({ block: 'nearest' }); }
            else input.removeAttribute('aria-activedescendant');
        }
        function choose(choice) {
            select.value = choice.value; input.value = choice.label; query = ''; input.setCustomValidity(''); close();
            select.dispatchEvent(new Event('change', { bubbles: true })); input.focus({ preventScroll: true }); close();
        }
        function draw() {
            filtered = choices.filter(choice => choice.label.toLocaleLowerCase().includes(query.trim().toLocaleLowerCase()));
            list.replaceChildren(); active = -1; input.removeAttribute('aria-activedescendant');
            filtered.forEach((choice, i) => {
                const option = document.createElement('li'); option.id = list.id + '-' + i; option.setAttribute('role', 'option'); option.setAttribute('aria-selected', String(choice.value === select.value)); option.textContent = choice.label;
                option.addEventListener('mousedown', event => event.preventDefault()); option.addEventListener('click', () => choose(choice)); list.append(option);
            });
            if (!filtered.length) { const empty = document.createElement('li'); empty.className = 'ptj-no-results'; empty.textContent = 'Tiada PTJ sepadan.'; list.append(empty); }
            list.hidden = false; input.setAttribute('aria-expanded', 'true'); status.textContent = filtered.length ? filtered.length + ' PTJ ditemui.' : 'Tiada PTJ sepadan.';
        }
        input.addEventListener('focus', () => { query = ''; input.select(); draw(); });
        input.addEventListener('input', () => { query = input.value; select.value = ''; input.setCustomValidity(query ? 'Pilih PTJ daripada senarai.' : ''); draw(); });
        input.addEventListener('keydown', event => {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') { event.preventDefault(); if (list.hidden) draw(); highlight(filtered.length ? (active < 0 ? (event.key === 'ArrowDown' ? 0 : filtered.length - 1) : (active + (event.key === 'ArrowDown' ? 1 : -1) + filtered.length) % filtered.length) : -1); }
            else if (event.key === 'Enter' && !list.hidden) { event.preventDefault(); if (active >= 0) choose(filtered[active]); }
            else if (event.key === 'Escape') { event.preventDefault(); close(); }
            else if (event.key === 'Tab') close();
        });
        button.addEventListener('mousedown', event => event.preventDefault());
        button.addEventListener('click', () => { if (!list.hidden) close(); else { input.focus(); query = ''; draw(); } });
        root.addEventListener('focusout', event => { if (!root.contains(event.relatedTarget)) close(); });
        document.addEventListener('pointerdown', event => { if (!root.contains(event.target)) close(); });
        select.addEventListener('change', () => { input.value = selectedLabel(); input.setCustomValidity(''); });
    });

    const deadline = Number(document.body.dataset.deadline) * 1000;
    if (deadline > 0) {
        const warning = document.getElementById('session-warning');
        setTimeout(() => { if (warning && !warning.open) warning.showModal(); }, Math.max(0, deadline - Date.now() - 300000));
        setTimeout(() => { window.location.assign('/login'); }, Math.max(0, deadline - Date.now()));
    }
})();
