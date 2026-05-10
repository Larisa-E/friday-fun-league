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
                import('./stats-chart-runtime'),
            ]);

            initStatsCharts(Chart);
        } catch (error) {
            console.error('Could not lazy load the statistics charts.', error);
        }
    };

    const scheduleChartLoad = () => {
        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(() => {
                void loadCharts();
            }, { timeout: 1200 });

            return;
        }

        window.setTimeout(() => {
            void loadCharts();
        }, 0);
    };

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                observer.disconnect();
                scheduleChartLoad();
            }
        }, { rootMargin: '120px 0px' });

        observer.observe(chartAnchor);
    } else {
        scheduleChartLoad();
    }
}