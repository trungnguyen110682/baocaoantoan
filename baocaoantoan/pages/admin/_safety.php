<!-- ══ PAGE: SAFETY REPORT ══ -->
<div class="tab-panel" id="page-safety">
  <div class="page-header">
    <div class="page-header-left"><div class="page-title">⚠️ Safety Report</div><div class="page-sub" id="sr-count-sub">Quản lý toàn bộ báo cáo sự cố an toàn</div></div>
    <div class="page-header-right" style="flex-wrap:wrap;gap:6px;">
      <!-- Nút lọc Tất cả / Tháng / Tuần -->
      <div class="btn-group-sm" id="sr-time-btns">
        <button class="btn btn-sm btn-navy" id="sr-btn-all"   onclick="setSRTimeType('all')">Tất cả</button>
        <button class="btn btn-sm btn-ghost" id="sr-btn-month" onclick="setSRTimeType('month')">Theo tháng</button>
        <button class="btn btn-sm btn-ghost" id="sr-btn-week"  onclick="setSRTimeType('week')">Theo tuần</button>
      </div>
      <!-- Năm + Tháng (khi chọn Theo tháng) -->
      <span id="sr-fl-month-row" style="display:none;" class="fl-row">
        <select class="fsel-sm" id="sr-fl-year-m" onchange="applyFilters()"></select>
        <select class="fsel-sm" id="sr-fl-month" onchange="applyFilters()"></select>
      </span>
      <!-- Năm + Tuần (khi chọn Theo tuần) -->
      <span id="sr-fl-week-row" style="display:none;" class="fl-row">
        <select class="fsel-sm" id="sr-fl-year-w" onchange="applyFilters()"></select>
        <select class="fsel-sm" id="sr-fl-week" onchange="applyFilters()"></select>
      </span>
      <!-- Workshop dropdown -->
      <select class="fsel-sm" id="sr-fl-ws" onchange="applyFilters()">
        <option value="">Tất cả xưởng</option>
        <option>Xuong F0</option><option>Xuong F1</option><option>Xuong F2</option><option>Xuong F3</option>
        <option>Kho van</option><option>Utility</option><option>ADM</option>
      </select>
      <!-- Search -->
      <div class="fsearch" style="min-width:200px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" id="sr-search" placeholder="Tìm mã BC, tên, nội dung..." oninput="applyFilters()">
      </div>
      <button class="btn btn-ghost btn-sm" id="btn-refresh-safety" onclick="refreshSafety()">↻ Làm mới</button>
      <button class="btn btn-ghost btn-sm" onclick="exportSafetyCSV()">⬇ CSV</button>
    </div>
  </div>

  <div class="page-body">
    <!-- Stat cards -->
    <div class="stat-grid" id="sr-stat-grid" style="margin-bottom:14px;">
      <div class="stat-card"><div class="stat-icon navy">📋</div><div><div class="stat-num" id="sr-s-total">0</div><div class="stat-lbl">Tổng</div></div></div>
      <div class="stat-card"><div class="stat-icon yellow">⏳</div><div><div class="stat-num" id="sr-s-chuakp">0</div><div class="stat-lbl">Chưa KP</div></div></div>
      <div class="stat-card"><div class="stat-icon green">✅</div><div><div class="stat-num" id="sr-s-dakp">0</div><div class="stat-lbl">Đã KP</div></div></div>
      <div class="stat-card"><div class="stat-icon red">🚨</div><div><div class="stat-num" id="sr-s-overdue">0</div><div class="stat-lbl">Quá hạn</div></div></div>
    </div>

    <!-- Bảng tổng hợp theo xưởng / người KP -->
    <div class="wcard" style="margin-bottom:14px;">
      <div class="wcard-hd" style="flex-wrap:wrap;gap:8px;">
        <div class="wcard-title">📊 Tổng hợp theo xưởng / người KP</div>
        <div style="display:flex;gap:6px;align-items:center;">
          <button class="btn btn-sm btn-navy" id="sr-tbl-btn-ws" onclick="switchSRTableTab('ws')">🏭 Theo xưởng</button>
          <button class="btn btn-sm btn-ghost" id="sr-tbl-btn-np" onclick="switchSRTableTab('np')">👤 Theo người KP</button>
          <button class="btn btn-ghost btn-sm" style="font-size:11px;" onclick="toggleSRTable(this)">▼ Thu gọn</button>
        </div>
      </div>
      <div class="wcard-body" id="sr-summary-wrap">
        <div style="overflow-x:auto;">
          <table class="tbl" id="sr-summary-table">
            <thead><tr>
              <th id="sr-th-name">Xưởng</th>
              <th>Tổng</th>
              <th style="color:var(--orange);">Chưa KP</th>
              <th style="color:var(--green);">Đã KP</th>
              <th style="color:var(--red);">Quá hạn</th>
              <th>Tỷ lệ KP</th>
            </tr></thead>
            <tbody id="sr-summary-body"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Overdue alert -->
    <div id="overdue-alert" style="display:none;background:var(--red-bg);border:1px solid var(--red-bd);border-radius:10px;padding:12px 16px;margin-bottom:12px;align-items:center;gap:10px;">
      <span style="font-size:18px;">🚨</span>
      <div><div style="font-weight:700;color:var(--red);" id="overdue-msg">Có báo cáo quá hạn!</div><div style="font-size:12px;color:var(--muted);">Cần xử lý ngay</div></div>
    </div>

    <!-- Status tabs -->
    <div class="itabs" id="sr-tabs">
      <button class="itab on" onclick="switchSRTab('',this)">Tất cả <span class="tc" id="tc-all">0</span></button>
      <button class="itab" onclick="switchSRTab('pending',this)">Chờ duyệt <span class="tc" id="tc-pending">0</span></button>
      <button class="itab" onclick="switchSRTab('approved',this)">Đã duyệt <span class="tc" id="tc-approved">0</span></button>
      <button class="itab" onclick="switchSRTab('fixed',this)">Đã KP <span class="tc" id="tc-fixed">0</span></button>
      <button class="itab" onclick="switchSRTab('rejected',this)">Từ chối <span class="tc" id="tc-rejected">0</span></button>
      <button class="itab" onclick="switchSRTab('overdue',this)">Quá hạn <span class="tc" id="tc-overdue">0</span></button>
    </div>

    <div id="sr-list"></div>
    <div id="sr-pagination"></div>
  </div>
</div>
