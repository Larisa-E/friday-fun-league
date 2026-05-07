// Build the statistics charts after the local Chart.js module is ready.
export const initStatsCharts = (ChartCtor) => {
    const statsNode = document.getElementById('stats-data');
    const legendBoxWidthKey = 'box' + 'Width';

    if (!statsNode || !ChartCtor || statsNode.dataset.chartsInitialized === 'true') {
        return;
    }

    statsNode.dataset.chartsInitialized = 'true';

    const payload = JSON.parse(statsNode.dataset.payload ?? '{}');
    const {
        participantLabels = [],
        participantPoints = [],
        participantWins = [],
        gameLabels = [],
        gameTotals = [],
    } = payload;

    const pointColors = ['#0f63c8', '#2580ef', '#4b9eef', '#74b6f1', '#9bcdf1', '#cfe4f8'];
    const gameColors = ['#0c5fbf', '#104b86', '#2276ca', '#5a98d6', '#8eb8dd', '#d3e4f3'];

    const pointsCanvas = document.getElementById('pointsChart');
    const gameTypeCanvas = document.getElementById('gameTypeChart');

    if (pointsCanvas) {
        new ChartCtor(pointsCanvas, {
            type: 'bar',
            data: {
                labels: participantLabels,
                datasets: [
                    {
                        label: 'Points',
                        data: participantPoints,
                        backgroundColor: participantPoints.map((_, index) => pointColors[index % pointColors.length]),
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 32,
                    },
                    {
                        label: 'Wins',
                        data: participantWins,
                        backgroundColor: '#143f72',
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 32,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            [legendBoxWidthKey]: 10,
                            color: '#48627c',
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(20, 63, 114, 0.09)',
                        },
                        ticks: {
                            precision: 0,
                            color: '#5f7387',
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: '#5f7387',
                        },
                    },
                },
            },
        });
    }

    if (gameTypeCanvas) {
        new ChartCtor(gameTypeCanvas, {
            type: 'doughnut',
            data: {
                labels: gameLabels,
                datasets: [
                    {
                        data: gameTotals,
                        backgroundColor: gameTotals.map((_, index) => gameColors[index % gameColors.length]),
                        ['border' + 'Width']: 0,
                        spacing: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            [legendBoxWidthKey]: 10,
                            padding: 14,
                            color: '#48627c',
                        },
                    },
                },
            },
        });
    }
};