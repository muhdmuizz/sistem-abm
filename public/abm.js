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
        form.addEventListener('click', event => { if (event.target.closest('.add-assignment, .remove-assignment')) dirtyForms.add(form); });
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
    const deadline = Number(document.body.dataset.deadline) * 1000;
    if (deadline > 0) {
        const warning = document.getElementById('session-warning');
        setTimeout(() => { if (warning && !warning.open) warning.showModal(); }, Math.max(0, deadline - Date.now() - 300000));
        setTimeout(() => { window.location.assign('/login'); }, Math.max(0, deadline - Date.now()));
    }
})();
