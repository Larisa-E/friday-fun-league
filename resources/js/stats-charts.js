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

    const pointColors = ['#0b5ed7', '#3699ff', '#63b4ff', '#8fcbff', '#b8ddff', '#dcecff'];
    const gameColors = ['#0b5ed7', '#0056b3', '#2f80ed', '#6ca8ff', '#8cc4ff', '#b8dbff'];

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
                        borderRadius: 4,
                        borderSkipped: false,
                        maxBarThickness: 34,
                    },
                    {
                        label: 'Wins',
                        data: participantWins,
                        backgroundColor: '#0a3870',
                        borderRadius: 4,
                        borderSkipped: false,
                        maxBarThickness: 34,
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
                            color: '#4f6478',
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 64, 128, 0.08)',
                        },
                        ticks: {
                            precision: 0,
                            color: '#617384',
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: '#617384',
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
                        spacing: 3,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            [legendBoxWidthKey]: 10,
                            padding: 14,
                            color: '#4f6478',
                        },
                    },
                },
            },
        });
    }
};