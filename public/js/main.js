/**
 * main.js - JavaScript principale ESG-BALANCE Platform
 */

'use strict';

// Conferme automatiche per form pericolosi
document.addEventListener('DOMContentLoaded', () => {

    // Auto-chiudi messaggi di feedback dopo 4 secondi
    const msgs = document.querySelectorAll('.msg, .msg-error');
    msgs.forEach(msg => {
        setTimeout(() => {
            msg.style.transition = 'opacity .5s';
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }, 4000);
    });

    // Evidenzia riga tabella al click
    document.querySelectorAll('.data-table tbody tr').forEach(row => {
        row.addEventListener('click', () => {
            document.querySelectorAll('.data-table tbody tr').forEach(r => r.classList.remove('selected'));
            row.classList.toggle('selected');
        });
    });

    // Toggle campi indicatori ESG
    const toggleBtn = document.getElementById('toggle-indicatori');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const groups = document.querySelectorAll('.indicatori-group');
            groups.forEach(g => g.classList.toggle('collapsed'));
        });
    }

    // Conferma per azioni irreversibili
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            if (!confirm(el.dataset.confirm)) e.preventDefault();
        });
    });

    // Contatore caratteri per textarea
    document.querySelectorAll('textarea[maxlength]').forEach(ta => {
        const counter = document.createElement('small');
        counter.className = 'char-counter';
        ta.parentNode.insertBefore(counter, ta.nextSibling);
        const update = () => {
            const remaining = ta.maxLength - ta.value.length;
            counter.textContent = `${remaining} caratteri rimanenti`;
            counter.style.color = remaining < 50 ? '#e74c3c' : '#7f8c8d';
        };
        ta.addEventListener('input', update);
        update();
    });

    // Funzione toggleCampiSpec per indicatori (compatibilita')
    window.toggleCampiSpec = function(id) {
        const el = document.getElementById('spec_' + id);
        if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
    };
});

// Utility: mostra/nascondi loader
function showLoader() {
    const loader = document.getElementById('loader');
    if (loader) loader.style.display = 'flex';
}
function hideLoader() {
    const loader = document.getElementById('loader');
    if (loader) loader.style.display = 'none';
}

// Intercetta form submission con loader
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', () => showLoader());
});
