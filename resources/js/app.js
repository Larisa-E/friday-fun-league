const bootstrapLoaders = {
    Alert: () => import('bootstrap/js/dist/alert'),
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

if (document.querySelector('.alert-dismissible')) {
    void ensureBootstrap(['Alert']);
}

// Only load the dashboard code on the dashboard page.
if (document.getElementById('dashboard-state')) {
    void ensureBootstrap(['Alert', 'Modal', 'Tab', 'Toast']).then(() => import('./dashboard'));
}

// Only load the statistics code on the statistics page.
if (document.getElementById('stats-data')) {
    void import('./stats-page');
}
