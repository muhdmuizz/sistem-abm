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
        if (workplaceField) workplaceField.hidden = index === 0;
        if (index === 0) updateSummary();
        if (focus) tabs[index].focus();
    }

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => selectTab(index));
        tab.addEventListener('keydown', event => {
            const targets = { ArrowLeft: (index + tabs.length - 1) % tabs.length, ArrowRight: (index + 1) % tabs.length, Home: 0, End: tabs.length - 1 };
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
    const rates = JSON.parse(preview.dataset.salaryRates);
    // PP 9/1991 (Utama) and SPP 10/2008 (Khas), matched to the MOF grade codes.
    const itjuRates = new Map([
        ['VU5', '2500.00'], ['VU6', '1500.00'], ['VU7', '1000.00'],
        ['VK5', '2500.00'], ['VK6', '1500.00'], ['VK7', '1000.00'],
    ]);

    const itkItkaRates = new Map();
    const housingRates = new Map();
    const livingRates = new Map();
    for (const [allowance, grade, location, , rate] of JSON.parse(preview.dataset.allowanceRates)) {
        if (allowance === 'Imbuhan Tetap Keraian' || allowance === 'Imbuhan Tetap Khidmat Awam') {
            itkItkaRates.set(grade, rate.replaceAll(',', ''));
        } else if (allowance === 'Imbuhan Tetap Perumahan') {
            housingRates.set('ITP:' + grade, rate.replaceAll(',', ''));
        } else if (allowance === 'Bayaran Imbuhan Perumahan Wilayah') {
            housingRates.set(location + ':' + grade, rate.replaceAll(',', ''));
        } else if (allowance === 'Bayaran Imbuhan Sara Hidup') {
            livingRates.set(location + ':' + grade, rate.replaceAll(',', ''));
        } else if (allowance === 'Bayaran Imbuhan Wilayah') {
            livingRates.set('BIW:' + grade, rate.replaceAll(',', ''));
        }
    }

    const district = preview.dataset.workplaceDistrict.trim();
    const otherDistricts = ['Besut', 'Hulu Terengganu', 'Kuala Nerus', 'Marang', 'Setiu'];
    const bipwLocality = ['Kuala Terengganu', 'Dungun'].includes(district) ? district
        : ['Kemaman', ...otherDistricts].includes(district) ? 'Kawasan lain' : '';
    const bishLocality = ['Kuala Terengganu', 'Dungun', 'Kemaman'].includes(district) ? district
        : otherDistricts.includes(district) ? 'Kawasan lain' : '';

    function allowanceGrade(code) {
        const executiveGrade = /^(?:VU|VK)(\d+)$/.exec(code);
        if (executiveGrade) return { '5': 'A', '6': 'B', '7': 'C' }[executiveGrade[1]];
        return /^[A-Z]*(\d+)$/.exec(code)?.[1];
    }

    function parseMoney(value) {
        if (!/^\d+(?:\.\d{1,2})?$/.test(value)) return null;
        const [whole, fraction = ''] = value.split('.');
        return BigInt(whole) * 100n + BigInt(fraction.padEnd(2, '0'));
    }

    function vu7Increment(salary) {
        if (salary === null || salary === 0n) return null;

        const maximumSalary = 2204000n;
        const maximumSalaryIncrement = 43763n;
        const ordinaryIncrement = (salary * 9n + 50n) / 100n;
        const gap = maximumSalary - salary;

        if (gap >= ordinaryIncrement) return ordinaryIncrement;
        return gap > maximumSalaryIncrement ? gap : maximumSalaryIncrement;
    }

    function money(cents) {
        const digits = cents.toString().padStart(3, '0');
        return AbmMoney.format(digits.slice(0, -2) + '.' + digits.slice(-2));
    }

    const workplace = preview.querySelector('[data-gaji-workplace]');
    const workplaceField = preview.querySelector('[data-gaji-workplace-field]');
    const summaryRows = preview.querySelector('[data-summary-rows]');
    const summaryTotal = preview.querySelector('[data-payroll-summary-total]');
    const drafts = new Map();
    const wizardDrafts = new WeakMap();
    const savedDraft = JSON.parse(preview.dataset.draft);
    const included = new Set(savedDraft?.included ?? []);
    let revision = Number(preview.dataset.draftRevision);
    const saveStatus = preview.querySelector('[data-save-status]');
    const saveRetry = preview.querySelector('[data-save-retry]');
    let pending = false;
    let saving = false;
    let conflict = false;
    let timer;
    let ownsEditorLock = false;
    let leaving = false;

    function captureDraft() {
        const workplaces = Object.fromEntries([...drafts, ...(current ? [[current, {
            jik: [...jikRows.rows], wizard: [...wizardRows.rows], ketua: ketua.value,
        }]] : [])].map(([id, draft]) => [id, {
            jik: draft.jik.map(row => {
                const values = [...row.querySelectorAll('input')].map(field => field.value);
                return [...values, row.dataset.jikId];
            }),
            wizard: draft.wizard.map(row => wizardDrafts.get(row)()),
            ketua: draft.ketua,
        }]));
        return JSON.stringify({ included: [...included], workplaces });
    }

    function restoreDraft(saved) {
        saved.jik.forEach(addJik);
        const groups = new Map();
        for (const row of jikRows.rows) {
            const [name, count] = row.querySelectorAll('input');
            const group = groups.get(name.value.trim()) ?? [];
            group.push({ id: row.dataset.jikId, remaining: count.valueAsNumber });
            groups.set(name.value.trim(), group);
        }
        for (const row of saved.wizard) {
            const candidates = groups.get(row.position) ?? [];
            const match = candidates.length === 1 ? candidates[0] : candidates.find(group => group.remaining > 0);
            if (match) match.remaining--;
            const jikId = Object.hasOwn(row, 'jikId') ? row.jikId : (match?.id ?? null);
            addPosition(row.position, row.vacant, row, jikId);
        }
        updateKetua();
        ketua.value = saved.ketua ?? '';
    }

    async function saveDraft() {
        if (!ownsEditorLock || leaving || document.hidden || saving || !pending || conflict) return;
        saving = true;
        pending = false;
        saveRetry.hidden = true;
        saveStatus.textContent = 'Menyimpan draf…';
        let success = false;
        try {
            const response = await fetch(preview.dataset.saveUrl, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': preview.dataset.csrf },
                body: JSON.stringify({ revision, snapshot: captureDraft() }),
            });
            conflict = response.status === 409;
            if (!response.ok) throw new Error('Autosave failed');
            ({ revision } = await response.json());
            success = true;
            saveStatus.textContent = pending ? 'Perubahan belum disimpan.' : 'Draf disimpan.';
        } catch {
            pending = true;
            saveStatus.textContent = conflict
                ? 'Draf telah berubah di tab lain. Perubahan di tab ini belum disimpan.'
                : 'Draf belum disimpan. Cuba simpan semula.';
            saveRetry.hidden = conflict;
        } finally {
            saving = false;
            if (success && pending) queueSave();
        }
    }

    function queueSave() {
        pending = true;
        if (conflict) return;
        saveStatus.textContent = 'Perubahan belum disimpan.';
        clearTimeout(timer);
        timer = setTimeout(saveDraft, 750);
    }
    const rowAmounts = new WeakMap();
    let current = workplace?.value ?? 'default';
    const jikEmpty = preview.querySelector('[data-jik-empty]');
    const wizardEmpty = preview.querySelector('[data-wizard-empty]');
    const totalCells = [...preview.querySelectorAll('[data-jik-total]')];
    const countFormat = new Intl.NumberFormat('en-MY');
    const incompletePanel = preview.querySelector('[data-incomplete-rows]');
    const incompleteCount = preview.querySelector('[data-incomplete-count]');
    const incompletePicker = preview.querySelector('[data-incomplete-picker]');
    const rowIssues = new WeakMap();

    function updateIncompleteRows() {
        const options = [];
        for (const row of wizardRows.rows) {
            const missing = rowIssues.get(row);
            if (!missing) continue;
            const id = row.dataset.positionId;
            options.push(new Option('#' + id + ' · ' + missing, id));
        }
        incompleteCount.textContent = countFormat.format(options.length) + ' baris belum lengkap.';
        incompletePanel.hidden = options.length === 0;
        incompletePicker.replaceChildren(new Option('Pilih baris', ''), ...options);
    }

    incompletePicker.addEventListener('change', () => {
        const row = wizardRows.rows[Number(incompletePicker.value) - 1];
        if (row) {
            const number = row.querySelector('[data-row-number]');
            number.tabIndex = -1;
            number.focus({ preventScroll: true });
            number.scrollIntoView({ block: 'center', inline: 'nearest' });
        }
        incompletePicker.value = '';
    });

    function jikCounts(row) {
        const [, establishment, occupancy] = row.querySelectorAll('input');
        const posts = Math.max(0, establishment.valueAsNumber || 0);
        const filled = Math.max(0, occupancy.valueAsNumber || 0);
        return [posts, filled, Math.max(0, posts - filled)];
    }

    function writeSummary(row, counts, amounts) {
        const total = amounts.reduce((sum, amount) => sum + amount, 0n);
        const values = [...counts.map(count => countFormat.format(count)), ...amounts.map(money), money(total)];
        row.querySelectorAll('[data-summary-value]').forEach((cell, index) => {
            cell.textContent = values[index];
        });
    }

    function updateSummary() {
        const grandCounts = [0, 0, 0];
        const grandAmounts = [0n, 0n, 0n];
        for (const summaryRow of summaryRows.rows) {
            const id = summaryRow.dataset.payrollSummary;
            const draft = id === current ? { jik: jikRows.rows, wizard: wizardRows.rows } : drafts.get(id);
            const counts = [0, 0, 0];
            const amounts = [0n, 0n, 0n];
            for (const row of draft?.jik ?? []) {
                jikCounts(row).forEach((count, index) => { counts[index] += count; });
            }
            for (const row of draft?.wizard ?? []) {
                const totals = rowAmounts.get(row);
                amounts[0] += totals.salary ?? 0n;
                amounts[1] += totals.allowances ?? 0n;
                amounts[2] += totals.contributions ?? 0n;
            }
            counts.forEach((count, index) => { grandCounts[index] += count; });
            amounts.forEach((amount, index) => { grandAmounts[index] += amount; });
            writeSummary(summaryRow, counts, amounts);
        }
        writeSummary(summaryTotal, grandCounts, grandAmounts);
    }

    function updateJikTotals() {
        jikEmpty.hidden = jikRows.rows.length > 0;
        const totals = { establishment: 0, filled: 0, vacant: 0 };
        for (const row of jikRows.rows) {
            const [, establishment, occupancy] = row.querySelectorAll('input');
            const [posts, filled, vacant] = jikCounts(row);
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

    function addJik(values = null) {
        const row = preview.querySelector('[data-jik-template]').content.firstElementChild.cloneNode(true);
        const input = row.querySelector('[role="combobox"]');
        const list = row.querySelector('[role="listbox"]');
        row.dataset.jikId = values?.[3] || crypto.randomUUID();
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
            input.dispatchEvent(new Event('change', { bubbles: true }));
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
        if (values) row.querySelectorAll('input').forEach((field, index) => { field.value = values[index] ?? ''; });
        jikRows.append(row);
        updateJikTotals();
    }

    function updateKetua() {
        wizardEmpty.hidden = wizardRows.rows.length > 0;
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

    function addPosition(position, vacant, saved = null, jikId = null) {
        const row = preview.querySelector('[data-wizard-template]').content.firstElementChild.cloneNode(true);
        const [officer, salary] = row.querySelectorAll('input');
        const [grade, tpg] = row.querySelectorAll('select');
        const jobRates = rates[position] ?? {};
        for (const code of Object.keys(jobRates)) {
            grade.add(new Option(code, code));
        }
        const allowances = [...row.querySelectorAll('[data-allowance-monthly]')];
        const [itjuMonthly, itkItkaMonthly, housingMonthly, livingMonthly] = allowances;
        const housingAllowance = row.querySelector('[data-housing-allowance]');
        const housingRate = row.querySelector('[data-housing-rate]');
        const livingAllowance = row.querySelector('[data-living-allowance]');
        const livingRate = row.querySelector('[data-living-rate]');
        const legacyLivingRate = saved?.fields[5] ?? '';
        if (legacyLivingRate && (saved.fields.length === 7 || saved.fields[7] === 'legacy')) {
            livingAllowance.add(new Option('Kadar draf terdahulu', 'legacy'));
        }
        livingMonthly.readOnly = true;
        housingAllowance.title = district ? 'Daerah Tempat Bertugas: ' + district : 'Daerah Tempat Bertugas belum tersedia.';
        livingAllowance.title = housingAllowance.title;
        housingMonthly.readOnly = true;
        housingMonthly.placeholder = '—';
        itjuMonthly.readOnly = true;
        itkItkaMonthly.readOnly = true;
        itkItkaMonthly.placeholder = '—';
        const annualCells = [...row.querySelectorAll('[data-annual]')];
        const allowanceTotal = row.querySelector('[data-allowance-total]');
        const monthsInYear = vacant ? 3 : 12;
        const annualMultiplier = BigInt(monthsInYear);
        const os13000Monthly = row.querySelector('[data-os13000-monthly]');
        const os13000Annual = row.querySelector('[data-os13000-annual]');
        const payrollTotal = row.querySelector('[data-payroll-total]');
        const totals = { salary: null, allowances: null, contributions: null };
        rowAmounts.set(row, totals);

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
        row.dataset.jikId = jikId ?? '';
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
            const rate = jobRates[grade.value];
            const months = tpg.value === '' ? null : Number(tpg.value) - 1;
            const asal = vacant ? parseMoney(rate?.median ?? '') : parseMoney(salary.value);
            const kgt = vacant ? null : grade.value === 'VU7' ? vu7Increment(asal) : parseMoney(rate?.kgt ?? '');
            const originalMonths = vacant ? monthsInYear : months;
            const newMonths = vacant || months === null ? null : monthsInYear - months;
            const baharu = newMonths !== null && asal !== null && kgt !== null ? asal + kgt : null;
            const first = asal === null || originalMonths === null ? null : asal * BigInt(originalMonths);
            const second = baharu === null ? null : baharu * BigInt(newMonths);
            if (vacant) salary.value = asal === null ? '' : money(asal);
            const salaryTotal = first !== null && (vacant || second !== null) ? first + (second ?? 0n) : null;
            setTotal('salary', salaryTotal);
            const values = {
                'asal-months': originalMonths,
                'asal-subtotal': first === null ? null : money(first),
                kgt: !vacant && kgt !== null ? money(kgt) : null,
                baharu: baharu === null ? null : money(baharu),
                'baharu-months': vacant ? null : newMonths,
                'baharu-subtotal': second === null ? null : money(second),
                total: salaryTotal === null ? null : money(salaryTotal),
            };
            for (const [key, value] of Object.entries(values)) cells[key].textContent = value ?? '—';
        }
        function recalculateAllowances() {
            const eligibleForItju = /^(?:VU|VK)\d+$/.test(grade.value);
            itjuMonthly.disabled = !eligibleForItju;
            itjuMonthly.placeholder = '—';
            itjuMonthly.value = itjuRates.get(grade.value) ?? '';
            itjuMonthly.title = eligibleForItju
                ? (itjuMonthly.value ? 'Kadar ITJU mengikut gred.' : 'Kadar ITJU belum tersedia untuk gred ini.')
                : 'ITJU hanya untuk Gred VU atau VK.';

            const referenceGrade = allowanceGrade(grade.value);
            // Grade 11 ITK requires university lecturer status and qualifying service.
            itkItkaMonthly.value = referenceGrade === '11' ? '' : (itkItkaRates.get(referenceGrade) ?? '');
            const housingLocality = housingAllowance.value === 'BIPW' ? bipwLocality : housingAllowance.value;
            housingMonthly.value = housingRates.get(housingLocality + ':' + referenceGrade) ?? '';
            const livingLocality = livingAllowance.value === 'BISH' ? bishLocality : livingAllowance.value;
            livingMonthly.value = livingAllowance.value === 'legacy'
                ? legacyLivingRate
                : (livingRates.get(livingLocality + ':' + referenceGrade) ?? '');
            const livingCents = parseMoney(livingMonthly.value.trim());
            livingRate.textContent = livingCents === null ? '—' : money(livingCents);
            const housingCents = parseMoney(housingMonthly.value);
            housingRate.textContent = housingCents === null ? '—' : money(housingCents);
            itkItkaMonthly.title = itkItkaMonthly.value
                ? 'Kadar ITK/ ITKA mengikut gred (REF-08).'
                : 'Tiada kadar ITK/ ITKA yang boleh diisi secara automatik.';

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

        function checkIncompleteRow() {
            const missing = [];
            if (!vacant && !officer.value.trim()) missing.push('Nama Pegawai');
            if (!grade.value) missing.push('Gred');
            if (!vacant && !tpg.value) missing.push('TPG');
            if (!vacant && (parseMoney(salary.value) ?? 0n) <= 0n) missing.push('Gaji Asal');
            for (const input of row.querySelectorAll('[aria-invalid="true"]')) {
                missing.push(input.getAttribute('aria-label') ?? 'Elaun-elaun Lain');
            }
            const issues = [...new Set(missing)].join(', ');
            const changed = issues !== rowIssues.get(row);
            rowIssues.set(row, issues);
            return changed;
        }

        function recalculateContributions() {
            const value = os13000Monthly.value.trim();
            const monthly = parseMoney(value);
            os13000Monthly.setAttribute('aria-invalid', String(Boolean(value) && monthly === null));
            const annual = monthly === null ? null : monthly * annualMultiplier;
            os13000Annual.textContent = annual === null ? '—' : money(annual);
            setTotal('contributions', annual);
        }

        row.addEventListener('input', event => {
            if (event.target.matches('[data-allowance-monthly], [data-other-name], [data-other-monthly]')) {
                recalculateAllowances();
            } else if (event.target === os13000Monthly) {
                recalculateContributions();
            } else {
                recalculate();
            }
            if (checkIncompleteRow()) updateIncompleteRows();
        });
        function addOtherItem(values = ['', '']) {
            const item = preview.querySelector('[data-other-allowance-template]').content.firstElementChild.cloneNode(true);
            item.querySelectorAll('input').forEach((field, index) => { field.value = values[index] ?? ''; });
            otherItems.append(item);
            return item;
        }
        otherAdd.addEventListener('click', () => addOtherItem().querySelector('[data-other-name]').focus());
        otherItems.addEventListener('click', event => {
            const remove = event.target.closest('[data-other-remove]');
            if (!remove) return;
            remove.closest('[data-other-item]').remove();
            recalculateAllowances();
            if (checkIncompleteRow()) updateIncompleteRows();
            otherAdd.focus();
        });
        grade.addEventListener('change', recalculateAllowances);
        housingAllowance.addEventListener('change', recalculateAllowances);
        officer.addEventListener('input', updateKetua);
        livingAllowance.addEventListener('change', recalculateAllowances);
        const fields = [officer, salary, grade, tpg, housingAllowance, livingMonthly, os13000Monthly, livingAllowance];
        wizardDrafts.set(row, () => ({
            position, vacant, jikId,
            fields: fields.map(field => (field === salary && vacant) || (field === livingMonthly && livingAllowance.value !== 'legacy') ? '' : field.value),
            other: [...otherItems.children].map(item => [...item.querySelectorAll('input')].map(field => field.value)),
        }));
        if (saved) {
            fields.forEach((field, index) => { field.value = saved.fields[index] ?? ''; });
            if (['Kuala Terengganu', 'Dungun', 'Kawasan lain'].includes(saved.fields[4])) housingAllowance.value = 'BIPW';
            if (['Kuala Terengganu', 'Dungun', 'Kemaman', 'Kawasan lain'].includes(saved.fields[7])) livingAllowance.value = 'BISH';
            if (saved.fields.length === 7 && legacyLivingRate) livingAllowance.value = 'legacy';
            saved.other.forEach(addOtherItem);
            recalculateContributions();
        }
        wizardRows.append(row);
        recalculate();
        recalculateAllowances();
        checkIncompleteRow();
    }

    function generate() {
        const entries = [...jikRows.rows].map(row => {
            const [name, establishment, occupancy] = row.querySelectorAll('input');
            return { name: name.value.trim(), total: establishment.valueAsNumber, filled: occupancy.valueAsNumber, jikId: row.dataset.jikId };
        });
        if (entries.some(entry => !positions.has(entry.name) || entry.filled > entry.total)) {
            error.textContent = 'Pilih Jawatan daripada senarai dan pastikan Bil. Pengisian tidak melebihi Bil. Perjawatan.';
            error.hidden = false;
            return;
        }
        error.hidden = true;
        if (wizardRows.rows.length && !window.confirm('Jana semula dan ganti semua butiran sedia ada untuk tempat ini?')) return false;
        wizardRows.replaceChildren();
        for (const entry of entries) {
            for (let index = 0; index < entry.total; index++) {
                addPosition(entry.name, index >= entry.filled, null, entry.jikId);
            }
        }
        updateKetua();
        updateIncompleteRows();
        return true;
    }

    function moveJikRow(row, target) {
        if (!row || !target || row === target || row.parentNode !== jikRows || target.parentNode !== jikRows) return;
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const firstIndex = Math.min(row.sectionRowIndex, target.sectionRowIndex);
        const lastIndex = Math.max(row.sectionRowIndex, target.sectionRowIndex);
        const movedRows = reduceMotion ? [] : [...jikRows.rows].slice(firstIndex, lastIndex + 1);
        const before = movedRows.map(item => item.getBoundingClientRect().top);
        for (const item of movedRows) {
            item.getAnimations().forEach(animation => animation.cancel());
        }
        if (row.sectionRowIndex < target.sectionRowIndex) target.after(row);
        else target.before(row);

        const offsets = movedRows.map((item, index) => before[index] - item.getBoundingClientRect().top);
        movedRows.forEach((item, index) => {
            if (!offsets[index]) return;
            item.animate([
                { transform: 'translateY(' + offsets[index] + 'px)' },
                { transform: 'translateY(0)' },
            ], { duration: 150, easing: 'ease-out' });
        });

        const order = new Map([...jikRows.rows].map((jikRow, index) => [jikRow.dataset.jikId, index]));
        const details = [...wizardRows.rows];
        const selectedOfficer = details.find(detail => detail.dataset.positionId === ketua.value);
        details.sort((a, b) => (order.get(a.dataset.jikId) ?? order.size) - (order.get(b.dataset.jikId) ?? order.size));
        details.forEach((detail, index) => {
            detail.dataset.positionId = String(index + 1);
            detail.querySelector('[data-row-number]').textContent = detail.dataset.positionId;
        });
        wizardRows.append(...details);
        updateKetua();
        ketua.value = selectedOfficer?.dataset.positionId ?? '';
        updateIncompleteRows();
        checkForEdits();
    }

    let drag = null;
    let dropTarget = null;

    function updateDropTarget(x, y) {
        const target = document.elementFromPoint(x, y)?.closest('tr');
        const next = target?.parentNode === jikRows && target !== drag.row ? target : null;
        if (next === dropTarget) return;
        dropTarget?.removeAttribute('data-jik-drop-target');
        dropTarget = next;
        if (dropTarget) {
            dropTarget.setAttribute('data-jik-drop-target', drag.row.sectionRowIndex < dropTarget.sectionRowIndex ? 'after' : 'before');
        }
    }

    jikRows.addEventListener('pointerdown', event => {
        const handle = event.target.closest('[data-jik-drag]');
        if (!handle || !event.isPrimary || event.button !== 0 || drag) return;
        handle.setPointerCapture(event.pointerId);
        drag = { row: handle.closest('tr'), pointerId: event.pointerId, x: event.clientX, y: event.clientY, frame: 0 };
    });

    jikRows.addEventListener('pointermove', event => {
        if (!drag || event.pointerId !== drag.pointerId) return;
        const dx = event.clientX - drag.x;
        const dy = event.clientY - drag.y;
        if (!drag.preview) {
            if (Math.hypot(dx, dy) < 4) return;
            const bounds = drag.row.getBoundingClientRect();
            const widths = [...drag.row.cells].map(cell => cell.getBoundingClientRect().width);
            const copy = drag.row.cloneNode(true);
            copy.querySelector('[role="listbox"]')?.remove();
            const sourceInputs = drag.row.querySelectorAll('input');
            copy.querySelectorAll('input').forEach((input, index) => {
                input.value = sourceInputs[index].value;
            });
            const table = document.createElement('table');
            table.className = 'anggaran-grid jik-drag-preview';
            table.inert = true;
            table.setAttribute('aria-hidden', 'true');
            Object.assign(table.style, { left: bounds.left + 'px', top: bounds.top + 'px', width: bounds.width + 'px' });
            const columns = document.createElement('colgroup');
            for (const width of widths) {
                const column = document.createElement('col');
                column.style.width = width + 'px';
                columns.append(column);
            }
            table.append(columns);
            table.createTBody().append(copy);
            document.body.append(table);
            drag.preview = table;
            drag.row.setAttribute('data-jik-dragging', '');
        }
        drag.dx = dx;
        drag.dy = dy;
        if (!drag.frame) {
            drag.frame = requestAnimationFrame(() => {
                drag.frame = 0;
                drag.preview.style.transform = 'translate3d(' + drag.dx + 'px, ' + drag.dy + 'px, 0)';
                updateDropTarget(drag.x + drag.dx, drag.y + drag.dy);
            });
        }
    });

    function finishJikDrag(event) {
        if (!drag || event.pointerId !== drag.pointerId) return;
        if (event.type === 'pointerup' && drag.preview) updateDropTarget(event.clientX, event.clientY);
        const { row, preview: floatingRow, frame } = drag;
        const target = dropTarget;
        drag = null;
        dropTarget = null;
        cancelAnimationFrame(frame);
        floatingRow?.remove();
        row.removeAttribute('data-jik-dragging');
        target?.removeAttribute('data-jik-drop-target');
        if (event.type === 'pointerup' && floatingRow) moveJikRow(row, target);
    }
    for (const type of ['pointerup', 'pointercancel', 'lostpointercapture']) {
        jikRows.addEventListener(type, finishJikDrag);
    }
    jikRows.addEventListener('keydown', event => {
        const handle = event.target.closest('[data-jik-drag]');
        if (!handle || !['ArrowUp', 'ArrowDown'].includes(event.key)) return;
        event.preventDefault();
        const row = handle.closest('tr');
        moveJikRow(row, event.key === 'ArrowUp' ? row.previousElementSibling : row.nextElementSibling);
        handle.focus();
    });

    jik.addEventListener('input', updateJikTotals);
    jik.addEventListener('submit', event => {
        event.preventDefault();
        if ((!workplace || current) && generate()) selectTab(2, true);
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
    addButton.addEventListener('click', () => {
        if (!workplace || current) addJik();
    });

    if (workplace) {
        const available = preview.querySelector('[data-available-workplace]');
        const include = preview.querySelector('[data-include-workplace]');
        const picker = preview.querySelector('[data-workplace-picker]');
        const pickerToggle = picker.querySelector('summary');
        const candidates = [...available.querySelectorAll('input[type="checkbox"]')];
        const checkedWorkplaces = () => candidates.filter(choice => choice.checked && !choice.disabled);
        const generateButton = jik.querySelector('button[type="submit"]');

        function switchWorkplace(next) {
            if (next !== current) {
                if (current) {
                    drafts.set(current, {
                        jik: [...jikRows.rows],
                        wizard: [...wizardRows.rows],
                        ketua: ketua.value,
                    });
                }
                current = next;
                const draft = drafts.get(current);
                drafts.delete(current);
                jikRows.replaceChildren(...(draft?.jik ?? []));
                wizardRows.replaceChildren(...(draft?.wizard ?? []));
                ketua.value = '';
                error.hidden = true;
                updateKetua();
                if (draft) ketua.value = draft.ketua;
                wizardRows.closest('.anggaran-grid-scroll').scrollLeft = 0;
            }
            updateJikTotals();
            updateKetua();
            updateIncompleteRows();
            addButton.disabled = !current;
            generateButton.disabled = !current;
        }

        function renderWorkplaces() {
            const selected = candidates.filter(option => included.has(option.value));
            const rows = selected.map((option, index) => {
                const row = preview.querySelector('[data-summary-workplace-template]').content.firstElementChild.cloneNode(true);
                row.dataset.payrollSummary = option.value;
                row.querySelector('[data-summary-number]').textContent = String(index + 1);
                row.querySelector('[data-summary-name]').textContent = option.dataset.workplaceName;
                row.querySelector('[data-exclude-workplace]').setAttribute('aria-label', 'Keluarkan ' + option.dataset.workplaceName);
                return row;
            });
            summaryRows.replaceChildren(...rows);
            workplace.replaceChildren(...(selected.length
                ? selected.map(option => new Option(option.dataset.workplaceName, option.value))
                : [new Option('Tambah Tempat Bertugas di Rumusan', '')]));
            workplace.value = included.has(current) ? current : (selected[0]?.value ?? '');
            workplace.disabled = !selected.length;
            for (const option of candidates) {
                option.disabled = included.has(option.value);
                option.checked = false;
            }
            include.disabled = true;
            switchWorkplace(workplace.value);
            updateSummary();
        }

        available.addEventListener('change', () => { include.disabled = checkedWorkplaces().length === 0; });
        include.addEventListener('click', () => {
            const checked = checkedWorkplaces();
            if (!checked.length) return;

            checked.forEach(choice => included.add(choice.value));
            renderWorkplaces();
            picker.open = false;
            pickerToggle.focus();
        });
        summaryRows.addEventListener('click', event => {
            const button = event.target.closest('[data-exclude-workplace]');
            if (!button) return;
            const row = button.closest('[data-payroll-summary]');
            const id = row.dataset.payrollSummary;
            const hasEntries = id === current ? jikRows.rows.length || wizardRows.rows.length
                : drafts.get(id)?.jik.length || drafts.get(id)?.wizard.length;
            if (hasEntries && !window.confirm('Keluarkan tempat bertugas daripada Rumusan? Butiran pratonton kekal sehingga halaman dimuat semula.')) return;
            included.delete(id);
            renderWorkplaces();
            pickerToggle.focus();
        });
        workplace.addEventListener('change', () => switchWorkplace(workplace.value));
        renderWorkplaces();
        const first = current;
        for (const [id, saved] of Object.entries(savedDraft?.workplaces ?? {})) {
            switchWorkplace(id);
            restoreDraft(saved);
        }
        switchWorkplace(first);
    } else if (savedDraft?.workplaces.default) {
        restoreDraft(savedDraft.workplaces.default);
    } else {
        addJik();
    }
    updateIncompleteRows();
    updateSummary();

    let previousSnapshot = captureDraft();
    function checkForEdits() {
        if (!ownsEditorLock || leaving || document.hidden) return;
        const snapshot = captureDraft();
        if (snapshot === previousSnapshot) return;
        previousSnapshot = snapshot;
        queueSave();
    }
    for (const type of ['input', 'change', 'submit']) {
        preview.addEventListener(type, () => queueMicrotask(checkForEdits));
    }
    preview.addEventListener('click', event => {
        if (event.target.closest('[data-jik-add], [data-jik-remove], [data-other-add], [data-other-remove], [data-include-workplace], [data-exclude-workplace]')) {
            queueMicrotask(checkForEdits);
        }
    });
    saveStatus.textContent = revision ? 'Draf dimuatkan.' : 'Tiada draf disimpan.';
    saveRetry.addEventListener('click', saveDraft);

    const lockNotice = preview.querySelector('[data-editor-lock]');
    const reloadButton = preview.querySelector('[data-editor-reload]');
    const editingSections = workplaceField ? [...panels, workplaceField] : panels;
    let releaseEditorLock;

    function setEditing(enabled) {
        for (const section of editingSections) section.inert = !enabled;
    }

    function showLocked(message, reload = false) {
        setEditing(false);
        lockNotice.hidden = false;
        lockNotice.textContent = message;
        reloadButton.hidden = !reload;
    }

    reloadButton.addEventListener('click', () => window.location.reload());

    async function openEditor() {
        if (!navigator.locks) {
            showLocked('Pelayar ini tidak menyokong penyuntingan draf yang selamat.');
            return;
        }

        const lockKey = 'abm-payroll-editor:' + preview.dataset.editorAccount + ':' + preview.dataset.saveUrl;
        try {
            await navigator.locks.request(lockKey, { ifAvailable: true }, async lock => {
                if (leaving) return;
                if (!lock) {
                    showLocked('Draf ini sedang disunting di tab lain. Tutup tab tersebut, kemudian muat semula untuk menyunting di sini.', true);
                    return;
                }

                ownsEditorLock = true;
                lockNotice.hidden = true;
                setEditing(!document.hidden);
                await new Promise(resolve => { releaseEditorLock = resolve; });
                ownsEditorLock = false;
            });
        } catch {
            showLocked('Akses penyuntingan tidak dapat disahkan. Muat semula untuk mencuba lagi.', true);
        }
    }

    document.addEventListener('visibilitychange', () => {
        if (!ownsEditorLock || leaving) return;
        setEditing(!document.hidden);
        if (!document.hidden && pending) saveDraft();
    });

    window.addEventListener('pagehide', () => {
        leaving = true;
        setEditing(false);
        clearTimeout(timer);
        releaseEditorLock?.();
    });
    window.addEventListener('pageshow', event => {
        if (event.persisted) window.location.reload();
    });

    openEditor();
    window.addEventListener('beforeunload', event => {
        if (!pending && !saving) return;
        event.preventDefault();
        event.returnValue = '';
    });
})();
