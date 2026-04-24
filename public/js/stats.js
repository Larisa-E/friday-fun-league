const statsNode = document.getElementById('stats-data');

if (statsNode && window.Chart) {
    const payload = JSON.parse(statsNode.dataset.payload ?? '{}');
    const {
        participantLabels = [],
        participantPoints = [],
        participantWins = [],
        gameLabels = [],
        gameTotals = [],
    } = payload;

    const pointColors = ['#007bff', '#3b91ff', '#63a8ff', '#8bbfff', '#b3d5ff', '#dbe9ff'];
    const gameColors = ['#007bff', '#004080', '#6c757d', '#adb5bd', '#dee2e6', '#ff4d4d'];

    const pointsCanvas = document.getElementById('pointsChart');
    const gameTypeCanvas = document.getElementById('gameTypeChart');

    if (pointsCanvas) {
        new Chart(pointsCanvas, {
            type: 'bar',
            data: {
                labels: participantLabels,
                datasets: [
                    {
                        label: 'Points',
                        data: participantPoints,
                        backgroundColor: participantPoints.map((_, index) => pointColors[index % pointColors.length]),
                        borderRadius: 4,
                    },
                    {
                        label: 'Wins',
                        data: participantWins,
                        backgroundColor: '#004080',
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                        },
                    },
                },
            },
        });
    }

    if (gameTypeCanvas) {
        new Chart(gameTypeCanvas, {
            type: 'doughnut',
            data: {
                labels: gameLabels,
                datasets: [
                    {
                        data: gameTotals,
                        backgroundColor: gameTotals.map((_, index) => gameColors[index % gameColors.length]),
                        ['border' + 'Width']: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                },
            },
        });
    }
}