<!-- ══ PAGE: DASHBOARD ══ -->
<div class="tab-panel active" id="page-dashboard">
  <div class="page-header">
    <div class="page-header-left"><div class="page-title">📊 HSE Dashboard</div><div class="page-sub" id="dash-sub">Tổng quan an toàn</div></div>
    <div class="page-header-right">
      <select class="fsel-sm" id="fl-type" onchange="toggleDashFilter()">
        <option value="all">Tất cả</option>
        <option value="month">Theo tháng</option>
        <option value="week">Theo tuần</option>
      </select>
      <span id="fl-time-row" style="display:none;" class="fl-row">
        <select class="fsel-sm" id="fl-year" onchange="renderDashboard()"></select>
        <select class="fsel-sm" id="fl-month" onchange="renderDashboard()" style="display:none;"></select>
        <select class="fsel-sm" id="fl-week" onchange="renderDashboard()" style="display:none;"></select>
      </span>
      <select class="fsel-sm" id="fl-ws" onchange="renderDashboard()">
        <option value="">Tất cả xưởng</option>
        <option>Xuong F0</option><option>Xuong F1</option><option>Xuong F2</option><option>Xuong F3</option>
        <option>Kho van</option><option>Utility</option><option>ADM</option>
      </select>
      <span id="fl-range-lbl" class="fl-lbl" style="display:none;"></span>
      <button class="btn btn-ghost btn-sm" onclick="showTop()">🏆 Top NV</button>
      <button class="btn btn-ghost btn-sm" id="btn-refresh-dash" onclick="refreshDashboard()">↻ Làm mới</button>
      <button class="btn btn-ghost btn-sm" onclick="exportAllCSV()">⬇ CSV</button>
    </div>
  </div>
  <div class="page-body">
    <div class="stat-grid" id="dash-stats">
      <div class="stat-card"><div class="stat-icon navy">📋</div><div><div class="stat-num" id="s-total"><?= $stats['total'] ?></div><div class="stat-lbl">Tổng</div></div></div>
      <div class="stat-card"><div class="stat-icon yellow">⏳</div><div><div class="stat-num" id="s-pending"><?= $stats['pending'] ?></div><div class="stat-lbl">Chờ duyệt</div></div></div>
      <div class="stat-card"><div class="stat-icon green">✅</div><div><div class="stat-num" id="s-fixed"><?= $stats['fixed'] ?></div><div class="stat-lbl">Đã KP</div></div></div>
      <div class="stat-card"><div class="stat-icon red">🚨</div><div><div class="stat-num" id="s-overdue"><?= $stats['overdue'] ?></div><div class="stat-lbl">Quá hạn</div></div></div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;" class="chart-grid-2">
      <div class="wcard"><div class="wcard-hd"><div class="wcard-title">Trạng thái</div></div><div class="wcard-body"><div style="position:relative;height:220px;"><canvas id="ch-status"></canvas></div></div></div>
      <div class="wcard"><div class="wcard-hd"><div class="wcard-title">Theo xưởng</div></div><div class="wcard-body"><div style="position:relative;height:220px;"><canvas id="ch-xuong"></canvas></div></div></div>
    </div>

    <div class="wcard" style="margin-bottom:14px;">
      <div class="wcard-hd" style="flex-wrap:wrap;gap:8px;">
        <div class="wcard-title">📊 Thống kê theo xưởng / người KP</div>
        <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
          <div style="display:flex;gap:4px;">
            <button class="btn btn-sm btn-navy" id="ws-tab-ws" onclick="switchWsChartTab('ws')">🏭 Theo xưởng</button>
            <button class="btn btn-sm btn-ghost" id="ws-tab-np" onclick="switchWsChartTab('np')">👤 Theo người KP</button>
          </div>
        </div>
      </div>
      <div class="wcard-body">
        <div class="ws-chart-grid" id="ws-charts-grid">
          <div class="ws-chart-box">
            <div class="ws-chart-title" style="color:var(--navy);">📋 Tổng số báo cáo</div>
            <div class="ws-chart-wrap"><canvas id="wsc-total"></canvas></div>
          </div>
          <div class="ws-chart-box">
            <div class="ws-chart-title" style="color:var(--yellow);">⚠️ Chưa khắc phục</div>
            <div class="ws-chart-wrap"><canvas id="wsc-chua"></canvas></div>
          </div>
          <div class="ws-chart-box">
            <div class="ws-chart-title" style="color:var(--orange);">🔴 Quá hạn</div>
            <div class="ws-chart-wrap"><canvas id="wsc-overdue"></canvas></div>
          </div>
          <div class="ws-chart-box">
            <div class="ws-chart-title" style="color:var(--teal);">✅ Tỷ lệ khắc phục (%)</div>
            <div class="ws-chart-wrap"><canvas id="wsc-rate"></canvas></div>
          </div>
        </div>
        <div style="margin-top:8px;text-align:right;">
          <button class="btn btn-ghost btn-sm" style="font-size:11px;" onclick="toggleWsTable(this)">▶ Xem bảng số liệu</button>
        </div>
        <div id="ws-table-wrap" style="display:none;margin-top:10px;overflow-x:auto;">
          <div id="ws-panel"><table class="tbl"><thead><tr><th>Xưởng / Người KP</th><th>Tổng</th><th>Chưa KP</th><th>Đã KP</th><th>Quá hạn</th><th>Tỷ lệ KP</th></tr></thead><tbody id="ws-body"></tbody></table></div>
        </div>
      </div>
    </div>

    <div class="wcard" style="margin-bottom:14px;">
      <div class="wcard-hd"><div class="wcard-title">🚶 Gemba Walk — tổng hợp</div></div>
      <div class="wcard-body">
        <div class="gb-stat-grid">
          <div class="gb-sc"><div class="gb-sn" id="d-gb-luot">0</div><div class="gb-sl">Lượt</div></div>
          <div class="gb-sc"><div class="gb-sn" id="d-gb-nguoi">0</div><div class="gb-sl">Người</div></div>
          <div class="gb-sc"><div class="gb-sn" id="d-gb-suco">0</div><div class="gb-sl">Sự cố</div></div>
        </div>
        <div style="position:relative;height:200px;">
          <canvas id="ch-gemba-ws"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>
