(() => {
    const update = (group) => {
        let selected = 0;
        const effective = (setting) => {
            const configured = setting.querySelector('.permission-configured');
            const allowed = setting.querySelector('.permission-allowed');
            if (configured) {
                allowed.disabled = !configured.checked;
                if (!configured.checked) allowed.checked = setting.dataset.inherited === 'yes';
                return configured.checked ? allowed.checked : setting.dataset.inherited === 'yes';
            }
            return allowed.checked;
        };
        const parent = effective(group.querySelector('[data-parent-setting="yes"]'));
        group.querySelectorAll('[data-parent-setting="no"]').forEach(setting => {
            if (effective(setting) && parent) selected++;
        });
        group.querySelector('.permission-preview').textContent = 'Pratonton pilihan: ' + selected + ' OS. Belum disimpan.';
    };
    document.querySelectorAll('[data-permission-activity]').forEach(group => {
        update(group);
        group.addEventListener('change', () => update(group));
    });
})();
