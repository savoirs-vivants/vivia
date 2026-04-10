document.addEventListener('DOMContentLoaded', function() {

    // On configure la couleur par défaut pour tous les graphiques Chart.js s'il est chargé
    if (typeof Chart !== 'undefined') {
        Chart.defaults.color = '#9ca3af';
    }

    // =========================================================
    // 1. LOGIQUE DE LA PAGE "STATISTIQUES GLOBALES"
    // =========================================================
    if (typeof window.statistiquesData !== 'undefined' && typeof Chart !== 'undefined') {
        const data = window.statistiquesData;

        // Graphique des Âges
        const ctxAge = document.getElementById('ageChart');
        if (ctxAge) {
            new Chart(ctxAge.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.ageData.labels,
                    datasets: [
                        { label: 'Filles / Femmes', data: data.ageData.filles, backgroundColor: '#16987C', borderRadius: 4, barPercentage: 0.6, categoryPercentage: 0.8 },
                        { label: 'Garçons / Hommes', data: data.ageData.garcons, backgroundColor: '#3b82f6', borderRadius: 4, barPercentage: 0.6, categoryPercentage: 0.8 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, font: { weight: 'bold', size: 11 } } } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f3f4f6', drawBorder: false }, border: { display: false } },
                        x: { grid: { display: false }, border: { display: false } }
                    }
                }
            });
        }

        // Graphique des Genres
        const ctxGender = document.getElementById('genderChart');
        if (ctxGender) {
            new Chart(ctxGender.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Filles / Femmes', 'Garçons / Hommes'],
                    datasets: [{ data: [data.nbFilles, data.nbGarcons], backgroundColor: ['#16987C', '#3b82f6'], borderWidth: 0, hoverOffset: 4 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { display: false } } }
            });
        }

        // Graphique d'Évolution
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
                        { label: 'Saison ' + data.saisonCourante, data: data.evolutionData.courante, borderColor: '#16987C', backgroundColor: gradientSaisonActuelle, borderWidth: 3, pointBackgroundColor: '#16987C', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 5, fill: true, tension: 0.4 },
                        { label: 'Saison ' + data.saisonPrecedente, data: data.evolutionData.precedente, borderColor: '#9ca3af', borderWidth: 2, borderDash: [5, 5], pointBackgroundColor: '#9ca3af', pointRadius: 0, fill: false, tension: 0.4 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, font: { weight: 'bold', size: 11 } } } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f3f4f6', drawBorder: false }, border: { display: false } },
                        x: { grid: { display: false }, border: { display: false } }
                    }
                }
            });
        }

        // Carte Leaflet
        const mapContainer = document.getElementById('adherentsMap');
        if (mapContainer && typeof L !== 'undefined') {
            setTimeout(function() {
                const adherentsMapInstance = L.map('adherentsMap').setView([46.603354, 1.888334], 5);
                L.tileLayer('https://api.maptiler.com/maps/base-v4/256/{z}/{x}/{y}.png?key=lpN2MwGYklm62ZAIZttO', {
                    attribution: '&copy; <a href="https://www.maptiler.com/copyright/" target="_blank">MapTiler</a> &copy; OpenStreetMap contributors',
                    maxZoom: 19, crossOrigin: true
                }).addTo(adherentsMapInstance);

                const bounds = [];
                data.mapData.forEach(point => {
                    L.circleMarker([point.lat, point.lng], { color: '#ffffff', fillColor: 'blue', fillOpacity: 0.9, radius: 8, weight: 2 }).addTo(adherentsMapInstance);
                    bounds.push([point.lat, point.lng]);
                });
                if (bounds.length > 0) adherentsMapInstance.fitBounds(bounds, { padding: [30, 30] });
            }, 100);
        }
    }

    // =========================================================
    // 2. LOGIQUE DE LA PAGE "DASHBOARD"
    // =========================================================
    if (typeof window.dashboardGraphData !== 'undefined' && typeof Chart !== 'undefined') {
        const dData = window.dashboardGraphData;

        const SV_DARK = '#083325';
        const SV_GREEN = '#16A37A';
        const SV_LIGHT = '#34D399';
        const SV_MUTED = '#A7F3D0';

        const modernTooltip = {
            backgroundColor: '#ffffff', titleColor: '#111827', bodyColor: '#6B7280', borderColor: '#F3F4F6',
            borderWidth: 1, padding: 12, boxPadding: 6, usePointStyle: true,
            titleFont: { family: 'Space Grotesk, sans-serif', size: 13, weight: 'bold' },
            bodyFont: { family: 'sans-serif', size: 12, weight: '500' },
            boxWidth: 8, boxHeight: 8, cornerRadius: 12,
        };

        const ctxTypes = document.getElementById('chartTypes');
        if (ctxTypes) {
            new Chart(ctxTypes, {
                type: 'doughnut',
                data: {
                    labels: dData.typeLabels,
                    datasets: [{ data: dData.typeData, backgroundColor: [SV_GREEN, SV_DARK, SV_LIGHT, SV_MUTED], borderWidth: 4, borderColor: '#ffffff', hoverOffset: 6 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '72%',
                    layout: { padding: { top: 8, bottom: 8 } },
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16, font: { family: 'Space Grotesk, sans-serif', size: 11, weight: 'bold' }, color: '#4B5563' } },
                        tooltip: modernTooltip,
                    }
                }
            });
        }

        const ctxAct = document.getElementById('chartActivites');
        if (ctxAct) {
            new Chart(ctxAct, {
                type: 'bar',
                data: {
                    labels: dData.actLabels,
                    datasets: [{ label: 'Inscrits', data: dData.actData, backgroundColor: SV_GREEN, borderRadius: 6, barThickness: 24, borderSkipped: false }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    layout: { padding: { right: 36 } },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            ...modernTooltip,
                            callbacks: {
                                label: (ctx) => ` ${ctx.parsed.x} inscrits`,
                                afterLabel: (ctx) => dData.actHoraires[ctx.dataIndex] ? ` ${dData.actHoraires[ctx.dataIndex]}` : '',
                            }
                        }
                    },
                    scales: {
                        x: { display: false, grid: { display: false } },
                        y: {
                            grid: { display: false, drawBorder: false },
                            ticks: { color: '#111827', font: { family: 'Space Grotesk, sans-serif', size: 12, weight: 'bold' }, padding: 8, callback: function(val) { const label = this.getLabelForValue(val); return label.length > 18 ? label.slice(0, 16) + '…' : label; } }
                        }
                    },
                    animation: { duration: 1000, easing: 'easeOutQuart' }
                },
                plugins: [{
                    id: 'valuesOnBar',
                    afterDatasetsDraw(chart) {
                        const { ctx, data } = chart;
                        chart.getDatasetMeta(0).data.forEach((bar, i) => {
                            const val = data.datasets[0].data[i];
                            if (!val) return;
                            ctx.save();
                            ctx.fillStyle = '#6B7280'; ctx.font = 'bold 12px Space Grotesk, sans-serif'; ctx.textAlign = 'left'; ctx.textBaseline = 'middle';
                            ctx.fillText(val, bar.x + 8, bar.y);
                            ctx.restore();
                        });
                    }
                }]
            });
        }
    }
});
