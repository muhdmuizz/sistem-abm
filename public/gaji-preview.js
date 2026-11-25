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

    const totalCells = [...preview.querySelectorAll('[data-jik-total]')];
    const countFormat = new Intl.NumberFormat('en-MY');

    function updateJikTotals() {
        const totals = { establishment: 0, filled: 0, vacant: 0 };
        for (const row of jikRows.rows) {
            const [, establishment, occupancy] = row.querySelectorAll('input');
            const posts = Math.max(0, establishment.valueAsNumber || 0);
            const filled = Math.max(0, occupancy.valueAsNumber || 0);
            const vacant = Math.max(0, posts - filled);
            occupancy.max = establishment.value;
            row.querySelector('output').value = String(vacant);
            totals.establishment += posts;
            totals.filled += filled;
            totals.vacant += vacant;
        }
        for (const cell of totalCells) {
            const value = countFormat.format(totals[cell.dataset.jikTotal]);
            if (cell.textContent !== value) cell.textContent = value;
        }
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
        updateJikTotals();
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

    function annualAllowances(monthlyValues, months) {
        const annual = monthlyValues.map(value => {
            const cents = parseMoney(value.trim());
            return cents === null ? null : cents * months;
        });
        const hasInvalid = monthlyValues.some((value, index) => value.trim() && annual[index] === null);
        const total = hasInvalid || annual.every(value => value === null)
            ? null
            : annual.reduce((sum, value) => sum + (value ?? 0n), 0n);
        return { annual, total };
    }

    function addPosition(position, vacant) {
        const row = preview.querySelector('[data-wizard-template]').content.firstElementChild.cloneNode(true);
        const [officer, salary] = row.querySelectorAll('input');
        const [grade, tpg] = row.querySelectorAll('select');
        const allowances = [...row.querySelectorAll('[data-allowance-monthly]')];
        const annualCells = [...row.querySelectorAll('[data-annual]')];
        const allowanceTotal = row.querySelector('[data-allowance-total]');
        const monthsInYear = vacant ? 3 : 12;
        const annualMultiplier = BigInt(monthsInYear);
        const os13000Monthly = row.querySelector('[data-os13000-monthly]');
        const os13000Annual = row.querySelector('[data-os13000-annual]');
        const payrollTotal = row.querySelector('[data-payroll-total]');
        const totals = { salary: null, allowances: null, contributions: null };

        function setTotal(key, amount) {
            totals[key] = amount;
            const available = Object.values(totals).filter(value => value !== null);
            payrollTotal.textContent = available.length ? money(available.reduce((sum, value) => sum + value, 0n)) : '—';
        }
        const otherItems = row.querySelector('[data-other-items]');
        const otherAdd = row.querySelector('[data-other-add]');
        const otherMonthTotal = row.querySelector('[data-other-month-total]');
        const otherYearTotal = row.querySelector('[data-other-year-total]');
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
            const originalMonths = vacant ? monthsInYear : months;
            const newMonths = vacant ? 0 : monthsInYear - months;
            const baharu = !vacant && asal !== null && rate ? asal + rate.kgt : null;
            const first = asal === null ? null : asal * BigInt(originalMonths);
            const second = baharu === null ? null : baharu * BigInt(newMonths);
            if (vacant) salary.value = asal === null ? '' : money(asal);
            const salaryTotal = first !== null && (vacant || second !== null) ? first + (second ?? 0n) : null;
            setTotal('salary', salaryTotal);
            const values = {
                'asal-months': originalMonths,
                'asal-subtotal': first === null ? null : money(first),
                kgt: !vacant && rate ? money(rate.kgt) : null,
                baharu: baharu === null ? null : money(baharu),
                'baharu-months': vacant ? null : newMonths,
                'baharu-subtotal': second === null ? null : money(second),
                total: salaryTotal === null ? null : money(salaryTotal),
            };
            for (const [key, value] of Object.entries(values)) cells[key].textContent = value ?? '—';
        }
        function recalculateAllowances() {
            const values = allowances.map(input => input.value);
            const fixed = annualAllowances(values, annualMultiplier);
            fixed.annual.forEach((amount, index) => {
                annualCells[index].textContent = amount === null ? '—' : money(amount);
                allowances[index].setAttribute('aria-invalid', String(Boolean(values[index].trim()) && amount === null));
            });

            let otherMonthly = null;
            let otherInvalid = false;
            for (const item of otherItems.children) {
                const name = item.querySelector('[data-other-name]');
                const monthly = item.querySelector('[data-other-monthly]');
                const started = Boolean(name.value.trim() || monthly.value.trim());
                const amount = parseMoney(monthly.value.trim());
                const valid = Boolean(name.value.trim()) && amount !== null;
                name.setAttribute('aria-invalid', String(started && !name.value.trim()));
                monthly.setAttribute('aria-invalid', String(started && amount === null));
                item.querySelector('[data-other-item-annual]').textContent = valid ? money(amount * annualMultiplier) : '—';
                if (valid) otherMonthly = (otherMonthly ?? 0n) + amount;
                if (started && !valid) otherInvalid = true;
            }

            const otherYearly = otherMonthly === null ? null : otherMonthly * annualMultiplier;
            otherMonthTotal.textContent = otherInvalid || otherMonthly === null ? '—' : money(otherMonthly);
            otherYearTotal.textContent = otherInvalid || otherYearly === null ? '—' : money(otherYearly);
            const fixedInvalid = values.some((value, index) => value.trim() && fixed.annual[index] === null);
            const annualTotal = fixedInvalid || otherInvalid || (fixed.total === null && otherYearly === null)
                ? null
                : (fixed.total ?? 0n) + (otherYearly ?? 0n);
            allowanceTotal.textContent = annualTotal === null ? '—' : money(annualTotal);
            setTotal('allowances', annualTotal);
        }

        row.addEventListener('input', event => {
            if (event.target.matches('[data-allowance-monthly], [data-other-name], [data-other-monthly]')) {
                recalculateAllowances();
            } else if (event.target === os13000Monthly) {
                const value = os13000Monthly.value.trim();
                const monthly = parseMoney(value);
                os13000Monthly.setAttribute('aria-invalid', String(Boolean(value) && monthly === null));
                const annual = monthly === null ? null : monthly * annualMultiplier;
                os13000Annual.textContent = annual === null ? '—' : money(annual);
                setTotal('contributions', annual);
            } else {
                recalculate();
            }
        });
        otherAdd.addEventListener('click', () => {
            const item = preview.querySelector('[data-other-allowance-template]').content.firstElementChild.cloneNode(true);
            otherItems.append(item);
            item.querySelector('[data-other-name]').focus();
        });
        otherItems.addEventListener('click', event => {
            const remove = event.target.closest('[data-other-remove]');
            if (!remove) return;
            remove.closest('[data-other-item]').remove();
            recalculateAllowances();
            otherAdd.focus();
        });
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

    jik.addEventListener('input', updateJikTotals);
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
        updateJikTotals();
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
