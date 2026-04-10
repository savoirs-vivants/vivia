document.addEventListener('DOMContentLoaded', function() {
    // On vérifie si les données existent (pour ne pas déclencher d'erreurs sur d'autres pages)
    if (typeof window.statistiquesData === 'undefined') return;

    const data = window.statistiquesData;

    // Configuration globale Chart.js
    if (typeof Chart !== 'undefined') {
        Chart.defaults.color = '#9ca3af';

        // 1. Graphique des Âges
        const ctxAge = document.getElementById('ageChart');
        if (ctxAge) {
            new Chart(ctxAge.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.ageData.labels,
                    datasets: [
                        {
                            label: 'Filles / Femmes',
                            data: data.ageData.filles,
                            backgroundColor: '#16987C',
                            borderRadius: 4,
                            barPercentage: 0.6,
                            categoryPercentage: 0.8
                        },
                        {
                            label: 'Garçons / Hommes',
                            data: data.ageData.garcons,
                            backgroundColor: '#3b82f6',
                            borderRadius: 4,
                            barPercentage: 0.6,
                            categoryPercentage: 0.8
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                font: { weight: 'bold', size: 11 }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f3f4f6', drawBorder: false },
                            border: { display: false }
                        },
                        x: {
                            grid: { display: false },
                            border: { display: false }
                        }
                    }
                }
            });
        }

        // 2. Graphique des Genres
        const ctxGender = document.getElementById('genderChart');
        if (ctxGender) {
            new Chart(ctxGender.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Filles / Femmes', 'Garçons / Hommes'],
                    datasets: [{
                        data: [data.nbFilles, data.nbGarcons],
                        backgroundColor: ['#16987C', '#3b82f6'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        // 3. Graphique d'Évolution
        const ctxEvol = document.getElementById('evolutionChart');
        if (ctxEvol) {
            const contextEvol = ctxEvol.getContext('2d');
            let gradientSaisonActuelle = contextEvol.createLinearGradient(0, 0, 0, 300);
            gradientSaisonActuelle.addColorStop(0, 'rgba(22, 152, 124, 0.2)');
            gradientSaisonActuelle.addColorStop(1, 'rgba(22, 152, 124, 0)');

            new Chart(contextEvol, {
                type: 'line',
                data: {
                    labels: data.evolutionData.labels,
                    datasets: [
                        {
                            label: 'Saison ' + data.saisonCourante,
                            data: data.evolutionData.courante,
                            borderColor: '#16987C',
                            backgroundColor: gradientSaisonActuelle,
                            borderWidth: 3,
                            pointBackgroundColor: '#16987C',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Saison ' + data.saisonPrecedente,
                            data: data.evolutionData.precedente,
                            borderColor: '#9ca3af',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            pointBackgroundColor: '#9ca3af',
                            pointRadius: 0,
                            fill: false,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                font: { weight: 'bold', size: 11 }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f3f4f6', drawBorder: false },
                            border: { display: false }
                        },
                        x: {
                            grid: { display: false },
                            border: { display: false }
                        }
                    }
                }
            });
        }
    }

    // 4. Carte Leaflet des Adhérents
    const mapContainer = document.getElementById('adherentsMap');
    if (mapContainer && typeof L !== 'undefined') {
        setTimeout(function() {
            const adherentsMapInstance = L.map('adherentsMap').setView([46.603354, 1.888334], 5);

            L.tileLayer('https://api.maptiler.com/maps/base-v4/256/{z}/{x}/{y}.png?key=lpN2MwGYklm62ZAIZttO', {
                attribution: '&copy; <a href="https://www.maptiler.com/copyright/" target="_blank">MapTiler</a> &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap contributors</a>',
                maxZoom: 19,
                crossOrigin: true
            }).addTo(adherentsMapInstance);

            const bounds = [];

            data.mapData.forEach(point => {
                L.circleMarker([point.lat, point.lng], {
                    color: '#ffffff',
                    fillColor: 'blue',
                    fillOpacity: 0.9,
                    radius: 8,
                    weight: 2
                }).addTo(adherentsMapInstance);

                bounds.push([point.lat, point.lng]);
            });

            if (bounds.length > 0) {
                adherentsMapInstance.fitBounds(bounds, {
                    padding: [30, 30]
                });
            }
        }, 100);
    }
});
