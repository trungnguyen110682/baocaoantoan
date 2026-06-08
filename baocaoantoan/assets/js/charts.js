/* An Toàn MMB — Chart Helpers (requires Chart.js + chartjs-plugin-datalabels) */

Chart.register(ChartDataLabels);

const COLORS = {
  navy:   '#1A3A63', denim: '#4A6D95', orange: '#F37021',
  green:  '#16a34a', red:   '#dc2626', yellow: '#d97706',
  blue:   '#2563eb', teal:  '#0d9488', purple: '#7c3aed',
  navyAlpha:  'rgba(26,58,99,.7)',
  orangeAlpha:'rgba(243,112,33,.7)',
};

const PALETTE = ['#1A3A63','#4A6D95','#F37021','#16a34a','#d97706','#2563eb','#0d9488','#7c3aed','#dc2626','#64748b'];

function destroyChart(id) {
  const existing = Chart.getChart(id);
  if (existing) existing.destroy();
}

// Bar chart — data label ở giữa cột
function drawBar(canvasId, labels, datasets, opts = {}) {
  destroyChart(canvasId);
  const ctx = document.getElementById(canvasId);
  if (!ctx) return;
  return new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { display: opts.legend ?? false },
        datalabels: {
          anchor: 'center', align: 'center',
          font: { size: 13, weight: 'bold' },
          color: 'white',
          formatter: v => v > 0 ? v : '',
        },
      },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
        y: { beginAtZero: true, ticks: { font: { size: 11 }, stepSize: 1 } },
      },
    },
  });
}

// Horizontal bar — tắt datalabels
function drawBarH(canvasId, labels, data, color = COLORS.navy, opts = {}) {
  destroyChart(canvasId);
  const ctx = document.getElementById(canvasId);
  if (!ctx) return;
  return new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets: [{ data, backgroundColor: color, borderRadius: 4 }] },
    options: {
      indexAxis: 'y',
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, datalabels: { display: false } },
      scales: {
        x: { beginAtZero: true, ticks: { font: { size: 11 }, stepSize: 1 } },
        y: { grid: { display: false }, ticks: { font: { size: 11 } } },
      },
      ...opts,
    },
  });
}

// Grouped bar — data label ở giữa cột
function drawBarGrouped(canvasId, labels, datasets) {
  destroyChart(canvasId);
  const ctx = document.getElementById(canvasId);
  if (!ctx) return;
  return new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets },
    options: {
      responsive: true, maintainAspectRatio: true,
      plugins: {
        legend: { display: true, position: 'top', labels: { font: { size: 11 }, boxWidth: 12 } },
        datalabels: {
          anchor: 'center', align: 'center',
          font: { size: 10, weight: 'bold' },
          color: 'white',
          formatter: v => v > 0 ? v : '',
        },
      },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
        y: { beginAtZero: true, ticks: { font: { size: 11 }, stepSize: 1 } },
      },
    },
  });
}

// Bar chart tỷ lệ KP — xanh >=90%, đỏ <90%, đường target 90%
function drawBarKP(canvasId, labels, data, opts = {}) {
  destroyChart(canvasId);
  const ctx = document.getElementById(canvasId);
  if (!ctx) return;
  const colors = data.map(v => v >= 90 ? COLORS.green : COLORS.red);
  return new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          type: 'bar',
          label: 'Tỷ lệ KP (%)',
          data,
          backgroundColor: colors,
          borderRadius: 4,
          datalabels: {
            anchor: 'center', align: 'center',
            font: { size: 11, weight: 'bold' },
            color: 'white',
            formatter: v => v > 0 ? v + '%' : '',
          },
        },
        {
          type: 'line',
          label: 'Target 90%',
          data: labels.map(() => 90),
          borderColor: '#d97706',
          borderWidth: 2,
          borderDash: [6, 4],
          pointRadius: 0,
          fill: false,
          tension: 0,
          datalabels: { display: false },
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: opts.maintainAspectRatio ?? true,
      plugins: {
        legend: { display: true, position: 'top', labels: { font: { size: 11 }, boxWidth: 12 } },
        datalabels: { display: true },
      },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
        y: {
          beginAtZero: true,
          max: 110,
          ticks: { font: { size: 11 }, callback: v => v + '%' },
        },
      },
    },
  });
}

// Doughnut — hiện số lượng + % trên từng phần
function drawDoughnut(canvasId, labels, data, opts = {}) {
  destroyChart(canvasId);
  const ctx = document.getElementById(canvasId);
  if (!ctx) return;
  const total = data.reduce((a, b) => a + b, 0);
  const bgColors = opts.colors || PALETTE.slice(0, labels.length);
  return new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{ data, backgroundColor: bgColors, borderWidth: 2, borderColor: 'white' }],
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      cutout: '60%',
      plugins: {
        legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 8 } },
        datalabels: {
          display: (ctx) => ctx.dataset.data[ctx.dataIndex] > 0,
          formatter: (value) => {
            const pct = total ? Math.round(value / total * 100) : 0;
            return value + '\n(' + pct + '%)';
          },
          color: 'white',
          font: { size: 11, weight: 'bold' },
          textAlign: 'center',
        },
      },
    },
  });
}

// Line — tắt datalabels
function drawLine(canvasId, labels, datasets, opts = {}) {
  destroyChart(canvasId);
  const ctx = document.getElementById(canvasId);
  if (!ctx) return;
  return new Chart(ctx, {
    type: 'line',
    data: { labels, datasets },
    options: {
      responsive: true, maintainAspectRatio: true,
      plugins: { legend: { display: opts.legend ?? true }, datalabels: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
        y: { beginAtZero: true, ticks: { font: { size: 11 }, stepSize: 1 } },
      },
      ...opts,
    },
  });
}

// Count array by key
function countBy(arr, key) {
  return arr.reduce((acc, r) => {
    const v = r[key] || 'Khác';
    acc[v] = (acc[v] || 0) + 1;
    return acc;
  }, {});
}

// Sort obj by value desc, top N
function topN(obj, n = 10) {
  return Object.entries(obj).sort((a,b) => b[1]-a[1]).slice(0, n);
}
