import './bootstrap';
import * as bootstrap from 'bootstrap';
import Chart from 'chart.js/auto';

window.bootstrap = bootstrap;
window.Chart = Chart;

document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('appSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');

    const closeSidebar = () => {
        sidebar?.classList.remove('show');
        backdrop?.classList.remove('show');
    };

    toggle?.addEventListener('click', () => {
        sidebar?.classList.toggle('show');
        backdrop?.classList.toggle('show');
    });

    backdrop?.addEventListener('click', closeSidebar);

    // Password visibility toggles
    document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = document.querySelector(btn.getAttribute('data-toggle-password'));
            if (!input) return;
            const isPassword = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPassword ? 'text' : 'password');
            btn.innerHTML = isPassword
                ? '<i class="bi bi-eye-slash"></i>'
                : '<i class="bi bi-eye"></i>';
        });
    });

    // Demo credential role picker on login
    document.querySelectorAll('[data-demo-role]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const email = btn.getAttribute('data-email');
            const password = btn.getAttribute('data-password');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            if (emailInput) emailInput.value = email || '';
            if (passwordInput) passwordInput.value = password || '';
            document.querySelectorAll('[data-demo-role]').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    // Chart helpers from data attributes
    document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
        try {
            const config = JSON.parse(canvas.getAttribute('data-chart'));

            const todayLabel = config?.options?.scales?.x?.ticks?.todayLabel;
            if (typeof todayLabel === 'string') {
                config.plugins = config.plugins || [];
                config.plugins.push({
                    id: 'todayTickColor',
                    beforeInit(chart) {
                        const xScale = chart.options?.scales?.x;
                        if (!xScale?.ticks) {
                            return;
                        }

                        xScale.ticks.color = (context) => {
                            const index = typeof context.index === 'number'
                                ? context.index
                                : context?.tick?.index;

                            let label = '';
                            if (typeof index === 'number' && Array.isArray(chart.data.labels)) {
                                label = String(chart.data.labels[index] ?? '');
                            } else {
                                label = String(context?.tick?.label ?? context?.label ?? context?.value ?? '');
                            }

                            return label === todayLabel ? '#2563eb' : '#6b7280';
                        };
                    },
                });
            }

            new Chart(canvas, config);
        } catch (e) {
            console.error('Chart parse error', e);
        }
    });

    // Session inactivity warning (30 min)
    const warnAt = 29 * 60 * 1000;
    const logoutAt = 30 * 60 * 1000;
    const logoutUrl = document.body.dataset.logoutUrl;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    if (logoutUrl && document.body.dataset.auth === '1') {
        setTimeout(() => {
            const modalEl = document.getElementById('sessionWarnModal');
            if (modalEl && window.bootstrap) {
                window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        }, warnAt);

        setTimeout(() => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = logoutUrl;
            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = csrf || '';
            form.appendChild(token);
            document.body.appendChild(form);
            form.submit();
        }, logoutAt);
    }
});
