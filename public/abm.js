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
    const deadline = Number(document.body.dataset.deadline) * 1000;
    if (deadline > 0) {
        const warning = document.getElementById('session-warning');
        setTimeout(() => { if (warning && !warning.open) warning.showModal(); }, Math.max(0, deadline - Date.now() - 300000));
        setTimeout(() => { window.location.assign('/login'); }, Math.max(0, deadline - Date.now()));
    }
})();
