import { Chart, LineController, LineElement, PointElement, BarController, BarElement, DoughnutController, ArcElement, CategoryScale, LinearScale, Filler, Tooltip, Legend } from 'chart.js';

Chart.register(LineController, LineElement, PointElement, BarController, BarElement, DoughnutController, ArcElement, CategoryScale, LinearScale, Filler, Tooltip, Legend);

Chart.defaults.font.family = '"Plus Jakarta Sans", sans-serif';
Chart.defaults.color = '#64748b';

window.renderAdminCharts = ({ votesByDay, ranking, pledgesByType }) => {
    const votes = document.getElementById('chart-votes');
    if (votes) {
        const ctx = votes.getContext('2d');
        const grad = ctx.createLinearGradient(0, 0, 0, 260);
        grad.addColorStop(0, 'rgba(245,183,10,.35)');
        grad.addColorStop(1, 'rgba(245,183,10,0)');
        new Chart(votes, {
            type: 'line',
            data: {
                labels: Object.keys(votesByDay),
                datasets: [{ label: 'Votos', data: Object.values(votesByDay), borderColor: '#d98f02', backgroundColor: grad, fill: true, tension: 0.35, pointRadius: 3, pointBackgroundColor: '#d98f02' }],
            },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } },
        });
    }

    const top = document.getElementById('chart-ranking');
    if (top) {
        new Chart(top, {
            type: 'bar',
            data: {
                labels: ranking.map((r) => r.name),
                datasets: [{ label: 'Votos', data: ranking.map((r) => r.votes), backgroundColor: '#232a55', borderRadius: 8, barThickness: 18 }],
            },
            options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, y: { grid: { display: false } } } },
        });
    }

    const types = document.getElementById('chart-pledges');
    if (types) {
        new Chart(types, {
            type: 'doughnut',
            data: {
                labels: Object.keys(pledgesByType),
                datasets: [{ data: Object.values(pledgesByType), backgroundColor: ['#232a55', '#f5b70a', '#8b5cf6', '#06b6d4', '#ec4899'], borderWidth: 0 }],
            },
            options: { maintainAspectRatio: false, cutout: '68%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } } },
        });
    }
};
