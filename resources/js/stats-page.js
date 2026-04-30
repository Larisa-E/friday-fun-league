import { initStatsCharts } from './stats-charts';

// Keep chart loading lazy, but load Chart.js from the local Vite bundle instead of a CDN.
const statsNode = document.getElementById('stats-data');
const chartAnchor = document.getElementById('pointsChart');

if (statsNode && chartAnchor) {
    let chartsRequested = false;

    const loadCharts = async () => {
        if (chartsRequested) {
            return;
        }

        chartsRequested = true;

        try {
            const [{ default: Chart }] = await Promise.all([
                import('chart.js/auto'),
            ]);

            initStatsCharts(Chart);
        } catch (error) {
            console.error('Could not lazy load the statistics charts.', error);
        }
    };

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                observer.disconnect();
                void loadCharts();
            }
        }, { rootMargin: '120px 0px' });

        observer.observe(chartAnchor);
    } else {
        void loadCharts();
    }
}