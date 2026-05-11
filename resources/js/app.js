const bootstrapLoaders = {
    Modal: () => import('bootstrap/js/dist/modal'),
    Tab: () => import('bootstrap/js/dist/tab'),
    Toast: () => import('bootstrap/js/dist/toast'),
};

const bootstrapModules = {};

const ensureBootstrap = async (moduleNames) => {
    const missingModules = moduleNames.filter((moduleName) => !bootstrapModules[moduleName]);

    if (missingModules.length) {
        const loadedModules = await Promise.all(missingModules.map(async (moduleName) => {
            const module = await bootstrapLoaders[moduleName]();

            return [moduleName, module.default];
        }));

        loadedModules.forEach(([moduleName, moduleValue]) => {
            bootstrapModules[moduleName] = moduleValue;
        });
    }

    window.bootstrap = bootstrapModules;

    return bootstrapModules;
};

window.ensureBootstrapModules = ensureBootstrap;

document.addEventListener('click', (event) => {
    const dismissButton = event.target.closest('[data-app-dismiss="alert"]');

    if (!dismissButton) {
        return;
    }

    const alertElement = dismissButton.closest('.alert');

    if (!alertElement) {
        return;
    }

    alertElement.classList.remove('show');

    window.setTimeout(() => {
        alertElement.remove();
    }, 150);
});

// Only load the dashboard code on the dashboard page.
if (document.getElementById('dashboard-state')) {
    void ensureBootstrap(['Tab', 'Toast']).then(() => import('./dashboard'));
}

// Only load the statistics code on the statistics page.
if (document.getElementById('stats-data')) {
    void import('./stats-page');
}
