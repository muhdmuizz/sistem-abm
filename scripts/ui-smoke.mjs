import './money-check.mjs';
import { chromium } from '@playwright/test';
const browser = await chromium.launch();
const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
await page.goto('http://127.0.0.1:4173/ptj.html');
await page.screenshot({ path: 'storage/app/ui-qa/desktop.png', fullPage: true });
await page.locator('#sidebar-toggle').click();
await page.reload();
if (!(await page.locator(".sidebar").getByRole("link", { name: "Utama", exact: true }).count())) throw Error("Collapsed navigation has no accessible name");
if (!(await page.locator('body').evaluate(el => el.classList.contains('sidebar-collapsed')))) throw Error('Sidebar preference lost');
await page.setViewportSize({ width: 390, height: 844 });
await page.reload();
await page.locator('#mobile-open').click();
if (!(await page.locator('#mobile-navigation').evaluate(el => el.open))) throw Error('Drawer did not open');
await page.keyboard.press('Escape');
if (await page.locator('#mobile-navigation').evaluate(el => el.open)) throw Error('Drawer did not close');
if (await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)) throw Error('Mobile overflow');
await page.screenshot({ path: 'storage/app/ui-qa/mobile.png', fullPage: true });
await page.goto('http://127.0.0.1:4173/login.html');
await page.screenshot({ path: 'storage/app/ui-qa/login.png', fullPage: true });

await page.setViewportSize({ width: 1440, height: 1000 });
await page.goto('http://127.0.0.1:4173/user-form.html');
if (await page.locator('body').evaluate(el => el.classList.contains('sidebar-collapsed'))) await page.locator('#sidebar-toggle').click();
await page.locator('#name').fill('Pegawai Rujukan');
await page.locator('#email').fill('rujukan@example.test');
await page.locator('#identity-ptj').selectOption({ label: 'JPNT' });
const workplace = page.locator('#identity-workplace');
const ownId = await workplace.locator('option').evaluateAll(options => options.find(o => o.textContent === 'Sektor Pengurusan — JPNT')?.value);
if (!ownId) throw Error('Real Sektor option missing');
await workplace.selectOption(ownId);
const group = page.locator('[data-role="penyedia_anggaran"]');
await group.locator('summary').click();
await group.locator('.role-enabled').check();
await group.locator('.add-assignment').click();
await group.locator('.add-assignment').click();
if (await group.locator('[data-assignment-row]').count() !== 2) throw Error('Assignment rows not added');
const ids = await group.locator('[data-assignment-row] [id]').evaluateAll(nodes => nodes.map(n => n.id));
if (new Set(ids).size !== ids.length || ids.some(id => id.includes('NEWROW'))) throw Error('Dynamic row identity collision');
await group.locator('[data-assignment-row]').first().locator('[data-search-select]').fill('JPNT');
await group.locator('[data-assignment-row]').first().locator('select[name$="[ptj_id]"]').selectOption({ label: 'JPNT' });
await group.locator('[data-assignment-row]').first().locator('select[name$="[aktiviti_kewangan_id]"]').selectOption({ index: 1 });
await group.locator('[data-own-workplace]').first().selectOption(ownId);
if (await group.locator('[data-own-workplace]').first().locator('option:not([disabled])').count() !== 2) throw Error('Own workplace selector leaks other workplaces');
const otherId = await workplace.locator('option').evaluateAll(options => options.find(o => o.value && !o.disabled && !o.selected)?.value);
await workplace.selectOption(otherId);
if (await group.locator('[data-own-workplace]').first().inputValue() !== ownId) throw Error('Workplace restriction silently broadened');
if (await group.locator('[data-own-workplace]').first().evaluate(el => el.checkValidity())) throw Error('Invalid own-workplace scope not flagged');
await workplace.selectOption(ownId);
await group.locator('[data-assignment-row]').last().locator('.remove-assignment').click();
if (await group.locator('[data-assignment-row]').count() !== 1) throw Error('Assignment removal failed');
await page.evaluate(() => window.scrollTo(0, 0));
await page.screenshot({ path: 'storage/app/ui-qa/user-form-desktop.png', fullPage: true });
await page.setViewportSize({ width: 390, height: 844 });
if (await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)) throw Error('Mobile user form overflow');
await page.evaluate(() => window.scrollTo(0, 0));
await page.screenshot({ path: 'storage/app/ui-qa/user-form-mobile.png', fullPage: true });
page.on('dialog', dialog => dialog.accept());
await page.goto('http://127.0.0.1:4173/users.html');
if (await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)) throw Error('Mobile users list overflow');
await page.screenshot({ path: 'storage/app/ui-qa/users-mobile.png', fullPage: true });
await page.setViewportSize({ width: 1440, height: 1000 });
await page.screenshot({ path: 'storage/app/ui-qa/users-desktop.png', fullPage: true });

await page.goto('http://127.0.0.1:4173/permissions-category.html');
page.removeAllListeners('dialog');
page.on('dialog', dialog => dialog.accept());
const permissionGroups = page.locator('[data-permission-activity]').filter({ has: page.locator('.permission-os-row') });
const permissionGroup = permissionGroups.first();
await permissionGroup.locator('summary').click();
const parentAllowed = permissionGroup.locator('[data-parent-setting="yes"] .permission-allowed');
const firstOs = permissionGroup.locator('[data-parent-setting="no"]').first();
const osConfigured = firstOs.locator('.permission-configured');
const osAllowed = firstOs.locator('.permission-allowed');
if (!(await osAllowed.isDisabled())) throw Error('Unset OS is editable as an explicit permission');
await osConfigured.check();
if (await osAllowed.isChecked()) throw Error('Configuring OS silently granted it');
await osAllowed.check();
await parentAllowed.uncheck();
if (!(await osAllowed.isChecked())) throw Error('Disabling activity erased an OS selection');
if (!(await permissionGroup.locator('.permission-preview').textContent()).includes('0 OS')) throw Error('Parent masking preview wrong');
await parentAllowed.check();
if (!(await permissionGroup.locator('.permission-preview').textContent()).includes('1 OS')) throw Error('Parent restore preview wrong');
if (await permissionGroup.locator('[data-parent-setting="no"] .permission-allowed:checked').count() !== 1) throw Error('Parent toggle checked all OS');
await osConfigured.uncheck();
if (!(await osAllowed.isDisabled()) || await osAllowed.isChecked()) throw Error('Category unset did not restore unconfigured state');
const otherGroup = permissionGroups.nth(1);
await otherGroup.locator('summary').click();
await otherGroup.locator('[data-parent-setting="yes"] .permission-allowed').uncheck();
await permissionGroup.locator('textarea[name=reason]').fill('QA draft only');
page.removeAllListeners('dialog');
let discardedPrompt = '';
page.on('dialog', dialog => { discardedPrompt = dialog.message(); dialog.dismiss(); });
await permissionGroup.locator('button[type=submit]').click();
if (!discardedPrompt.includes('perubahan lain')) throw Error('Other unsaved activity changes were not protected');
if (await permissionGroup.locator('button[type=submit]').isDisabled()) throw Error('Cancelled save left button disabled');
await otherGroup.locator('summary').click();
await page.evaluate(() => scrollTo(0, 0));
await page.screenshot({ path: 'storage/app/ui-qa/permissions-desktop.png', fullPage: true });
await page.setViewportSize({ width: 390, height: 844 });
await page.evaluate(() => scrollTo(0, 0));
if (await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)) throw Error('Mobile permission editor overflow');
await page.screenshot({ path: 'storage/app/ui-qa/permissions-mobile.png', fullPage: true });
page.removeAllListeners('dialog');
page.on('dialog', dialog => dialog.accept());
await page.goto('http://127.0.0.1:4173/permissions-ptj.html');
const ptjGroup = page.locator('[data-permission-activity]').first();
await ptjGroup.locator('summary').click();
const ptjParent = ptjGroup.locator('[data-parent-setting="yes"]');
if (!(await ptjParent.locator('.permission-allowed').isDisabled())) throw Error('Inherited activity is not distinguished');
await ptjParent.locator('.permission-configured').check();
await ptjParent.locator('.permission-allowed').uncheck();
await ptjParent.locator('.permission-configured').uncheck();
if (!(await ptjParent.locator('.permission-allowed').isChecked())) throw Error('Reset to category inheritance failed');
await page.goto('http://127.0.0.1:4173/permissions.html');
if (await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)) throw Error('Mobile PTJ permission list overflow');
await page.screenshot({ path: 'storage/app/ui-qa/permissions-list-mobile.png', fullPage: true });

await page.goto('http://127.0.0.1:4173/cycles.html');
await page.setViewportSize({ width: 1440, height: 1000 });
const cycleDetails = page.locator('.cycle-record').first();
if (!(await cycleDetails.evaluate(el => el.open))) await cycleDetails.locator(':scope > summary').click();
if (!(await page.getByText('Bidaan Awal', { exact: true }).count())) throw Error('Cycle expansion missing phase record');
await page.evaluate(() => scrollTo(0, 0));
await page.screenshot({ path: 'storage/app/ui-qa/cycles-desktop.png', fullPage: true });
await page.setViewportSize({ width: 390, height: 844 });
if (await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)) throw Error('Mobile cycle list overflow');
await page.screenshot({ path: 'storage/app/ui-qa/cycles-mobile.png', fullPage: true });
await page.goto('http://127.0.0.1:4173/phase-form.html');
await page.setViewportSize({ width: 1440, height: 1000 });
await page.locator('#phase-kind').selectOption('pindaan');
await page.locator('#phase-start').fill('2028-05-01');
await page.locator('#phase-end').fill('2028-05-31');
await page.locator('#phase-scope').selectOption('ptj');
await page.locator('[data-filter-checklist]').fill('JPNT');
const ptjChecks = page.locator('[data-scope-mode="ptj"] input[type=checkbox]');
await ptjChecks.first().check();
await page.locator('#phase-scope').selectOption('gabungan');
if (!(await ptjChecks.first().isDisabled())) throw Error('Inactive scope fields still submitted');
await page.locator('.add-pair').click();
const pairRows = page.locator('[data-pair-row]');
if (await pairRows.count() !== 2) throw Error('Pair entry not added');
const pairIds = await pairRows.locator('[id]').evaluateAll(nodes => nodes.map(node => node.id));
if (new Set(pairIds).size !== pairIds.length) throw Error('Pair control IDs repeated');
await pairRows.first().locator('select[name$="[ptj_id]"]').selectOption({ label: 'JPNT' });
await pairRows.first().locator('select[name$="[aktiviti_kewangan_id]"]').selectOption({ index: 1 });
await pairRows.last().locator('.remove-pair').click();
if (!(await page.locator('#pair-count').textContent()).includes('1 kombinasi lengkap')) throw Error('Pair selection count incorrect');
await page.evaluate(() => scrollTo(0, 0));
await page.screenshot({ path: 'storage/app/ui-qa/phase-form-desktop.png', fullPage: true });
await page.setViewportSize({ width: 390, height: 844 });
await page.evaluate(() => scrollTo(0, 0));
if (await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)) throw Error('Mobile phase form overflow');
await page.screenshot({ path: 'storage/app/ui-qa/phase-form-mobile.png', fullPage: true });

await page.setViewportSize({ width: 1440, height: 1000 });
for (const name of ['bid-context','bid-list','bid-os','bid-empty','bid-no-results','bid-os-empty','bid-activation','bid-validation']) {
    await page.goto('http://127.0.0.1:4173/' + name + '.html');
    if (await page.locator('body').evaluate(el => el.classList.contains('sidebar-collapsed'))) await page.locator('#sidebar-toggle').click();
    if (await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)) throw Error(name + ' desktop overflow');
    await page.screenshot({ path: 'storage/app/ui-qa/' + name + '-desktop.png', fullPage: true });
    if (name === 'bid-context') {
        await page.getByRole('combobox',{name:'PTJ',exact:false}).fill('JPNT');
        await page.getByRole('option',{name:'JPNT',exact:true}).click();
        if (!(await page.locator('#bid-ptj option').evaluateAll(nodes => nodes.some(n => n.textContent === 'JPNT' && !n.hidden)))) throw Error('Scoped PTJ search failed');
    }
    if (name === 'bid-activation') {
        const reason = page.locator('#activation-reason');
        if (!(await reason.getAttribute('required') !== null)) throw Error('Override reason is not required');
        await reason.fill('Pintasan pentadbiran yang diluluskan.');
        if (!(await reason.inputValue()).includes('Pintasan')) throw Error('Reason entry failed');
    }
    if (name === 'bid-validation' && !(await page.locator('#activation-reason[aria-invalid="true"]').count())) throw Error('Reason validation not exposed');
    for (const empty of await page.locator('.empty-state').all()) {
        const insets = await empty.evaluate(el => { const s = getComputedStyle(el); return [s.paddingLeft,s.paddingRight,s.paddingTop,s.paddingBottom].map(parseFloat); });
        if (insets.some(n => n < 16)) throw Error(name + ' empty-state padding too small');
    }
    await page.setViewportSize({ width: 390, height: 844 });
    if (await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)) throw Error(name + ' mobile overflow');
    await page.screenshot({ path: 'storage/app/ui-qa/' + name + '-mobile.png', fullPage: true });
    await page.setViewportSize({ width: 1440, height: 1000 });
}


for (const name of ['estimate-empty','estimate-populated','estimate-workplace','estimate-readonly','estimate-closed','estimate-validation']) {
    await page.goto('http://127.0.0.1:4173/' + name + '.html');
    if (await page.locator('body').evaluate(el => el.classList.contains('sidebar-collapsed'))) await page.locator('#sidebar-toggle').click();
    if (!(await page.locator('.sidebar a[aria-label="Bida Peruntukan"][aria-current="page"]').count())) throw Error('Anggaran lost parent navigation context');
    if (!(await page.getByRole('navigation', { name: 'Navigasi OS' }).count())) throw Error('OS navigation missing');
    if (!(await page.locator('#estimate-os').inputValue())) throw Error('Current OS not selected');
    if (await page.locator('textarea[name=data_sumber],input[name=data_sumber]').count()) throw Error('Opaque source editor exposed');
    if (name === 'estimate-validation' && !(await page.getByRole('alert').count())) throw Error('OS validation summary missing');
    for (const width of [1440,390]) {
        await page.setViewportSize({ width, height: width === 1440 ? 1000 : 844 });
        if (await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)) throw Error(name + ' overflow at ' + width);
        for (const empty of await page.locator('.empty-state').all()) {
            const insets = await empty.evaluate(el => { const s = getComputedStyle(el); return [s.paddingLeft,s.paddingRight,s.paddingTop,s.paddingBottom].map(parseFloat); });
            if (insets.some(n => n < 16)) throw Error(name + ' empty-state padding too small');
        }
        if (width === 390 && name === 'estimate-populated') {
            if (await page.locator('.estimate-sources tr').first().evaluate(el => getComputedStyle(el).display) !== 'block') throw Error('Butiran records not adapted to mobile');
        }
        await page.screenshot({ path: 'storage/app/ui-qa/' + name + '-' + (width === 1440 ? 'desktop' : 'mobile') + '.png', fullPage: true });
    }
    await page.setViewportSize({ width:1440,height:1000 });
}


for (const name of ['history-empty','history-populated','history-duplicated','history-workplace','history-page','history-readonly','history-validation']) {
    await page.goto('http://127.0.0.1:4173/' + name + '.html');
    if (await page.locator('body').evaluate(el => el.classList.contains('sidebar-collapsed'))) await page.locator('#sidebar-toggle').click();
    const history = page.locator('#estimate-history');
    if (await page.content().then(html => html.includes('INTERNAL_REASON_NOT_FOR_DISPLAY') || html.includes('RAW_SNAPSHOT_SECRET'))) throw Error('Raw audit information exposed');
    for (const width of [1440,390]) {
        await page.setViewportSize({ width, height: width === 1440 ? 1000 : 844 });
        if (name === 'history-empty' || name === 'history-populated') {
            await history.evaluate(el => { el.open = false; });
            await page.screenshot({ path: 'storage/app/ui-qa/' + name + '-collapsed-' + width + '.png', fullPage:true });
        }
        if (!(await history.evaluate(el => el.open))) {
            await history.locator(':scope > summary').focus();
            await page.keyboard.press('Enter');
        }
        if (!(await history.evaluate(el => el.open))) throw Error('History keyboard expansion failed');
        if (await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)) throw Error(name + ' overflow at ' + width);
        for (const empty of await history.locator('.empty-state').all()) {
            const padding = await empty.evaluate(el => { const s=getComputedStyle(el);return [s.paddingLeft,s.paddingRight,s.paddingTop,s.paddingBottom].map(parseFloat); });
            if (padding.some(n => n < 16)) throw Error('Audit empty state padding too small');
        }
        if (width === 390 && name === 'history-populated' && await history.locator('tbody tr').first().evaluate(el => getComputedStyle(el).display) !== 'block') throw Error('Audit records did not become mobile cards');
        await page.screenshot({ path: 'storage/app/ui-qa/' + name + '-expanded-' + width + '.png', fullPage:true });
    }
}

{
// Batch 021: real Blade fixtures; HTTP persistence/authorisation is verified by PHPUnit.
page.removeAllListeners('dialog');
page.on('dialog', dialog => dialog.accept());
for (const name of ['program-package','program-empty','program-create','program-list','program-edit','program-validation','program-conflict','program-readonly','program-override']) {
    for (const width of [1440,390]) {
        await page.setViewportSize({width,height:1000});
        await page.goto('http://127.0.0.1:4173/'+name+'.html');
        if (await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)) throw Error(name+' overflow at '+width);
        for (const empty of await page.locator('.empty-state').all()) {
            if (await empty.evaluate(el => ['paddingLeft','paddingRight','paddingTop','paddingBottom'].some(k=>parseFloat(getComputedStyle(el)[k])<16))) throw Error('Programme empty state padding');
        }
        if (name==='program-readonly' && await page.getByRole('button',{name:'Simpan Draf'}).count()) throw Error('Read-only programme has save action');
        if (name==='program-validation' && !(await page.locator('[aria-invalid=true]').count())) throw Error('Programme field error not associated');
        await page.screenshot({path:'storage/app/ui-qa/'+name+'-'+width+'.png',fullPage:true});
    }
}
await page.setViewportSize({width:1440,height:1000});
await page.goto('http://127.0.0.1:4173/program-edit.html');
const form = page.locator('[data-program-form]');
const ts=page.locator('#program-teras'), strategy=page.locator('#program-strategy'), initiative=page.locator('#program-initiative');
const anotherTs=await ts.locator('option').evaluateAll(options=>options.find(o=>o.value&&!o.selected).value);
await ts.selectOption(anotherTs);
if (await strategy.inputValue() || await initiative.inputValue()) throw Error('Objectives did not clear');
if (!(await initiative.isDisabled())) throw Error('Child objective enabled without parent');
const strategyId=await strategy.locator('option').evaluateAll(options=>options.find(o=>o.value&&!o.disabled).value);
await strategy.selectOption(strategyId);
if (await initiative.isDisabled()) throw Error('Prakarsa not enabled');
const initiativeId=await initiative.locator('option').evaluateAll(options=>options.find(o=>o.value&&!o.disabled).value);
await initiative.selectOption(initiativeId);
const mode=page.locator('#program-kaedah');
await mode.selectOption('Pakej');
await page.getByLabel('Penginapan',{exact:true}).check();
if (!(await page.locator('[data-program-package]').isVisible())) throw Error('Package not revealed');
await mode.selectOption('Bukan Pakej');
if (await page.getByLabel('Penginapan',{exact:true}).isChecked()) throw Error('Package not explicitly cleared');
if (await page.locator('[data-program-package]').isVisible()) throw Error('Package not hidden');
const group=page.locator('[data-person-group="peserta"]');
const originalCount=await group.locator('[data-person-row]').count();
await group.locator('[data-add-person]').click();
let row=group.locator('[data-person-row]').last();
await row.locator('[data-person-category]').selectOption('Lain-lain');
if (!(await row.locator('[data-person-class]').isDisabled())) throw Error('Lain-lain classification still submitted');
await row.locator('[data-person-note]').fill('Wakil komuniti');
await row.locator('[data-person-count]').fill('9007199254740993');
if ((await group.locator('[data-person-total]').textContent())!=='9007199254741020') throw Error('Headcount lost integer precision');
await row.locator('[data-person-category]').selectOption('Murid Rendah');
if (await row.locator('[data-person-class]').getAttribute('max')!=='6') throw Error('Murid class range incorrect');
await row.locator('[data-person-class]').fill('2');
await row.locator('[data-person-count]').fill('3');
await group.locator('[data-person-row]').first().locator('[data-remove-person]').click();
const indexes=await group.locator('[data-person-row] [data-person-count]').evaluateAll(inputs=>inputs.map(i=>i.name));
if (indexes.some((name,i)=>name!==`maklumat[peserta][${i}][bilangan]`)) throw Error('Removed programme rows left sparse indexes');
if (await group.locator('[data-person-row]').count()!==originalCount) throw Error('Programme row count mismatch');
const ids=await form.locator('[id]').evaluateAll(nodes=>nodes.map(n=>n.id));
if (new Set(ids).size!==ids.length) throw Error('Programme form duplicate IDs');
if (!(await page.locator('[data-program-save-status]').textContent()).includes('belum disimpan')) throw Error('Unsaved programme status absent');
await page.locator('#program-tarikh_mula').fill('2026-11-14');
if (await page.locator('#program-tarikh_mula').evaluate(el=>el.checkValidity())) throw Error('Date outside Kitaran accepted in browser');
await page.locator('#program-tarikh_mula').fill('2028-02-01');
await page.setViewportSize({width:390,height:844});
if (await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth)) throw Error('Dynamic programme form mobile overflow');
await page.screenshot({path:'storage/app/ui-qa/program-dynamic-390.png',fullPage:true});

}

{
// Batch 022: shared layouts, compact sign-in and independently scrolling navigation.
page.removeAllListeners('dialog');
page.on('dialog', dialog => dialog.accept());
for (const name of ['login-ready','login','login-error','login-status']) {
    for (const [width,height] of [[1366,768],[1280,720],[390,844],[320,568]]) {
        await page.setViewportSize({width,height});
        await page.goto('http://127.0.0.1:4173/'+name+'.html');
        if (await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth)) throw Error(name+' login horizontal overflow');
        if (width>=1280 && await page.evaluate(()=>document.documentElement.scrollHeight>innerHeight)) throw Error(name+' login does not fit a standard laptop display');
        const panel=page.locator('.login-panel');
        if (await panel.evaluate(el=>parseFloat(getComputedStyle(el).paddingLeft))<24) throw Error('Login insets crowded');
        await page.screenshot({path:'storage/app/ui-qa/refined-'+name+'-'+width+'.png',fullPage:true});
    }
}
await page.setViewportSize({width:1366,height:480});
await page.goto('http://127.0.0.1:4173/ptj.html');
if (await page.locator('body').evaluate(el=>el.classList.contains('sidebar-collapsed'))) await page.locator('#sidebar-toggle').click();
const nav=page.locator('.sidebar>nav');
for(const collapsed of [false,true]) {
    if ((await page.locator('body').evaluate(el=>el.classList.contains('sidebar-collapsed')))!==collapsed) await page.locator('#sidebar-toggle').click();
    await nav.evaluate(el=>el.scrollTop=0);
    if (!(await nav.evaluate(el=>el.scrollHeight>el.clientHeight))) throw Error('Short sidebar test has no overflow');
    await nav.hover(); await page.mouse.wheel(0,600);
    await page.waitForFunction(()=>document.querySelector('.sidebar>nav').scrollTop>0);
    await nav.evaluate(el=>el.scrollTop=0); await nav.focus(); await page.keyboard.press('End');
    await page.waitForFunction(()=>document.querySelector('.sidebar>nav').scrollTop>0);
    const toggle=await page.locator('#sidebar-toggle').boundingBox();
    if (!toggle || toggle.y+toggle.height>480) throw Error('Sidebar toggle clipped');
    await page.screenshot({path:'storage/app/ui-qa/sidebar-short-'+(collapsed?'collapsed':'expanded')+'.png',fullPage:true});
}
await page.reload();
if (!(await page.locator('body').evaluate(el=>el.classList.contains('sidebar-collapsed')))) throw Error('Refinement lost collapse preference');
await page.setViewportSize({width:390,height:420});
await page.locator('#mobile-open').click();
const drawer=page.locator('#mobile-navigation');
const mobileNav=drawer.locator('nav');
if (await drawer.evaluate(el=>el.getBoundingClientRect().height)>420) throw Error('Drawer exceeds short viewport');
await mobileNav.focus(); await page.keyboard.press('End');
await page.waitForFunction(()=>document.querySelector('#mobile-navigation>nav').scrollTop>0);
await page.screenshot({path:'storage/app/ui-qa/sidebar-mobile-short.png',fullPage:true});
await page.keyboard.press('Escape');
if (await drawer.evaluate(el=>el.open)) throw Error('Mobile drawer did not close');
for (const name of ['cycles-empty','users-empty']) {
    for (const width of [1366,390]) {
        await page.setViewportSize({width,height:768});
        await page.goto('http://127.0.0.1:4173/'+name+'.html');
        const cell=page.locator('td.table-empty');
        if (!(await cell.count())) throw Error(name+' empty cell missing');
        if (await cell.evaluate(el=>['paddingLeft','paddingRight','paddingTop','paddingBottom'].some(k=>parseFloat(getComputedStyle(el)[k])<16))) throw Error(name+' empty-state padding regressed');
        if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth)) throw Error(name+' overflow');
        await page.screenshot({path:'storage/app/ui-qa/refined-'+name+'-'+width+'.png',fullPage:true});
    }
}
await page.goto('http://127.0.0.1:4173/program-readonly.html');
if (!(await page.locator('.sidebar a[aria-label="Bida Peruntukan"][aria-current="page"]').count())) throw Error('Programme active parent missing');
if (await page.locator('[data-remove-person]:visible').count()) throw Error('Read-only programme displays row removal controls');
if (await page.locator('[data-person-count]').first().evaluate(el=>getComputedStyle(el).backgroundColor)!=='rgb(243, 245, 247)') throw Error('Read-only numerical input styling inconsistent');
await page.goto('http://127.0.0.1:4173/program-edit.html');
if (await page.locator('.program-form').evaluate(el=>getComputedStyle(el).maxWidth)!=='1120px') throw Error('Programme form width constraint invalid');
await page.locator('[data-program-form]').evaluate(el=>el.dispatchEvent(new Event('submit',{bubbles:true,cancelable:true})));
if (!(await page.locator('button[aria-busy=true]:disabled').count())) throw Error('Saving state not disabled/announced');
for (const width of [1366,390]) {
    await page.setViewportSize({width,height:768});
    await page.screenshot({path:'storage/app/ui-qa/refined-saving-'+width+'.png',fullPage:true});
}
}

{
// Shared spreadsheet source editor. Real saves/permissions are exercised through HTTP in PostgreSQL tests.
page.removeAllListeners('dialog');page.on('dialog',dialog=>dialog.accept());
for(const name of ['grid-empty','grid-populated','grid-legacy','grid-readonly']){
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:900});await page.goto('http://127.0.0.1:4173/'+name+'.html');
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error(name+' outer overflow');
  if(width===390&&!(await page.locator('.anggaran-grid-scroll').evaluate(el=>el.scrollWidth>el.clientWidth)))throw Error('Mobile spreadsheet lost tabular horizontal scrolling');
  if(name==='grid-readonly'&&await page.getByRole('button',{name:'Simpan Draf',exact:true}).count())throw Error('Read-only grid exposes save');
  if(name==='grid-legacy'&&await page.content().then(s=>s.includes('SECRET_LEGACY')))throw Error('Opaque source exposed');
  await page.screenshot({path:'storage/app/ui-qa/'+name+'-'+width+'.png',fullPage:true});
 }
}
await page.setViewportSize({width:1440,height:900});await page.goto('http://127.0.0.1:4173/grid-empty.html');
const grid=page.locator('[data-anggaran-grid]');
await page.locator('[data-grid-add-count]').fill('5');await page.locator('[data-grid-add]').click();
if(await page.locator('[data-grid-body] tr').count()!==5)throw Error('Bulk rows not added');
const first=page.locator('[data-grid-body] tr').first();
await first.locator('[data-field=kategori1]').selectOption('Gula-gula');await first.locator('[data-field=bil_unit]').fill('2.625');
if(await first.locator('[data-output=unit]').textContent()!=='Balang/ Paket')throw Error('Reference compound unit split');
if(await first.locator('[data-output=preview]').getAttribute('data-exact')!=='39.375')throw Error('Preview was rounded or lost precision');
await first.locator('[data-row-select]').check();await page.locator('[data-grid-copy]').click();
if(await page.locator('[data-grid-body] tr').count()!==6)throw Error('Local row duplication failed');
if(await page.locator('[data-grid-body] tr').last().locator('[data-field=bil_unit]').inputValue()!=='2.625')throw Error('Copy dropped current inputs');
const place=await grid.evaluate(el=>JSON.parse(el.dataset.places)[0].nama);
await first.locator('[data-field=tempat_bertugas_id]').evaluate((el,text)=>{
 const transfer=new DataTransfer();transfer.setData('text/plain',text);el.dispatchEvent(new ClipboardEvent('paste',{clipboardData:transfer,bubbles:true,cancelable:true}));
},place+'\tAir Minuman\t9007199254740993\n'+place+'\tBiskut\t3');
if(await page.locator('[data-grid-body] tr').first().locator('[data-output=preview]').getAttribute('data-exact')!=='135107988821114895')throw Error('Clipboard quantity precision lost');
const second=page.locator('[data-grid-body] tr').nth(1);await second.locator('[data-field=kategori1]').focus();await page.keyboard.press('Tab');
if(!(await second.locator('[data-field=bil_unit]').evaluate(el=>document.activeElement===el)))throw Error('Tab between inline inputs failed');
let lastPayload;
await page.route('**/bida-peruntukan/**/anggaran/**/jadual',async route=>{lastPayload=route.request().postDataJSON();await route.fulfill({status:422,contentType:'application/json',body:JSON.stringify({errors:{'rekod.0.bil_unit':['Semak Bil. Unit.']}})});});
await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForSelector('[aria-invalid=true]');
if(lastPayload.rekod.some(r=>'kadar'in r||'unit'in r||'data_sumber'in r))throw Error('Client submits derived/opaque fields');
if(lastPayload.rekod[0].bil_unit!=='9007199254740993')throw Error('Save payload decimal coerced');
if(await page.locator('[data-grid-body] tr').first().locator('[data-field=bil_unit]').inputValue()!=='9007199254740993')throw Error('Validation lost user inputs');
await page.screenshot({path:'storage/app/ui-qa/grid-validation-1440.png',fullPage:true});
await page.setViewportSize({width:390,height:844});await page.screenshot({path:'storage/app/ui-qa/grid-validation-390.png',fullPage:true});
await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
await page.route('**/bida-peruntukan/**/anggaran/**/jadual',route=>route.fulfill({status:409,contentType:'application/json',body:'{}'}));
await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForFunction(()=>document.querySelector('[data-grid-error]').textContent.includes('telah berubah'));
await page.screenshot({path:'storage/app/ui-qa/grid-conflict-390.png',fullPage:true});
await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
await page.route('**/bida-peruntukan/**/anggaran/**/jadual',route=>route.abort());
await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForFunction(()=>document.querySelector('[data-grid-error]').textContent.includes('Sambungan gagal'));
if(await page.getByRole('button',{name:'Simpan Draf',exact:true}).isDisabled())throw Error('Failed save cannot retry');
await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
await page.goto('http://127.0.0.1:4173/grid-populated.html');
const persisted=page.locator('[data-grid-body] tr').first();await persisted.locator('[data-row-select]').check();await page.locator('[data-grid-remove]').click();
if(!(await page.locator('[data-grid-undo]').isVisible()))throw Error('Persisted delete cannot be undone');
await page.locator('[data-grid-undo]').click();
if(await page.locator('[data-grid-body] tr').count()!==2)throw Error('Undo deletion lost rows');
const ids=await page.locator('[data-grid-body] [id]').evaluateAll(nodes=>nodes.map(n=>n.id));if(new Set(ids).size!==ids.length)throw Error('Grid control ID collision');
await page.locator('[data-grid-body] tr').first().locator('[data-row-select]').check();await page.locator('[data-grid-remove]').click();
await page.route('**/bida-peruntukan/**/anggaran/**/jadual',async route=>{lastPayload=route.request().postDataJSON();await route.fulfill({status:409,contentType:'application/json',body:'{}'});});
await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForFunction(()=>document.querySelector('[data-grid-error]').textContent.includes('telah berubah'));
if(lastPayload.padam.length!==1||!lastPayload.padam[0].butiran_id)throw Error('Deletion lacks stable identity/revision');
await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
}


{
// SBK reuses the same grid controls; HTTP tests cover real persistence and activity boundaries.
for(const name of ['sbk-empty','sbk-populated','sbk-menengah','sbk-partial','sbk-legacy','sbk-readonly']){
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:900});await page.goto('http://127.0.0.1:4173/'+name+'.html');
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error(name+' outer overflow');
  if(width===390&&!(await page.locator('.anggaran-grid-scroll').evaluate(el=>el.scrollWidth>el.clientWidth)))throw Error('SBK mobile table containment failed');
  if(name==='sbk-readonly'&&!(await page.locator('[data-grid-body] tr').first().locator('td').nth(4).evaluate(el=>getComputedStyle(el).textAlign==='right')))throw Error('Read-only monthly rate alignment');
  if(name==='sbk-readonly'&&await page.getByRole('button',{name:'Simpan Draf',exact:true}).count())throw Error('SBK read-only save exposed');
  if(name==='sbk-legacy'&&(await page.content()).includes('SECRET_LEGACY'))throw Error('SBK legacy payload exposed');
  await page.screenshot({path:'storage/app/ui-qa/'+name+'-'+width+'.png',fullPage:true});
 }
}
await page.setViewportSize({width:1440,height:900});await page.goto('http://127.0.0.1:4173/sbk-empty.html');
await page.locator('[data-grid-add-count]').fill('2');await page.locator('[data-grid-add]').click();
const first=page.locator('[data-grid-body] tr').first();
if((await first.locator('[data-field=kategori2] option').allTextContents()).length!==5)throw Error('SBK utility choices are not four plus placeholder');
if(!(await first.locator('[data-field=kadar]').evaluate(el=>getComputedStyle(el).textAlign==='right')))throw Error('Monthly rate input alignment');
if(await first.locator('[data-field=kadar]').inputValue()!=='')throw Error('SBK manual rate was fabricated');
await first.locator('[data-field=kategori2]').selectOption('Air');await first.locator('[data-field=kadar]').fill('123.4567');
if(await first.locator('[data-output=kategori1]').textContent()!=='Pukal')throw Error('SBK fixed Pukal missing');
if(await first.locator('[data-output=bil_bulan]').textContent()!=='12')throw Error('SBK fixed months missing');
if(await first.locator('[data-output=preview]').getAttribute('data-exact')!=='1481.4804')throw Error('SBK monthly calculation rounded');
await first.locator('[data-field=kategori2]').focus();await page.keyboard.press('Tab');
if(!(await first.locator('[data-field=kadar]').evaluate(el=>document.activeElement===el)))throw Error('SBK Tab does not reach manual rate');
await first.locator('[data-field=kategori2]').evaluate(el=>{
 const transfer=new DataTransfer();transfer.setData('text/plain','Telefon/ Faks\t9007199254740993\nElektrik\t0.001');
 el.dispatchEvent(new ClipboardEvent('paste',{clipboardData:transfer,bubbles:true,cancelable:true}));
});
if(await page.locator('[data-grid-body] tr').first().locator('[data-output=preview]').getAttribute('data-exact')!=='108086391056891916')throw Error('SBK pasted rate lost precision');
if(await page.locator('[data-grid-body] tr').nth(1).locator('[data-output=preview]').getAttribute('data-exact')!=='0.012')throw Error('SBK fractional rate lost precision');
await page.locator('[data-grid-body] tr').first().locator('[data-row-select]').check();await page.locator('[data-grid-copy]').click();
if(await page.locator('[data-grid-body] tr').count()!==3)throw Error('SBK local duplication failed');
let payload;
await page.route('**/bida-peruntukan/**/anggaran/**/jadual',async route=>{
 payload=route.request().postDataJSON();
 await route.fulfill({status:422,contentType:'application/json',body:JSON.stringify({errors:{'rekod.0.kadar':['Semak KADAR bulanan.']}})});
});
await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForSelector('[aria-invalid=true]');
if(payload.rekod.some(row=>'kategori1'in row||'bil_bulan'in row||'preview'in row||'bil_unit'in row))throw Error('SBK fixed/other-definition fields submitted');
if(payload.rekod[0].kadar!=='9007199254740993'||payload.rekod[0].kategori2!=='Telefon/ Faks')throw Error('SBK save payload lost pasted values');
for(const width of [1440,390]){
 await page.setViewportSize({width,height:900});await page.screenshot({path:'storage/app/ui-qa/sbk-validation-'+width+'.png',fullPage:true});
}
await page.locator('[data-grid-body] tr').first().locator('[data-field=kadar]').focus();
if(await page.locator('[data-grid-body] tr').first().locator('[data-field=kadar]').inputValue()!=='9007199254740993')throw Error('SBK validation lost edits');
await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
}


{
for(const name of ['taska-empty','taska-populated','taska-partial','taska-legacy','taska-readonly']){
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:900});await page.goto('http://127.0.0.1:4173/'+name+'.html');
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error(name+' outer overflow');
  if(width===390&&!(await page.locator('.anggaran-grid-scroll').evaluate(el=>el.scrollWidth>el.clientWidth)))throw Error('TASKA mobile grid containment');
  if(name==='taska-readonly'&&await page.getByRole('button',{name:'Simpan Draf',exact:true}).count())throw Error('TASKA read-only save exposed');
  if(name==='taska-legacy'&&(await page.content()).includes('SECRET_LEGACY'))throw Error('TASKA opaque source exposed');
  await page.screenshot({path:'storage/app/ui-qa/'+name+'-'+width+'.png',fullPage:true});
 }
}
await page.setViewportSize({width:1440,height:900});await page.goto('http://127.0.0.1:4173/taska-empty.html');
await page.locator('[data-grid-add-count]').fill('2');await page.locator('[data-grid-add]').click();
const first=page.locator('[data-grid-body] tr').first();
if(await first.locator('[data-field=nama]').getAttribute('type')!=='text')throw Error('TASKA name is not a text input');
if(await first.locator('[data-field=bil_unit]').inputValue()!==''||await first.locator('[data-field=bil_bulan]').inputValue()!=='')throw Error('TASKA quantities fabricated');
if(await first.locator('[data-output=unit]').textContent()!=='Anak'||await first.locator('[data-output=kadar]').textContent()!=='180.00')throw Error('TASKA fixed reference/unit changed');
await first.locator('[data-field=nama]').fill('Ibu Contoh');await page.keyboard.press('Tab');
if(!(await first.locator('[data-field=bil_unit]').evaluate(el=>document.activeElement===el)))throw Error('TASKA Tab text to quantity');
await first.locator('[data-field=bil_unit]').fill('2');await first.locator('[data-field=bil_bulan]').fill('12');
if(await first.locator('[data-output=preview]').getAttribute('data-exact')!=='4320')throw Error('TASKA multi-child formula');
await first.locator('[data-field=bil_bulan]').fill('-0.5');
if(await first.locator('[data-output=preview]').getAttribute('data-exact')!=='-180')throw Error('TASKA signed draft factor preview differs from server');
const pastedName='Bapa <script>window.TASKA_XSS=1</script>';
await first.locator('[data-field=nama]').focus();
await first.locator('[data-field=nama]').evaluate((el,name)=>{
 const transfer=new DataTransfer();transfer.setData('text/plain',name+'\t9007199254740993\t12\nIbu Contoh\t3\t10');
 el.dispatchEvent(new ClipboardEvent('paste',{clipboardData:transfer,bubbles:true,cancelable:true}));
},pastedName);
if(await page.evaluate(()=>window.TASKA_XSS!==undefined))throw Error('TASKA name executed HTML');
if(await page.locator('[data-grid-body] tr').first().locator('[data-field=nama]').inputValue()!==pastedName)throw Error('TASKA paste lost parent name');
if(await page.locator('[data-grid-body] tr').first().locator('[data-output=preview]').getAttribute('data-exact')!=='19455550390240544880')throw Error('TASKA exact preview lost digits: '+await page.locator('[data-grid-body] tr').first().innerText());
if(await page.locator('[data-grid-body] tr').nth(1).locator('[data-output=preview]').getAttribute('data-exact')!=='5400')throw Error('TASKA second row formula');
await page.locator('[data-grid-body] tr').nth(1).locator('[data-row-select]').check();await page.locator('[data-grid-copy]').click();
if(await page.locator('[data-grid-body] tr').count()!==3||await page.locator('[data-grid-body] tr').last().locator('[data-field=nama]').inputValue()!=='Ibu Contoh')throw Error('TASKA duplicate current name/quantities');
let payload;
await page.route('**/bida-peruntukan/**/anggaran/**/jadual',async route=>{
 payload=route.request().postDataJSON();
 await route.fulfill({status:422,contentType:'application/json',body:JSON.stringify({errors:{'rekod.0.nama':['Semak Nama Ibu/ Bapa.']}})});
});
await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForSelector('[aria-invalid=true]');
if(payload.rekod.some(row=>'kadar'in row||'unit'in row||'preview'in row||'kategori1'in row||'kategori2'in row))throw Error('TASKA derived/other-definition fields submitted');
if(payload.rekod[0].nama!==pastedName||payload.rekod[0].bil_unit!=='9007199254740993'||payload.rekod[0].bil_bulan!=='12')throw Error('TASKA save payload lost source values');
for(const width of [1440,390]){
 await page.setViewportSize({width,height:900});await page.screenshot({path:'storage/app/ui-qa/taska-validation-'+width+'.png',fullPage:true});
}
if(await page.locator('[data-grid-body] tr').first().locator('[data-field=nama]').inputValue()!==pastedName)throw Error('TASKA validation lost name');
await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
}


{
// Shared monetary contract on existing definitions.
for(const [fixture,expected] of [['grid-populated','39.38'],['sbk-populated','1,481.48'],['taska-populated','4,320.00']]){
 await page.setViewportSize({width:1440,height:900});await page.goto('http://127.0.0.1:4173/'+fixture+'.html');
 const row=page.locator('[data-grid-body] tr').first(),out=row.locator('[data-output=preview]');
 if(await out.textContent()!==expected)throw Error(fixture+' monetary presentation');
 if(!(await out.evaluate(el=>getComputedStyle(el).fontVariantNumeric.includes('tabular-nums'))))throw Error('Money is not tabular');
 if(fixture==='grid-populated'){
  await row.locator('.money-exact summary').first().click();
  if(!(await row.locator('.money-exact').first().textContent()).includes('39.375'))throw Error('Exact preview inaccessible');
 }
 await page.screenshot({path:'storage/app/ui-qa/money-'+fixture+'-1440.png',fullPage:true});
}
await page.goto('http://127.0.0.1:4173/sbk-populated.html');
const rate=page.locator('[data-grid-body] tr').first().locator('[data-field=kadar]');
if(await rate.inputValue()!=='123.46')throw Error('Idle manual money not formatted');
await rate.focus();if(await rate.inputValue()!=='123.4567')throw Error('Focus destroyed source precision');
await rate.fill('4,320.001');await page.locator('[data-grid-add-count]').focus();
if(await rate.inputValue()!=='4,320.00')throw Error('Blur money format incorrect');
await rate.focus();if(await rate.inputValue()!=='4320.001')throw Error('Grouped input round-trip precision lost');
await page.locator('[data-grid-body] tr').first().locator('[data-row-select]').check();await page.locator('[data-grid-copy]').click();
let moneyPayload;
await page.route('**/bida-peruntukan/**/anggaran/**/jadual',async route=>{moneyPayload=route.request().postDataJSON();await route.fulfill({status:422,contentType:'application/json',body:JSON.stringify({errors:{'rekod.0.kadar':['Semak KADAR.']}})});});
await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForSelector('[aria-invalid=true]');
if(moneyPayload.rekod[0].kadar!=='4320.001'||moneyPayload.rekod.at(-1).kadar!=='4320.001')throw Error('Money save/copy used rounded display');
await page.locator('[data-grid-body] tr').first().locator('[data-field=kadar]').focus();
if(await page.locator('[data-grid-body] tr').first().locator('[data-field=kadar]').inputValue()!=='4320.001')throw Error('Validation destroyed precise money');
await page.screenshot({path:'storage/app/ui-qa/money-validation-1440.png',fullPage:true});
await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');

for(const fixture of ['maintenance-empty','maintenance-populated','maintenance-partial','maintenance-readonly']){
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:900});await page.goto('http://127.0.0.1:4173/'+fixture+'.html');
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error(fixture+' outer overflow');
  if(!(await page.locator('.anggaran-grid-scroll').evaluate(el=>el.scrollWidth>el.clientWidth)))throw Error('Maintenance detailed grid lost scrolling');
  if(fixture==='maintenance-readonly'&&await page.getByRole('button',{name:'Simpan Draf',exact:true}).count())throw Error('Maintenance read-only save exposed');
  for(const edge of ['left','right']){
   await page.locator('.anggaran-grid-scroll').evaluate((el,edge)=>el.scrollLeft=edge==='left'?0:el.scrollWidth,edge);
   await page.screenshot({path:'storage/app/ui-qa/'+fixture+'-'+width+'-'+edge+'.png',fullPage:true});
  }
 }
}
await page.setViewportSize({width:1440,height:900});await page.goto('http://127.0.0.1:4173/maintenance-empty.html');
await page.locator('[data-grid-add-count]').fill('1');await page.locator('[data-grid-add]').click();
const first=page.locator('[data-grid-body] tr').first();
await first.locator('[data-field=tujuan]').fill('Penyelenggaraan aset');
await first.locator('[data-field=kategori1]').selectOption('Penyelenggaraan Alat Pemadam Api');
await first.locator('[data-field=kategori2]').selectOption('Alat Pemadam Api');
if(!(await first.locator('[data-field=kadar]').evaluate(el=>el.readOnly))||await first.locator('[data-field=kadar]').inputValue()!=='100.00')throw Error('Fixed extinguisher rate editable/wrong');
await first.locator('[data-field=bil_unit]').fill('3');await first.locator('[data-field=kekerapan]').fill('1');await first.locator('[data-field=bil_bulan]').fill('12');
if(await first.locator('[data-output=preview]').textContent()!=='3,600.00')throw Error('Maintenance fixed formula');
await first.locator('[data-field=kategori2]').selectOption('Sistem Pencegahan Kebakaran');
if(!(await first.locator('[data-field=kadar]').isEditable())||await first.locator('[data-field=kadar]').inputValue()!=='')throw Error('Manual rate fabricated or locked');
await first.locator('[data-field=kadar]').fill('1,234.5678');await first.locator('[data-field=tujuan]').focus();
if(await first.locator('[data-field=kadar]').inputValue()!=='1,234.57'||await first.locator('[data-output=preview]').textContent()!=='44,444.44')throw Error('Maintenance grouped manual rate/formula');
await first.locator('[data-field=kategori1]').selectOption('Tebang Pokok');
if(!(await first.locator('[data-field=kategori2]').isDisabled()))throw Error('Tree cutting fabricated child selector');
if(await first.locator('[data-field=kadar]').inputValue()!=='1,500.00'||!(await first.locator('[data-field=kadar]').evaluate(el=>el.readOnly)))throw Error('Tree fixed rate');
if(await first.locator('[data-output=preview]').textContent()!=='54,000.00')throw Error('Tree formula');
for(const width of [1440,390]){
 await page.setViewportSize({width,height:900});
 for(const edge of ['left','right']){
  await page.locator('.anggaran-grid-scroll').evaluate((el,edge)=>el.scrollLeft=edge==='left'?0:el.scrollWidth,edge);
  await page.screenshot({path:'storage/app/ui-qa/maintenance-dynamic-'+width+'-'+edge+'.png',fullPage:true});
 }
}
await page.route('**/bida-peruntukan/**/anggaran/**/jadual',async route=>{moneyPayload=route.request().postDataJSON();await route.fulfill({status:422,contentType:'application/json',body:JSON.stringify({errors:{'rekod.0.tujuan':['Semak tujuan.']}})});});
await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForSelector('[aria-invalid=true]');
if(moneyPayload.rekod[0].kategori2!==null||moneyPayload.rekod[0].kadar!==null)throw Error('Fixed reference or fake child submitted');
await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
}


{
// Header casing is checked on the actual shared table and accessible input labels.
for(const [fixture,labels] of [
 ['grid-populated',['Unit','Kadar (RM)']],['sbk-populated',['Kadar Bulanan (RM)']],
 ['taska-populated',['Bil. Anak','Unit','Kadar Bulanan (RM/ Anak)']],
 ['maintenance-populated',['Kadar (RM)']],['uniform-populated',['Jawatan','Bil. Pengisian','Bil. Pasang','Unit','Kadar (RM)']]
]){
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:900});await page.goto('http://127.0.0.1:4173/'+fixture+'.html');
  const headers=await page.locator('.anggaran-grid th').allTextContents();
  for(const label of labels)if(!headers.includes(label))throw Error(fixture+' missing title-case header '+label);
  if(headers.some(label=>/(^|\W)(UNIT|KADAR|BIL\.)(?=\W|$)/.test(label)))throw Error(fixture+' uppercase header remains');
  const columns=await page.locator('[data-anggaran-grid]').evaluate(el=>JSON.parse(el.dataset.columns));
  for(const column of columns.filter(c=>c.type!=='output')){
   const input=page.locator('[data-grid-body] tr').first().locator('[data-field='+column.key+']');
   if(await input.count()&&!(await input.getAttribute('aria-label')).startsWith(column.label+' '))throw Error('Header/accessibility casing mismatch');
  }
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error(fixture+' header overflow');
  await page.screenshot({path:'storage/app/ui-qa/headers-'+fixture+'-'+width+'.png',fullPage:true});
 }
}
for(const fixture of ['uniform-empty','uniform-populated','uniform-partial','uniform-unpriced','uniform-readonly']){
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:900});await page.goto('http://127.0.0.1:4173/'+fixture+'.html');
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error(fixture+' outer overflow');
  if(fixture==='uniform-readonly'&&await page.getByRole('button',{name:'Simpan Draf',exact:true}).count())throw Error('Uniform read-only save exposed');
  for(const edge of ['left','right']){
   await page.locator('.anggaran-grid-scroll').evaluate((el,edge)=>el.scrollLeft=edge==='left'?0:el.scrollWidth,edge);
   await page.screenshot({path:'storage/app/ui-qa/'+fixture+'-'+width+'-'+edge+'.png',fullPage:true});
  }
 }
}
await page.setViewportSize({width:1440,height:900});await page.goto('http://127.0.0.1:4173/uniform-empty.html');
await page.locator('[data-grid-add-count]').fill('1');await page.locator('[data-grid-add]').click();
const first=page.locator('[data-grid-body] tr').first();
if(await first.locator('[data-field=kategori2]').count()||await first.locator('[data-field=gred]').count())throw Error('Uniform introduced category/grade personnel hierarchy');
const jobs=await first.locator('[data-field=jawatan] option').evaluateAll(options=>options.map(o=>o.value));
if(JSON.stringify(jobs)!==JSON.stringify(['','PEMBANTU KHIDMAT AM','PEMBANTU MAKMAL']))throw Error('Uniform must offer only the two applicable shared jobs');
await first.locator('[data-field=jawatan]').evaluate(el=>{const transfer=new DataTransfer();transfer.setData('text/plain','AKAUNTAN\t5');el.dispatchEvent(new ClipboardEvent('paste',{clipboardData:transfer,bubbles:true,cancelable:true}));});
if(await first.locator('[data-field=jawatan]').inputValue()!==''||!await page.getByText('Pilih Jawatan yang terpakai daripada senarai.',{exact:true}).count())throw Error('Uniform unsupported paste accepted');

await first.locator('[data-field=jawatan]').selectOption('PEMBANTU KHIDMAT AM');
await first.locator('[data-field=kategori1]').selectOption('Pakaian Seragam');
if(await first.locator('[data-field=jawatan]').inputValue()!=='PEMBANTU KHIDMAT AM')throw Error('Category reset independent Jawatan');
if(await first.locator('[data-output=kadar]').textContent()!=='230.00'||await first.locator('[data-output=unit]').textContent()!=='Pasang')throw Error('Uniform prescribed rate/unit');
await first.locator('[data-field=bil_unit]').fill('5');
if(await first.locator('[data-field=kekerapan]').count()||await first.locator('[data-output=kekerapan]').textContent()!=='2')throw Error('Bil. Pasang must be fixed at two');
if(await first.locator('[data-output=preview]').textContent()!=='2,300.00')throw Error('Uniform formula/accounting display');
await first.locator('[data-field=kategori1]').selectOption('Kasut');
if(await first.locator('[data-field=jawatan]').inputValue()!=='PEMBANTU KHIDMAT AM'||await first.locator('[data-output=kadar]').textContent()!=='150.00')throw Error('Shoe Khidmat Am lookup');
await first.locator('[data-field=jawatan]').selectOption('PEMBANTU MAKMAL');
if(await first.locator('[data-output=kadar]').textContent()!=='250.00'||await first.locator('[data-output=unit]').textContent()!=='Pasang')throw Error('Shoe Makmal lookup');
await first.locator('[data-field=jawatan]').selectOption('PEMBANTU MAKMAL');
await page.keyboard.press('Tab');if(!(await first.locator('[data-field=bil_unit]').evaluate(el=>el===document.activeElement)))throw Error('Uniform Jawatan keyboard flow');
await first.locator('[data-field=kategori1]').focus();
await first.locator('[data-field=kategori1]').evaluate(el=>{
 const transfer=new DataTransfer();transfer.setData('text/plain','Pakaian Seragam\tPEMBANTU KHIDMAT AM\t5');
 el.dispatchEvent(new ClipboardEvent('paste',{clipboardData:transfer,bubbles:true,cancelable:true}));
});
if(await first.locator('[data-output=preview]').textContent()!=='2,300.00')throw Error('Uniform paste formula');
await first.locator('[data-row-select]').check();await page.locator('[data-grid-copy]').click();
if(await page.locator('[data-grid-body] tr').count()!==2||await page.locator('[data-grid-body] tr').last().locator('[data-field=jawatan]').inputValue()!=='PEMBANTU KHIDMAT AM')throw Error('Uniform copied source lost job');
let uniformPayload;
await page.route('**/bida-peruntukan/**/anggaran/**/jadual',async route=>{uniformPayload=route.request().postDataJSON();await route.fulfill({status:422,contentType:'application/json',body:JSON.stringify({errors:{'rekod.0.bil_unit':['Semak Bil. Pengisian.']}})});});
await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForSelector('[aria-invalid=true]');
if(uniformPayload.rekod.some(row=>'kadar'in row||'unit'in row||'gred'in row||'kategori2'in row||'kekerapan'in row))throw Error('Uniform submits derived/unapproved fields');
if(uniformPayload.rekod[0].jawatan!=='PEMBANTU KHIDMAT AM'||uniformPayload.rekod[0].bil_unit!=='5')throw Error('Uniform save payload source lost');
for(const width of [1440,390]){
 await page.setViewportSize({width,height:900});await page.evaluate(()=>window.scrollTo(0,0));
 await page.locator('.anggaran-grid-scroll').evaluate(el=>el.scrollLeft=el.scrollWidth);
 await page.screenshot({path:'storage/app/ui-qa/uniform-validation-'+width+'.png',fullPage:true});
}
await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
}

{
await page.setViewportSize({width:1440,height:900});await page.goto('http://127.0.0.1:4173/uniform-unpriced.html');
const job=page.locator('[data-field=jawatan]').first();
if(JSON.stringify(await job.locator('option').evaluateAll(options=>options.map(o=>o.value)))!==JSON.stringify(['','PEMBANTU KHIDMAT AM','PEMBANTU MAKMAL']))throw Error('Old uniform draft reoffered an unrelated job');
if(await job.inputValue()!==''||!await page.getByText('Jawatan asal tidak terpakai.',{exact:false}).count())throw Error('Old uniform draft missing repair notice');
await job.selectOption('PEMBANTU KHIDMAT AM');
if(await page.getByText('Jawatan asal tidak terpakai.',{exact:false}).count()||await page.locator('[data-output=preview]').first().textContent()!=='2,300.00')throw Error('Uniform replacement did not repair preview/notice');
for(const width of [1440,390]){await page.setViewportSize({width,height:900});await page.screenshot({path:'storage/app/ui-qa/uniform-repaired-'+width+'.png',fullPage:true});}
}

// Monthly Touch ’n Go source adapter and locked uniform multiplier.
for(const fixture of ['touchngo-empty','touchngo-populated','touchngo-partial','touchngo-readonly']){
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:900});await page.goto('http://127.0.0.1:4173/'+fixture+'.html');
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1))throw Error('Touch n Go outer overflow');
  if(await page.locator('[data-field=kekerapan],[data-field=kategori1],[data-output=unit]').count())throw Error('Touch n Go inactive field exposed');
  if(fixture==='touchngo-readonly'&&await page.getByRole('button',{name:'Simpan Draf',exact:true}).count())throw Error('Touch n Go readonly save');
  await page.screenshot({path:'storage/app/ui-qa/'+fixture+'-'+width+'-left.png',fullPage:true});
  await page.locator('.anggaran-grid-scroll').evaluate(el=>el.scrollLeft=el.scrollWidth);
  await page.screenshot({path:'storage/app/ui-qa/'+fixture+'-'+width+'-right.png',fullPage:true});
 }
}
{
await page.setViewportSize({width:1440,height:900});await page.goto('http://127.0.0.1:4173/touchngo-empty.html');
await page.locator('[data-grid-add-count]').fill('1');await page.locator('[data-grid-add]').click();
const first=page.locator('[data-grid-body] tr').first();
await first.locator('[data-field=tujuan]').fill('Van JPNT');await first.locator('[data-field=bil_unit]').fill('3');
await first.locator('[data-field=kadar]').fill('2,000.125');await first.locator('[data-field=bil_bulan]').fill('12');
if(await first.locator('[data-output=preview]').textContent()!=='72,004.50')throw Error('Touch n Go exact monthly preview');
await first.locator('[data-field=kadar]').focus();if(await first.locator('[data-field=kadar]').inputValue()!=='2000.125')throw Error('Touch n Go exact edit');
await first.locator('[data-field=tujuan]').focus();
await first.locator('[data-field=tujuan]').evaluate(el=>{const transfer=new DataTransfer();transfer.setData('text/plain','Bas JPNT\t3\t200\t12');el.dispatchEvent(new ClipboardEvent('paste',{clipboardData:transfer,bubbles:true,cancelable:true}));});
if(await first.locator('[data-output=preview]').textContent()!=='7,200.00')throw Error('Touch n Go paste');
await first.locator('[data-row-select]').check();await page.locator('[data-grid-copy]').click();
if(await page.locator('[data-grid-body] tr').count()!==2)throw Error('Touch n Go duplicate');
let payload;await page.route('**/bida-peruntukan/**/anggaran/**/jadual',async route=>{payload=route.request().postDataJSON();await route.fulfill({status:422,contentType:'application/json',body:JSON.stringify({errors:{'rekod.0.kadar':['Semak Kadar Bulanan.']}})});});
await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForSelector('[aria-invalid=true]');
if(payload.rekod.some(row=>'kekerapan'in row||'kategori1'in row||'unit'in row||'preview'in row))throw Error('Touch n Go derived payload');
if(payload.rekod[0].kadar!=='200'||payload.rekod[0].tujuan!=='Bas JPNT')throw Error('Touch n Go source lost');
for(const width of [1440,390]){await page.setViewportSize({width,height:900});await page.evaluate(()=>window.scrollTo(0,0));await page.locator('.anggaran-grid-scroll').evaluate(el=>el.scrollLeft=el.scrollWidth);await page.screenshot({path:'storage/app/ui-qa/touchngo-validation-'+width+'.png',fullPage:true});}
await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
}

// Seven utility choices, fixed twelve-month projection and accounting-style money.
for(const fixture of ['utilities-empty','utilities-populated','utilities-partial','utilities-readonly']){
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:900});await page.goto('http://127.0.0.1:4173/'+fixture+'.html');
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1))throw Error('Utility outer overflow');
  if(await page.locator('[data-field=bil_bulan],[data-field=bil_unit],[data-field=kekerapan]').count())throw Error('Utility fixed/inactive input exposed');
  if(fixture==='utilities-readonly'&&await page.getByRole('button',{name:'Simpan Draf',exact:true}).count())throw Error('Utility readonly save');
  for(const edge of ['left','right']){await page.locator('.anggaran-grid-scroll').evaluate((el,edge)=>el.scrollLeft=edge==='left'?0:el.scrollWidth,edge);await page.screenshot({path:'storage/app/ui-qa/'+fixture+'-'+width+'-'+edge+'.png',fullPage:true});}
 }
}
{
await page.setViewportSize({width:1440,height:900});await page.goto('http://127.0.0.1:4173/utilities-empty.html');
await page.locator('[data-grid-add-count]').fill('1');await page.locator('[data-grid-add]').click();
const first=page.locator('[data-grid-body] tr').first();
await first.locator('[data-field=kategori1]').selectOption('Pukal');
if(await first.locator('[data-field=kategori2] option').count()!==5)throw Error('Utility Pukal choices');
await first.locator('[data-field=kategori2]').selectOption('Air');await first.locator('[data-field=kadar]').fill('1,234.567');
if(await first.locator('[data-output=preview]').textContent()!=='14,814.80'||await first.locator('[data-output=bil_bulan]').textContent()!=='12')throw Error('Utility exact preview/fixed months');
await first.locator('[data-field=kategori1]').selectOption('Bukan Pukal');
if(await first.locator('[data-field=kategori2]').inputValue()!==''||await first.locator('[data-field=kategori2] option').count()!==4)throw Error('Utility dependent reset');
await first.locator('[data-field=kategori2]').selectOption('Bil Telefon');await first.locator('[data-field=kadar]').fill('360');
await first.locator('[data-field=kategori1]').focus();await first.locator('[data-field=kategori1]').evaluate(el=>{const transfer=new DataTransfer();transfer.setData('text/plain','Bukan Pukal\tBil Telefon\t360');el.dispatchEvent(new ClipboardEvent('paste',{clipboardData:transfer,bubbles:true,cancelable:true}));});
if(await first.locator('[data-output=preview]').textContent()!=='4,320.00')throw Error('Utility paste monthly formula');
await first.locator('[data-row-select]').check();await page.locator('[data-grid-copy]').click();if(await page.locator('[data-grid-body] tr').count()!==2)throw Error('Utility duplicate');
let payload;await page.route('**/bida-peruntukan/**/anggaran/**/jadual',async route=>{payload=route.request().postDataJSON();await route.fulfill({status:422,contentType:'application/json',body:JSON.stringify({errors:{'rekod.0.kadar':['Semak Kadar Bulanan.']}})});});
await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForSelector('[aria-invalid=true]');
if(payload.rekod.some(row=>'bil_bulan'in row||'preview'in row||'bil_unit'in row||'kekerapan'in row))throw Error('Utility derived payload');
if(payload.rekod[0].kategori2!=='Bil Telefon'||payload.rekod[0].kadar!=='360')throw Error('Utility source lost');
for(const width of [1440,390]){await page.setViewportSize({width,height:900});await page.evaluate(()=>window.scrollTo(0,0));await page.locator('.anggaran-grid-scroll').evaluate(el=>el.scrollLeft=el.scrollWidth);await page.screenshot({path:'storage/app/ui-qa/utilities-validation-'+width+'.png',fullPage:true});}
await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
}

// Single searchable PTJ control: native fallback, explicit selection and scoped values.
for(const width of [1440,390]){
 await page.setViewportSize({width,height:900});await page.goto('http://127.0.0.1:4173/combobox-context.html');
 const input=page.getByRole('combobox',{name:'PTJ',exact:false});
 if(await page.locator('[data-search-select="bid-ptj"]').count()||await input.count()!==1)throw Error('PTJ retains separate search/select UI');
 await input.focus();
 if(await input.getAttribute('aria-expanded')!=='true'||await page.getByRole('listbox',{name:'Pilihan PTJ'}).getByRole('option').count()<8)throw Error('PTJ dropdown did not expose scoped choices');
 if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1))throw Error('PTJ combobox outer overflow');
 await page.screenshot({path:'storage/app/ui-qa/ptj-combobox-open-'+width+'.png',fullPage:true});
 await input.fill('nErUs');if(await page.getByRole('listbox',{name:'Pilihan PTJ'}).getByRole('option').count()!==1)throw Error('PTJ case-insensitive filtering');
 await page.screenshot({path:'storage/app/ui-qa/ptj-combobox-filtered-'+width+'.png',fullPage:true});
 await input.press('ArrowDown');await input.press('Enter');
 if(await input.inputValue()!=='PPD Kuala Nerus'||await input.getAttribute('aria-expanded')!=='false')throw Error('PTJ keyboard choice/close');
 const choice=await page.locator('#bid-ptj').inputValue();
 if(!choice||await page.locator('form.workspace-context').evaluate(form=>new FormData(form).get('ptj_id'))!==choice)throw Error('PTJ selected identity lost');
 await input.fill('No such PTJ');if(await page.locator('#bid-ptj').inputValue()!==''||!await page.getByRole('listbox',{name:'Pilihan PTJ'}).getByText('Tiada PTJ sepadan.',{exact:true}).isVisible())throw Error('PTJ arbitrary text retained selection');
 if(await input.evaluate(el=>el.checkValidity()))throw Error('PTJ free text passes validation');
 await page.screenshot({path:'storage/app/ui-qa/ptj-combobox-no-results-'+width+'.png',fullPage:true});
 await input.press('Escape');if(await input.getAttribute('aria-expanded')!=='false')throw Error('PTJ Escape');
 await input.fill('JPNT');await page.getByRole('option',{name:'JPNT',exact:true}).click();
 if(!await input.evaluate(el=>el.checkValidity()))throw Error('PTJ valid selection rejected');
 await page.screenshot({path:'storage/app/ui-qa/ptj-combobox-selected-'+width+'.png',fullPage:true});
}
{
 const context=await browser.newContext({javaScriptEnabled:false,viewport:{width:1440,height:900}});const fallback=await context.newPage();
 await fallback.goto('http://127.0.0.1:4173/combobox-context.html');
 if(!await fallback.locator('#bid-ptj').isVisible())throw Error('Native PTJ fallback missing');
 await fallback.selectOption('#bid-ptj',{label:'PPD Kuala Nerus'});
 await fallback.screenshot({path:'storage/app/ui-qa/ptj-combobox-no-js.png',fullPage:true});await context.close();
}
for(const width of [1440,390]){
 await page.setViewportSize({width,height:900});await page.goto('http://127.0.0.1:4173/utility-clean-card.html');
 if(await page.locator('.os-reference').filter({hasText:/^OS23000$/}).count()!==1||await page.getByText('Rekod sedia ada',{exact:true}).count())throw Error('Obsolete utility preservation UI remains');
 await page.screenshot({path:'storage/app/ui-qa/utility-clean-card-'+width+'.png',fullPage:true});
}
for(const fixture of ['mystep-empty','mystep-populated','mystep-partial','mystep-readonly']){
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:900});await page.goto('http://127.0.0.1:4173/'+fixture+'.html');
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1))throw Error('MySTEP outer overflow');
  if(await page.locator('[data-field=nama],[data-field=jawatan],[data-field=gred],[data-field=kadar]').count())throw Error('MySTEP inactive/derived inputs exposed');
  if(fixture==='mystep-readonly'&&await page.getByRole('button',{name:'Simpan Draf',exact:true}).count())throw Error('MySTEP readonly save');
  for(const edge of ['left','right']){await page.locator('.anggaran-grid-scroll').evaluate((el,edge)=>el.scrollLeft=edge==='left'?0:el.scrollWidth,edge);await page.screenshot({path:'storage/app/ui-qa/'+fixture+'-'+width+'-'+edge+'.png',fullPage:true});}
 }
}
{
await page.setViewportSize({width:1440,height:900});await page.goto('http://127.0.0.1:4173/mystep-empty.html');
await page.locator('[data-grid-add-count]').fill('1');await page.locator('[data-grid-add]').click();const first=page.locator('[data-grid-body] tr').first();
if(await first.locator('[data-field=kategori1] option').count()!==4||await first.locator('[data-field=tujuan] option').count()!==7||await first.locator('[data-field=kategori2] option').count()!==5)throw Error('MySTEP reference options');
await first.locator('[data-field=kategori1]').selectOption('Guru Ganti');await first.locator('[data-field=tujuan]').selectOption('Projek/ Program/ Ad Hoc/ Tugas Bermusim (Seasonal)');
await first.locator('[data-field=kategori1]').selectOption('Pengurusan');if(await first.locator('[data-field=tujuan]').inputValue()!=='Projek/ Program/ Ad Hoc/ Tugas Bermusim (Seasonal)')throw Error('MySTEP inferred purpose filtering');
await first.locator('[data-field=kategori2]').selectOption('Setaraf Ijazah Sarjana Muda');await first.locator('[data-field=bil_unit]').fill('2');await first.locator('[data-field=bil_bulan]').fill('12');
await first.locator('[data-field=tarikh_mula]').fill('2028-02-28');await first.locator('[data-field=tarikh_tamat]').fill('2028-03-01');
if(await first.locator('[data-output=kadar]').textContent()!=='2,638.35'||await first.locator('[data-output=preview]').textContent()!=='63,320.40'||await first.locator('[data-output=tempoh]').textContent()!=='3')throw Error('MySTEP formula/duration/accounting');
if(await first.locator('[data-field=tarikh_mula]').getAttribute('min')!=='2028-01-01'||await first.locator('[data-field=tarikh_tamat]').getAttribute('max')!=='2028-12-31')throw Error('MySTEP date year bounds');
await first.locator('[data-field=tarikh_tamat]').fill('2028-12-31');if(await first.locator('[data-field=bil_bulan]').inputValue()!=='12')throw Error('MySTEP dates overwrote budget months');
await first.locator('[data-row-select]').check();await page.locator('[data-grid-copy]').click();if(await page.locator('[data-grid-body] tr').count()!==2)throw Error('MySTEP duplicate');
let payload;await page.route('**/bida-peruntukan/**/anggaran/**/jadual',async route=>{payload=route.request().postDataJSON();await route.fulfill({status:422,contentType:'application/json',body:JSON.stringify({errors:{'rekod.0.bil_unit':['Semak Bil. Unit.']}})});});
await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForSelector('[aria-invalid=true]');
if(payload.rekod.some(row=>'kadar'in row||'unit'in row||'tempoh'in row||'nama'in row||'jawatan'in row||'gred'in row))throw Error('MySTEP derived payload');
if(payload.rekod[0].bil_unit!=='2'||payload.rekod[0].bil_bulan!=='12'||payload.rekod[0].tarikh_mula!=='2028-02-28')throw Error('MySTEP source lost');
for(const width of [1440,390]){await page.setViewportSize({width,height:900});await page.evaluate(()=>window.scrollTo(0,0));await page.locator('.anggaran-grid-scroll').evaluate(el=>el.scrollLeft=el.scrollWidth);await page.screenshot({path:'storage/app/ui-qa/mystep-validation-'+width+'.png',fullPage:true});}
await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
}

for(const fixture of ['pakej-empty','pakej-populated','pakej-partial','pakej-readonly','pakej-grouped-empty','pakej-grouped-populated','pakej-grouped-partial','pakej-grouped-readonly']){
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:900});await page.goto('http://127.0.0.1:4173/'+fixture+'.html');await page.waitForSelector('[data-pakej-grid]');
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Pakej page overflow '+fixture);
  if(fixture.endsWith('readonly')&&await page.locator('[data-add-activity]').count())throw Error('Pakej readonly edit action');
  for(const edge of ['left','right']){await page.locator('.anggaran-grid-scroll').evaluateAll((elements,edge)=>elements.forEach(el=>el.scrollLeft=edge==='left'?0:el.scrollWidth),edge);await page.screenshot({path:'storage/app/ui-qa/'+fixture+'-'+width+'-'+edge+'.png',fullPage:true});}
  if(fixture==='pakej-grouped-populated'){await page.locator('.pakej-activity>summary').first().click();await page.screenshot({path:'storage/app/ui-qa/pakej-collapsed-'+width+'.png',fullPage:true});}
 }
}
{
await page.setViewportSize({width:1440,height:900});await page.goto('http://127.0.0.1:4173/pakej-grouped-empty.html');
await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();if(!await page.getByRole('button',{name:'Simpan Draf',exact:true}).isEnabled())throw Error('Empty Pakej save left busy button');
await page.locator('[data-add-activity]').click();const group=page.locator('.pakej-activity').first();
await group.locator('[data-shared=tujuan]').fill('Bengkel Pengurusan');await group.locator('[data-shared=tarikh_mula]').fill('2028-02-28');await group.locator('[data-shared=tarikh_tamat]').fill('2028-03-01');
if(await group.locator('[data-calendar-days]').textContent()!=='3')throw Error('Pakej calendar duration');
for(const [name,qty,rate,days] of [['Penginapan','10','180','2'],['Makan Minum','20','35','3'],['Sewaan Dewan','1','500','3']]){
 await group.getByRole('button',{name:'Tambah Komponen',exact:true}).click();const row=group.locator('tbody tr').last();
 await row.locator('[data-field=kategori1]').selectOption(name);await row.locator('[data-field=bil_unit]').fill(qty);await row.locator('[data-field=kadar]').focus();await row.locator('[data-field=kadar]').fill(rate);await row.locator('[data-field=bil_hari]').fill(days);
}
if(await group.locator('[data-subtotal]').textContent()!=='7,200.00')throw Error('Grouped Pakej subtotal');
const first=group.locator('tbody tr').first();if(!(await first.locator('[data-field=bil_hari]').getAttribute('aria-label')).startsWith('Bil. Malam')||await first.locator('[data-basis]').textContent()!=='RM sebilik semalam')throw Error('Pakej night basis');
await group.locator('[data-shared=tarikh_tamat]').fill('2028-12-31');if(await first.locator('[data-field=bil_hari]').inputValue()!=='2'||await group.locator('[data-subtotal]').textContent()!=='7,200.00')throw Error('Dates overwrote charged duration');
await group.getByRole('button',{name:'Salin Aktiviti',exact:true}).click();if(await page.locator('.pakej-activity').count()!==2)throw Error('Pakej copy activity');
const other=page.locator('.pakej-activity').nth(1);await other.locator('tbody tr').first().locator('[data-field=bil_unit]').fill('99');if(await first.locator('[data-field=bil_unit]').inputValue()!=='10')throw Error('Pakej activity copy shares state');
await other.getByRole('button',{name:'Padam Aktiviti',exact:true}).click();await page.locator('[data-undo-activity]').click();if(await page.locator('.pakej-activity').count()!==2)throw Error('Pakej undo activity');
await page.locator('.pakej-activity').nth(1).getByRole('button',{name:'Padam Aktiviti',exact:true}).click();
await first.locator('[data-component-select]').check();await group.getByRole('button',{name:'Salin Komponen Dipilih',exact:true}).click();if(await group.locator('tbody tr').count()!==4)throw Error('Pakej copy component');
await group.locator('tbody tr').last().locator('[data-component-select]').check();await group.getByRole('button',{name:'Padam Komponen Dipilih',exact:true}).click();await group.getByRole('button',{name:'Batal Padam Komponen',exact:true}).click();if(await group.locator('tbody tr').count()!==4)throw Error('Pakej undo component');
await group.locator('tbody tr').last().locator('[data-component-select]').check();await group.getByRole('button',{name:'Padam Komponen Dipilih',exact:true}).click();
await first.locator('[data-field=kadar]').focus();await first.locator('[data-field=kadar]').fill('1,234.567');await first.locator('[data-field=bil_unit]').focus();
if(await group.locator('[data-subtotal]').getAttribute('data-exact')!=='28291.34')throw Error('Pakej exact subtotal');
await first.locator('.money-exact summary').first().click();await page.screenshot({path:'storage/app/ui-qa/pakej-exact-expanded.png',fullPage:true});
let payload;await page.route('**/bida-peruntukan/**/anggaran/**/jadual',async route=>{payload=route.request().postDataJSON();await route.fulfill({status:422,contentType:'application/json',body:JSON.stringify({errors:{'rekod.0.komponen.1.kadar':['Semak kadar komponen.']}})});});
await group.locator('summary').first().click();await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForSelector('[aria-invalid=true]');
if(!await group.evaluate(el=>el.open))throw Error('Pakej nested error stayed collapsed');
if(payload.rekod.length!==1||payload.rekod[0].komponen.length!==3||payload.rekod[0].komponen[0].kadar!=='1234.567'||'preview'in payload.rekod[0]||'unit'in payload.rekod[0].komponen[0])throw Error('Pakej grouped source payload');
const ids=payload.rekod[0].komponen.map(c=>c.id);if(new Set(ids).size!==3)throw Error('Pakej duplicate component IDs');
for(const width of [1440,390]){await page.setViewportSize({width,height:900});await group.locator('.anggaran-grid-scroll').evaluate(el=>el.scrollLeft=el.scrollWidth);await page.screenshot({path:'storage/app/ui-qa/pakej-grouped-validation-'+width+'.png',fullPage:true});}
await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
await page.route('**/bida-peruntukan/**/anggaran/**/jadual',route=>route.fulfill({status:409,contentType:'application/json',body:'{}'}));await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForFunction(()=>document.querySelector('[data-pakej-error]').textContent.includes('telah berubah'));if(await first.locator('[data-field=kadar]').inputValue()!=='1,234.57')throw Error('Pakej stale save lost exact source display');await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
await page.route('**/bida-peruntukan/**/anggaran/**/jadual',route=>route.abort());await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForFunction(()=>document.querySelector('[data-pakej-error]').textContent.includes('Sambungan gagal'));await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
let release;await page.route('**/bida-peruntukan/**/anggaran/**/jadual',route=>new Promise(resolve=>{release=()=>route.fulfill({status:503,contentType:'application/json',body:'{}'}).then(resolve);}));
await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForFunction(()=>document.querySelector('[data-pakej-grid]').hasAttribute('data-submitting'));
if(!await first.locator('[data-field=kadar]').isDisabled())throw Error('Pakej permits edits during pending save');
for(const width of [1440,390]){await page.setViewportSize({width,height:900});await page.screenshot({path:'storage/app/ui-qa/pakej-grouped-saving-'+width+'.png',fullPage:true});}
await release();await page.waitForFunction(()=>!document.querySelector('[data-pakej-grid]').hasAttribute('data-submitting'));if(await first.locator('[data-field=kadar]').isDisabled())throw Error('Pakej controls not restored after save failure');await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
await first.locator('[data-field=kategori1]').evaluate(el=>el.dispatchEvent(new ClipboardEvent('paste',{bubbles:true,cancelable:true,clipboardData:new DataTransfer()})));
await first.locator('[data-field=kategori1]').evaluate(el=>{const data=new DataTransfer();data.setData('text/plain','Penginapan\t2\t180\t2\nMakan Minum\t20\t35\t3');el.dispatchEvent(new ClipboardEvent('paste',{bubbles:true,cancelable:true,clipboardData:data}));});
if(await group.locator('tbody tr').nth(0).locator('[data-component-amount]').textContent()!=='720.00'||await group.locator('tbody tr').nth(1).locator('[data-component-amount]').textContent()!=='2,100.00')throw Error('Pakej component paste');
}
for(const fixture of ['umum-empty','umum-populated','umum-partial','umum-readonly','umum-grouped-empty','umum-grouped-populated','umum-grouped-partial','umum-grouped-readonly']){
 for(const width of [1440,390]){await page.setViewportSize({width,height:900});await page.goto('http://127.0.0.1:4173/'+fixture+'.html');await page.waitForSelector('[data-pakej-grid]');if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Grouped Umum page overflow');
 if(fixture.endsWith('readonly')&&await page.locator('[data-add-activity]').count())throw Error('Grouped Umum readonly editor');
 for(const edge of ['left','right']){await page.locator('.anggaran-grid-scroll').evaluateAll((elements,edge)=>elements.forEach(el=>el.scrollLeft=edge==='left'?0:el.scrollWidth),edge);await page.screenshot({path:'storage/app/ui-qa/'+fixture+'-'+width+'-'+edge+'.png',fullPage:true});}
 if(fixture==='umum-grouped-populated'){await page.locator('.pakej-activity>summary').first().click();await page.screenshot({path:'storage/app/ui-qa/umum-grouped-collapsed-'+width+'.png',fullPage:true});}}
}
{
await page.setViewportSize({width:1440,height:900});await page.goto('http://127.0.0.1:4173/umum-grouped-empty.html');await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();if(!await page.getByRole('button',{name:'Simpan Draf',exact:true}).isEnabled())throw Error('Umum empty save stuck');
await page.locator('[data-add-activity]').click();const group=page.locator('.pakej-activity').first();await group.locator('[data-shared=tujuan]').fill('Bengkel Pengurusan Kewangan');await group.locator('[data-shared=tarikh_mula]').fill('2028-02-28');await group.locator('[data-shared=tarikh_tamat]').fill('2028-03-01');
await group.getByRole('button',{name:'Tambah Komponen',exact:true}).click();const first=group.locator('tbody tr').first();
await first.locator('[data-field=kategori1]').selectOption('Bayaran Penceramah/ Fasilitator');await first.locator('[data-field=kategori2]').selectOption('Bayaran Penceramah');await first.locator('[data-field=nama]').fill('AHMAD');await first.locator('[data-field=jawatan]').selectOption('JURUTERA');await first.locator('[data-field=gred]').selectOption('J14');await first.locator('[data-field=bil_unit]').fill('3');
if(await first.locator('[data-component-amount]').textContent()!=='600.00'||await first.locator('[data-output=payment_grade]').textContent()!=='Kumpulan P&P Gred 14'||!await first.locator('[data-field=kadar]').evaluate(el=>el.readOnly))throw Error('Grouped Umum speaker rate');
await group.getByRole('button',{name:'Tambah Komponen',exact:true}).click();let second=group.locator('tbody tr').nth(1);await second.locator('[data-field=kategori1]').selectOption('Keraian, Hospitaliti, Honorarium dll.');await second.locator('[data-field=kategori2]').selectOption('Makan Minum (Mesyuarat)');await second.locator('[data-field=bil_unit]').fill('20');
if(await second.locator('[data-field=nama]').count()||await second.locator('[data-component-amount]').textContent()!=='160.00'||await group.locator('[data-subtotal]').textContent()!=='760.00')throw Error('Grouped Umum mixed service subtotal/personnel');
await group.locator('[data-shared=tarikh_tamat]').fill('2028-12-31');if(await group.locator('[data-calendar-days]').textContent()!=='308'||await group.locator('[data-subtotal]').textContent()!=='760.00')throw Error('Grouped Umum date/quantity independence');
await first.locator('[data-field=kategori2]').selectOption('Bayaran Fasilitator');if(await group.locator('[data-subtotal]').textContent()!=='460.00')throw Error('Grouped facilitator subtotal');await first.locator('[data-field=kategori2]').selectOption('Bayaran Penceramah');
await first.locator('[data-field=jawatan]').selectOption('PENGARAH PENDIDIKAN NEGERI');if(await first.locator('[data-field=gred]').inputValue()!==''||await group.locator('[data-subtotal]').textContent()!=='—')throw Error('Grouped Umum stale grade');await first.locator('[data-field=gred]').selectOption('VU7');if(await first.locator('[data-output=payment_grade]').textContent()!=='Kumpulan Pengurusan Tertinggi'||await group.locator('[data-subtotal]').textContent()!=='1,060.00')throw Error('Grouped VU7 rate');
await first.locator('[data-field=jawatan]').selectOption('JURUTERA');await first.locator('[data-field=gred]').selectOption('J14');
await group.getByRole('button',{name:'Salin Aktiviti',exact:true}).click();const copy=page.locator('.pakej-activity').nth(1);await copy.locator('tbody tr').first().locator('[data-field=bil_unit]').fill('99');if(await first.locator('[data-field=bil_unit]').inputValue()!=='3')throw Error('Umum copy shared state');await copy.getByRole('button',{name:'Padam Aktiviti',exact:true}).click();await page.locator('[data-undo-activity]').click();if(await page.locator('.pakej-activity').count()!==2)throw Error('Umum undo activity');await page.locator('.pakej-activity').nth(1).getByRole('button',{name:'Padam Aktiviti',exact:true}).click();
await second.locator('[data-component-select]').check();await group.getByRole('button',{name:'Salin Komponen Dipilih',exact:true}).click();if(await group.locator('tbody tr').count()!==3)throw Error('Umum copy component');await group.locator('tbody tr').last().locator('[data-component-select]').check();await group.getByRole('button',{name:'Padam Komponen Dipilih',exact:true}).click();await group.getByRole('button',{name:'Batal Padam Komponen',exact:true}).click();if(await group.locator('tbody tr').count()!==3)throw Error('Umum undo component');await group.locator('tbody tr').last().locator('[data-component-select]').check();await group.getByRole('button',{name:'Padam Komponen Dipilih',exact:true}).click();
await group.getByRole('button',{name:'Tambah Komponen',exact:true}).click();const manual=group.locator('tbody tr').last();await manual.locator('[data-field=kategori1]').selectOption('Keraian, Hospitaliti, Honorarium dll.');await manual.locator('[data-field=kategori2]').selectOption('Dobi');await manual.locator('[data-field=bil_unit]').fill('2');await manual.locator('[data-field=kadar]').focus();await manual.locator('[data-field=kadar]').fill('1,234.567');await manual.locator('[data-field=bil_unit]').focus();
if(await group.locator('[data-subtotal]').getAttribute('data-exact')!=='3229.134'||await group.locator('[data-subtotal]').textContent()!=='3,229.13')throw Error('Umum grouped exact subtotal');await manual.locator('.money-exact summary').first().click();await page.screenshot({path:'storage/app/ui-qa/umum-grouped-exact-expanded.png',fullPage:true});
let payload;await page.route('**/bida-peruntukan/**/anggaran/**/jadual',async route=>{payload=route.request().postDataJSON();await route.fulfill({status:422,contentType:'application/json',body:JSON.stringify({errors:{'rekod.0.komponen.0.gred':['Semak gred komponen.']}})});});await group.locator('summary').first().click();await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForSelector('[aria-invalid=true]');
if(!await group.evaluate(el=>el.open)||!await page.getByRole('button',{name:'Simpan Draf',exact:true}).isEnabled())throw Error('Umum nested error/recovery');if(payload.rekod.length!==1||payload.rekod[0].komponen.length!==3||payload.rekod[0].komponen[0].kadar!==null||payload.rekod[0].komponen[1].nama!==null||payload.rekod[0].komponen[2].kadar!=='1234.567'||payload.rekod[0].komponen.some(c=>'tujuan'in c||'payment_grade'in c||'unit'in c))throw Error('Umum grouped source payload');
for(const width of [1440,390]){await page.setViewportSize({width,height:900});await group.locator('.anggaran-grid-scroll').evaluate(el=>el.scrollLeft=el.scrollWidth/2);await page.screenshot({path:'storage/app/ui-qa/umum-grouped-validation-'+width+'.png',fullPage:true});}await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
await page.route('**/bida-peruntukan/**/anggaran/**/jadual',route=>route.fulfill({status:409,contentType:'application/json',body:'{}'}));await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForFunction(()=>document.querySelector('[data-pakej-error]').textContent.includes('telah berubah'));await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
await page.route('**/bida-peruntukan/**/anggaran/**/jadual',route=>route.abort());await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForFunction(()=>document.querySelector('[data-pakej-error]').textContent.includes('Sambungan gagal'));await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
let release;await page.route('**/bida-peruntukan/**/anggaran/**/jadual',route=>new Promise(resolve=>{release=()=>route.fulfill({status:503,contentType:'application/json',body:'{}'}).then(resolve);}));await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForFunction(()=>document.querySelector('[data-pakej-grid]').hasAttribute('data-submitting'));if(!await first.locator('[data-field=bil_unit]').isDisabled())throw Error('Umum pending edits not locked');for(const width of [1440,390]){await page.setViewportSize({width,height:900});await page.screenshot({path:'storage/app/ui-qa/umum-grouped-saving-'+width+'.png',fullPage:true});}await release();await page.waitForFunction(()=>!document.querySelector('[data-pakej-grid]').hasAttribute('data-submitting'));await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
await second.locator('[data-field=kategori1]').selectOption('Bayaran Yuran Bengkel/ Kursus/ Taklimat/ Seminar');await second.locator('[data-field=kategori2]').selectOption('Bayaran Yuran');await second.locator('[data-field=nama]').fill('AHMAD');await second.locator('[data-field=jawatan]').selectOption('PEMBANTU AKAUNTAN');await second.locator('[data-field=gred]').selectOption('W1');await second.locator('[data-field=kadar]').focus();await second.locator('[data-field=kadar]').fill('50');await second.locator('[data-field=bil_unit]').focus();if(await second.locator('[data-output=payment_grade]').textContent()!=='—')throw Error('Grouped fee speaker classification leakage');
await second.locator('[data-field=kategori1]').selectOption('Keraian, Hospitaliti, Honorarium dll.');await second.locator('[data-field=kategori2]').selectOption('Hadiah Penghargaan Pesara');if(await second.locator('[data-field=nama]').count())throw Error('Umum group inactive personnel retained');
}

for(const fixture of ['meter-empty','meter-populated','meter-partial','meter-readonly']){
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:900});await page.goto('http://127.0.0.1:4173/'+fixture+'.html');await page.locator('[data-anggaran-grid]').waitFor();
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Meter page overflow '+fixture);
  for(const side of ['left','right']){
   await page.locator('.anggaran-grid-scroll').evaluate((el,side)=>el.scrollLeft=side==='left'?0:el.scrollWidth,side);
   await page.screenshot({path:'storage/app/ui-qa/'+fixture+'-'+width+'-'+side+'.png',fullPage:true});
  }
  if(fixture==='meter-readonly'&&await page.getByRole('button',{name:'Simpan Draf',exact:true}).count())throw Error('Meter readonly save');
 }
}
await page.setViewportSize({width:1440,height:900});await page.goto('http://127.0.0.1:4173/meter-empty.html');
await page.locator('[data-grid-add-count]').fill('1');await page.locator('[data-grid-add]').click();
{
 const row=page.locator('[data-grid-body] tr').first(),field=key=>row.locator('[data-field="'+key+'"]');
 await field('tempat_bertugas_id').selectOption({index:1});await field('kategori1').selectOption('Berwarna');
 await field('tarikh_mula').fill('2028-01-01');await field('tarikh_tamat').fill('2028-12-31');
 await field('tarikh_mula_kontrak').fill('2026-02-01');await field('tarikh_tamat_kontrak').fill('2030-01-31');
 await field('bil_unit').fill('12000');await field('kadar').focus();await field('kadar').fill('0.03');await field('bil_bulan').fill('12');
 if(await row.locator('[data-output=tempoh]').textContent()!=='1 tahun, 0 bulan, 0 hari'||await row.locator('[data-output=tempoh_kontrak]').textContent()!=='4 tahun, 0 bulan, 0 hari')throw Error('Meter contract duration mismatch');
 if(await row.locator('[data-output=preview]').textContent()!=='4,320.00')throw Error('Meter monthly copy formula');
 for(const [start,end,expected] of [
 ['2028-02-29','2028-02-29','0 tahun, 0 bulan, 1 hari'],
 ['2028-02-01','2028-02-29','0 tahun, 1 bulan, 0 hari'],
 ['2028-01-31','2028-02-28','0 tahun, 1 bulan, 0 hari'],
 ['2028-01-31','2028-02-29','0 tahun, 1 bulan, 1 hari'],
 ['2028-02-29','2029-02-28','1 tahun, 0 bulan, 1 hari'],
 ['9999-12-31','9999-12-31','0 tahun, 0 bulan, 1 hari']
 ]){
  await field('tarikh_mula_kontrak').fill(start);await field('tarikh_tamat_kontrak').fill(end);
  if(await row.locator('[data-output=tempoh_kontrak]').textContent()!==expected)throw Error('Meter calendar boundary '+start+' / '+end);
  if(await field('bil_bulan').inputValue()!=='12'||await row.locator('[data-output=preview]').textContent()!=='4,320.00')throw Error('Meter contract auto-filled chargeable months');
 }
 await field('tarikh_mula_kontrak').fill('2026-02-01');await field('tarikh_tamat_kontrak').fill('2030-01-31');
 await field('kategori1').selectOption('Hitam dan Putih');if(await field('kadar').inputValue()!=='0.03')throw Error('Meter user rate lost across type change');
 await field('kadar').focus();await field('kadar').fill('0.012345');await field('bil_unit').fill('12345.6');await field('bil_bulan').fill('7');
 if(await row.locator('[data-output=preview]').getAttribute('data-exact')!=='1066.845024'||await row.locator('[data-output=preview]').textContent()!=='1,066.85')throw Error('Meter exact precision');
 await field('kadar').focus();if(await field('kadar').inputValue()!=='0.012345')throw Error('Meter exact edit value lost');await field('lokasi').fill('Bilik Cetak');
 await row.locator('[data-row-select]').check();await page.locator('[data-grid-copy]').click();
 if(await page.locator('[data-grid-body] tr').count()!==2)throw Error('Meter copy failed');
 const second=page.locator('[data-grid-body] tr').nth(1);await second.locator('[data-field=bil_bulan]').fill('5');
 if(await field('bil_bulan').inputValue()!=='7')throw Error('Meter copied row not independent');
 await second.locator('[data-row-select]').check();await page.locator('[data-grid-remove]').click();
 let payload;
 await page.route('**/bida-peruntukan/**/anggaran/**/jadual',async route=>{payload=route.request().postDataJSON();await route.fulfill({status:422,contentType:'application/json',body:JSON.stringify({errors:{'rekod.0.kadar':['Semak kadar sesalinan.']}})});});
 await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForFunction(()=>document.querySelector('[data-grid-error]').textContent.includes('Semak kadar'));
 if(payload.rekod[0].kadar!=='0.012345'||payload.rekod[0].bil_bulan!=='7'||'tempoh' in payload.rekod[0]||'tempoh_kontrak' in payload.rekod[0]||'preview' in payload.rekod[0])throw Error('Meter source payload drift');
 if(await field('kadar').getAttribute('aria-invalid')!=='true')throw Error('Meter nested field error missing');
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:900});await page.evaluate(()=>window.scrollTo(0,0));await page.locator('.anggaran-grid-scroll').evaluate(el=>el.scrollLeft=el.scrollWidth);
  await page.screenshot({path:'storage/app/ui-qa/meter-validation-'+width+'.png',fullPage:true});
 }
 await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
 await page.route('**/bida-peruntukan/**/anggaran/**/jadual',route=>route.fulfill({status:409,contentType:'application/json',body:'{}'}));
 await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForFunction(()=>document.querySelector('[data-grid-error]').textContent.includes('telah berubah'));
 if(await field('kadar').inputValue()!=='0.01')throw Error('Meter retry lost formatted input');
 await field('kadar').focus();if(await field('kadar').inputValue()!=='0.012345')throw Error('Meter retry lost exact input');
 await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
 await field('kadar').fill('');await field('bil_bulan').focus();if(await row.locator('[data-output=preview]').textContent()!=='—')throw Error('Meter missing rate assumed');
}


for(const fixture of ['rental-empty','rental-populated','rental-partial','rental-readonly']){
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:900});await page.goto('http://127.0.0.1:4173/'+fixture+'.html');await page.locator('[data-anggaran-grid]').waitFor();
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Rental page overflow '+fixture);
  for(const side of ['left','right']){
   await page.locator('.anggaran-grid-scroll').evaluate((el,side)=>el.scrollLeft=side==='left'?0:el.scrollWidth,side);
   await page.screenshot({path:'storage/app/ui-qa/'+fixture+'-'+width+'-'+side+'.png',fullPage:true});
  }
  if(fixture==='rental-readonly'&&await page.getByRole('button',{name:'Simpan Draf',exact:true}).count())throw Error('Rental readonly save');
 }
}
await page.setViewportSize({width:1440,height:900});await page.goto('http://127.0.0.1:4173/rental-empty.html');
await page.locator('[data-grid-add-count]').fill('1');await page.locator('[data-grid-add]').click();
{
 const row=page.locator('[data-grid-body] tr').first(),field=key=>row.locator('[data-field="'+key+'"]');
 await field('tempat_bertugas_id').selectOption({index:1});await field('tujuan').fill('Sewaan Dewan Mesyuarat');await field('kategori1').selectOption('Berkontrak');
 await field('kategori2').selectOption('Pelbagai');await field('kategori3').selectOption('Dewan (Venue)');
 await field('tarikh_mula').fill('2028-02-28');await field('tarikh_tamat').fill('2028-03-01');
 await field('bil_unit').fill('2');await field('kadar').focus();await field('kadar').fill('180');await field('bil_bulan').fill('12');
 if(await row.locator('[data-output=preview]').textContent()!=='4,320.00'||await row.locator('[data-output=tempoh]').textContent()!=='3')throw Error('Rental formula or leap duration');
 await field('tarikh_mula').fill('2028-01-01');await field('tarikh_tamat').fill('2028-12-31');
 if(await row.locator('[data-output=tempoh]').textContent()!=='366'||await field('bil_bulan').inputValue()!=='12'||await row.locator('[data-output=preview]').textContent()!=='4,320.00')throw Error('Rental duration sets chargeable months');
 await field('kategori2').selectOption('Kenderaan');
 if(await field('kategori3').inputValue()!==''||await row.locator('[data-output=preview]').textContent()!=='—')throw Error('Rental stale third category after equipment change');
 await field('kategori3').selectOption('Van');
 await field('kategori1').selectOption('Tidak Berkontrak');
 if(await field('kategori2').inputValue()!==''||await field('kategori3').inputValue()!==''||await field('tarikh_mula').count()||await field('tarikh_tamat').count())throw Error('Rental arrangement fails to clear descendants or dates');
 if(await row.locator('[data-output=tempoh]').textContent()!=='Tidak berkenaan')throw Error('Rental inactive duration shown');
 await field('kategori2').selectOption('Tanah');if(await field('kategori3').count())throw Error('Rental invented land type');
 if(await row.locator('[data-output=preview]').textContent()!=='4,320.00')throw Error('Rental blank land leaf not resolved');
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:900});await page.evaluate(()=>window.scrollTo(0,0));
  await row.locator('[data-output=tempoh]').evaluate(el=>el.scrollIntoView({block:'nearest',inline:'center'}));
  await page.screenshot({path:'storage/app/ui-qa/rental-noncontract-'+width+'.png',fullPage:true});
 }
 await field('kategori2').selectOption('Alat Kelengkapan Penyiaran');await field('kategori3').selectOption('Alat Kelengkapan Penyiaran');
 await field('kategori1').selectOption('Berkontrak');
 const choices=await field('kategori2').locator('option').allTextContents();
 if(!choices.includes('Sewa Alat Kelengkapan Penyiaran')||choices.includes('Alat Kelengkapan Penyiaran'))throw Error('Rental broadcast labels merged');
 await field('kategori2').selectOption('Sewa Alat Kelengkapan Penyiaran');await field('kategori3').selectOption('Alat Kelengkapan Penyiaran');
 if(await field('tarikh_mula').inputValue()!==''||await field('tarikh_tamat').inputValue()!=='')throw Error('Rental inactive dates resurrected');
 await field('kadar').focus();await field('kadar').fill('1234.567');await field('bil_bulan').fill('3');
 if(await row.locator('[data-output=preview]').getAttribute('data-exact')!=='7407.402'||await row.locator('[data-output=preview]').textContent()!=='7,407.40')throw Error('Rental exact precision');
 await field('kadar').focus();if(await field('kadar').inputValue()!=='1234.567')throw Error('Rental exact edit lost');await field('bil_unit').focus();
 await row.locator('[data-row-select]').check();await page.locator('[data-grid-copy]').click();
 if(await page.locator('[data-grid-body] tr').count()!==2)throw Error('Rental row copy');
 const copy=page.locator('[data-grid-body] tr').nth(1);await copy.locator('[data-field=bil_bulan]').fill('5');if(await field('bil_bulan').inputValue()!=='3')throw Error('Rental copy shared values');
 await copy.locator('[data-row-select]').check();await page.locator('[data-grid-remove]').click();
 let payload;
 await page.route('**/bida-peruntukan/**/anggaran/**/jadual',async route=>{payload=route.request().postDataJSON();await route.fulfill({status:422,contentType:'application/json',body:JSON.stringify({errors:{'rekod.0.kategori3':['Semak Jenis Peralatan.']}})});});
 await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForFunction(()=>document.querySelector('[data-grid-error]').textContent.includes('Semak Jenis'));
 if(payload.rekod[0].kadar!=='1234.567'||payload.rekod[0].bil_bulan!=='3'||'tempoh' in payload.rekod[0]||'preview' in payload.rekod[0])throw Error('Rental source payload drift');
 if(await field('kategori3').getAttribute('aria-invalid')!=='true')throw Error('Rental category error missing');
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:900});await page.evaluate(()=>window.scrollTo(0,0));await field('kategori3').evaluate(el=>el.scrollIntoView({block:'nearest',inline:'center'}));
  await page.screenshot({path:'storage/app/ui-qa/rental-validation-'+width+'.png',fullPage:true});
 }
 await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
 await page.route('**/bida-peruntukan/**/anggaran/**/jadual',route=>route.fulfill({status:409,contentType:'application/json',body:'{}'}));
 await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForFunction(()=>document.querySelector('[data-grid-error]').textContent.includes('telah berubah'));
 await field('kadar').focus();if(await field('kadar').inputValue()!=='1234.567')throw Error('Rental retry lost exact source');
 await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
 await field('bil_bulan').focus();
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:900});await row.locator('[data-output=preview]').evaluate(el=>el.scrollIntoView({block:'nearest',inline:'center'}));
  const detail=row.locator('[data-output=preview]').locator('..').locator('details');await detail.evaluate(el=>el.open=true);
  await page.screenshot({path:'storage/app/ui-qa/rental-exact-'+width+'.png',fullPage:true});
 }
 await field('kadar').focus();await field('kadar').fill('');await field('bil_bulan').focus();if(await row.locator('[data-output=preview]').textContent()!=='—')throw Error('Rental missing manual rate assumed');
}


for(const fixture of ['fotostat-empty','fotostat-populated','fotostat-linked-meter']){
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:900});await page.goto('http://127.0.0.1:4173/'+fixture+'.html');await page.locator('[data-anggaran-grid]').waitFor();
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Fotostat overflow '+fixture);
  for(const side of ['left','right']){await page.locator('.anggaran-grid-scroll').evaluate((el,side)=>el.scrollLeft=side==='left'?0:el.scrollWidth,side);await page.screenshot({path:'storage/app/ui-qa/'+fixture+'-'+width+'-'+side+'.png',fullPage:true});}
 }
}
for(const fixture of ['fotostat-activation','fotostat-links-empty','fotostat-deactivate','fotostat-link-review']){
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:900});await page.goto('http://127.0.0.1:4173/'+fixture+'.html');
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Fotostat review overflow '+fixture);
  await page.screenshot({path:'storage/app/ui-qa/'+fixture+'-'+width+'.png',fullPage:true});
 }
}
await page.setViewportSize({width:1440,height:900});await page.goto('http://127.0.0.1:4173/fotostat-linked-meter.html');
{
 const row=page.locator('[data-grid-body] tr').first();
 for(const key of ['tempat_bertugas_id','kategori1','lokasi','tarikh_mula','tarikh_tamat','tarikh_mula_kontrak','tarikh_tamat_kontrak'])if(await row.locator('[data-field='+key+']').count())throw Error('Linked meter shared input editable '+key);
 if(await row.locator('[data-row-select]').count())throw Error('Linked meter independently selectable');
 if(await row.locator('[data-output=preview]').textContent()!=='4,320.00')throw Error('Linked meter financial preview');
 await row.locator('[data-field=kadar]').focus();await row.locator('[data-field=kadar]').fill('0.012345');await row.locator('[data-field=bil_bulan]').fill('7');
 if(await row.locator('[data-output=preview]').getAttribute('data-exact')!=='1036.98')throw Error('Linked meter exact rate independent');
}
await page.goto('http://127.0.0.1:4173/fotostat-populated.html');
{
 const row=page.locator('[data-grid-body] tr').first();await row.locator('[data-row-select]').check();await page.locator('[data-grid-remove]').click();
 let message='',payload;page.once('dialog',async d=>{message=d.message();await d.accept();});
 await page.route('**/bida-peruntukan/**/anggaran/**/jadual',async route=>{payload=route.request().postDataJSON();await route.fulfill({status:409,contentType:'application/json',body:'{}'});});
 await page.getByRole('button',{name:'Simpan Draf',exact:true}).click();await page.waitForFunction(()=>document.querySelector('[data-grid-error]').textContent.includes('telah berubah'));
 if(!message.includes('Bacaan Meter #')||!message.includes('12000')||!message.includes('0.03'))throw Error('Paired deletion does not disclose meter values');
 if(!payload.padam[0].sahkan_meter||!Number.isInteger(payload.padam[0].semakan_meter))throw Error('Paired deletion revision missing');
 await page.unroute('**/bida-peruntukan/**/anggaran/**/jadual');
}

await browser.close();
