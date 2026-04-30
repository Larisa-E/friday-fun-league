import Alert from 'bootstrap/js/dist/alert';
import Modal from 'bootstrap/js/dist/modal';
import Tab from 'bootstrap/js/dist/tab';
import Toast from 'bootstrap/js/dist/toast';

window.bootstrap = { Alert, Modal, Tab, Toast };

// Only load the dashboard code on the dashboard page.
if (document.getElementById('dashboard-state')) {
    void import('./dashboard');
}

// Only load the statistics code on the statistics page.
if (document.getElementById('stats-data')) {
    void import('./stats-page');
}
