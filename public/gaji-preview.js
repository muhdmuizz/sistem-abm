(() => {
    const preview = document.querySelector('[data-gaji-preview]');
    if (!preview) return;

    const tabs = [...preview.querySelectorAll('[role="tab"]')];
    const panels = tabs.map(tab => document.getElementById(tab.getAttribute('aria-controls')));

    function selectTab(index, focus = false) {
        tabs.forEach((tab, position) => {
            const selected = position === index;
            tab.setAttribute('aria-selected', String(selected));
            tab.tabIndex = selected ? 0 : -1;
            tab.classList.toggle('primary', selected);
            panels[position].hidden = !selected;
        });
        if (focus) tabs[index].focus();
    }

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => selectTab(index));
        tab.addEventListener('keydown', event => {
            const targets = { ArrowLeft: 1 - index, ArrowRight: 1 - index, Home: 0, End: tabs.length - 1 };
            if (!Object.hasOwn(targets, event.key)) return;
            event.preventDefault();
            selectTab(targets[event.key], true);
        });
    });

    const jik = preview.querySelector('[data-jik]');
    const jikRows = preview.querySelector('[data-jik-rows]');
    const wizardRows = preview.querySelector('[data-wizard-rows]');
    const ketua = preview.querySelector('[data-ketua]');
    const error = preview.querySelector('[data-jik-error]');
    const addButton = preview.querySelector('[data-jik-add]');
    const positions = new Set([...preview.querySelectorAll('#jik-jawatan-options option')].map(option => option.value));
    const rates = {
        A: { median: 320000n, kgt: 15000n },
        B: { median: 480000n, kgt: 20000n },
    };

    function parseMoney(value) {
        if (!/^\d+(?:\.\d{1,2})?$/.test(value)) return null;
        const [whole, fraction = ''] = value.split('.');
        return BigInt(whole) * 100n + BigInt(fraction.padEnd(2, '0'));
    }

    function money(cents) {
        const digits = cents.toString().padStart(3, '0');
        return AbmMoney.format(digits.slice(0, -2) + '.' + digits.slice(-2));
    }

    let nextSuggestionId = 0;

    function addJik() {
        const row = preview.querySelector('[data-jik-template]').content.firstElementChild.cloneNode(true);
        const input = row.querySelector('[role="combobox"]');
        const list = row.querySelector('[role="listbox"]');
        list.id = 'jik-jawatan-suggestions-' + (++nextSuggestionId);
        input.setAttribute('aria-controls', list.id);
        let active = -1;

        function close() {
            list.hidden = true;
            active = -1;
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
        }

        function choose(option) {
            input.value = option.textContent;
            close();
            input.focus();
        }

        input.addEventListener('input', () => {
            close();
            const query = input.value.trim().toLocaleLowerCase();
            if (!query) return;

            const matches = [...positions].filter(title => title.toLocaleLowerCase().includes(query));
            list.replaceChildren(...matches.map((title, index) => {
                const option = document.createElement('li');
                option.id = list.id + '-' + index;
                option.setAttribute('role', 'option');
                option.setAttribute('aria-selected', 'false');
                option.textContent = title;
                return option;
            }));
            list.scrollTop = 0;
            list.hidden = matches.length === 0;
            input.setAttribute('aria-expanded', String(!list.hidden));
        });
        input.addEventListener('keydown', event => {
            if (list.hidden) return;
            if (event.key === 'Escape') {
                event.preventDefault();
                close();
            } else if (event.key === 'Enter' && active >= 0) {
                event.preventDefault();
                choose(list.children[active]);
            } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                list.children[active]?.setAttribute('aria-selected', 'false');
                const last = list.children.length - 1;
                active = event.key === 'ArrowDown' ? Math.min(last, active + 1) : active <= 0 ? last : active - 1;
                const option = list.children[active];
                option.setAttribute('aria-selected', 'true');
                input.setAttribute('aria-activedescendant', option.id);
                option.scrollIntoView({ block: 'nearest' });
            }
        });
        input.addEventListener('blur', close);
        list.addEventListener('pointerdown', event => {
            if (event.target.closest('[role="option"]')) event.preventDefault();
        });
        list.addEventListener('click', event => {
            const option = event.target.closest('[role="option"]');
            if (option) choose(option);
        });
        jikRows.append(row);
    }

    function updateKetua() {
        const selection = ketua.value;
        ketua.replaceChildren(new Option('Pilih pegawai', ''));
        for (const row of wizardRows.rows) {
            const officer = row.querySelector('input');
            if (officer.disabled || !officer.value.trim()) continue;
            const position = row.querySelector('[data-position]').textContent;
            ketua.add(new Option(officer.value.trim() + ' · ' + position, row.dataset.positionId));
        }
        ketua.value = selection;
        if (ketua.selectedIndex === -1) ketua.selectedIndex = 0;
    }

    function addPosition(position, vacant) {
        const row = preview.querySelector('[data-wizard-template]').content.firstElementChild.cloneNode(true);
        const [officer, salary] = row.querySelectorAll('input');
        const [grade, tpg] = row.querySelectorAll('select');
        const cells = Object.fromEntries([...row.querySelectorAll('[data-value]')].map(cell => [cell.dataset.value, cell]));
        row.dataset.positionId = String(wizardRows.rows.length + 1);
        row.querySelector('[data-row-number]').textContent = row.dataset.positionId;
        row.querySelector('[data-position]').textContent = position;
        if (vacant) {
            row.classList.add('gaji-vacant');
            officer.disabled = true;
            officer.hidden = true;
            row.querySelector('[data-vacancy]').hidden = false;
            tpg.disabled = true;
            tpg.hidden = true;
            salary.readOnly = true;
            salary.placeholder = '—';
        }

        function recalculate() {
            const rate = rates[grade.value];
            const months = Number(tpg.value) - 1;
            const asal = vacant ? rate?.median ?? null : parseMoney(salary.value);
            const originalMonths = vacant ? 3 : months;
            const newMonths = vacant ? 0 : 12 - months;
            const baharu = !vacant && asal !== null && rate ? asal + rate.kgt : null;
            const first = asal === null ? null : asal * BigInt(originalMonths);
            const second = baharu === null ? null : baharu * BigInt(newMonths);
            if (vacant) salary.value = asal === null ? '' : money(asal);
            const values = {
                'asal-months': originalMonths,
                'asal-subtotal': first === null ? null : money(first),
                kgt: !vacant && rate ? money(rate.kgt) : null,
                baharu: baharu === null ? null : money(baharu),
                'baharu-months': vacant ? null : newMonths,
                'baharu-subtotal': second === null ? null : money(second),
                total: first !== null && (vacant || second !== null) ? money(first + (second ?? 0n)) : null,
            };
            for (const [key, value] of Object.entries(values)) cells[key].textContent = value ?? '—';
        }
        row.addEventListener('input', recalculate);
        officer.addEventListener('input', updateKetua);
        wizardRows.append(row);
        recalculate();
    }

    function generate() {
        const entries = [...jikRows.rows].map(row => {
            const [name, establishment, occupancy] = row.querySelectorAll('input');
            return { name: name.value.trim(), total: establishment.valueAsNumber, filled: occupancy.valueAsNumber };
        });
        if (entries.some(entry => !positions.has(entry.name) || entry.filled > entry.total)) {
            error.textContent = 'Pilih Jawatan daripada senarai dan pastikan Bil. Pengisian tidak melebihi Bil. Perjawatan.';
            error.hidden = false;
            return;
        }
        error.hidden = true;
        wizardRows.replaceChildren();
        for (const entry of entries) {
            for (let index = 0; index < entry.total; index++) {
                addPosition(entry.name, index >= entry.filled);
            }
        }
        updateKetua();
        return true;
    }

    jik.addEventListener('input', event => {
        const row = event.target.closest('tr');
        if (!row) return;
        const [, establishment, occupancy] = row.querySelectorAll('input');
        occupancy.max = establishment.value;
        row.querySelector('output').value = String(Math.max(0, (establishment.valueAsNumber || 0) - (occupancy.valueAsNumber || 0)));
    });
    jik.addEventListener('submit', event => {
        event.preventDefault();
        if (generate()) selectTab(1, true);
    });
    jikRows.addEventListener('click', event => {
        const button = event.target.closest('[data-jik-remove]');
        if (!button) return;
        const row = button.closest('tr');
        const neighbor = row.nextElementSibling ?? row.previousElementSibling;
        row.remove();
        (neighbor?.querySelector('[data-jik-remove]') ?? addButton).focus();
    });
    addButton.addEventListener('click', addJik);
    addJik();
    const firstPosition = positions.values().next().value;
    if (firstPosition) {
        jikRows.rows[0].querySelector('input').value = firstPosition;
        generate();
    }
})();
