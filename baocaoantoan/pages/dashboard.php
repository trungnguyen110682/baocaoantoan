<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireViewer();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản Lý An Toàn — MMB</title>
<link rel="stylesheet" href="/assets/css/style.css?v=<?php echo time(); ?>">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<style>
.gb-stat-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:14px; }
.gb-sc { background:white; border-radius:10px; border:1px solid var(--border); padding:12px 14px; text-align:center; }
.gb-sn { font-size:24px; font-weight:700; color:var(--navy); }
.gb-sl { font-size:11px; color:var(--muted); margin-top:2px; }
.fsel-sm { padding:5px 9px; border:1.5px solid var(--border); border-radius:7px; font-size:12px; background:var(--bg); cursor:pointer; }
.fsel-sm:focus { outline:none; border-color:var(--navy); }
.fl-row { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
</style>
</head>
<body style="display:flex;flex-direction:column;min-height:100vh;">

<!-- NAV -->
<nav>
  <div class="nav-brand">
    <button class="sb-toggle" id="sb-toggle">☰</button>
    <div class="nav-logo" style="margin-left:4px;">
      <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
    </div>
    <span class="nav-title">An Toàn MMB</span>
    <span class="nav-badge">QUẢN LÝ</span>
  </div>
  <div class="nav-right">
    <span id="nav-time"></span>
    <a href="/logout.php"><button class="logout-btn">Đăng xuất</button></a>
  </div>
</nav>
<div id="sb-overlay" class="sb-overlay"></div>

<div class="app-shell">
<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sb-logo">
    <div class="sb-logo-title">HRE SYSTEM</div>
    <div class="sb-logo-sub">An toàn sản xuất</div>
  </div>
  <div class="sb-section">Báo cáo</div>
  <button class="sb-item active" onclick="switchPage('safety',this)"><span class="sb-icon">⚠️</span> Safety Report <span class="sb-badge" id="sb-pending-cnt">0</span></button>
  <button class="sb-item" onclick="switchPage('gemba',this)"><span class="sb-icon">🔍</span> Gemba Walk</button>
  <div class="sb-bottom">
    <div class="sb-user">
      <div class="sb-user-av">Q</div>
      <div><div class="sb-user-name">Quản lý</div><div class="sb-user-role">Xem & Khắc phục</div></div>
    </div>
  </div>
</aside>

<!-- MAIN -->
<div class="main-content" id="main-content">

<!-- ══ PAGE: SAFETY REPORT ══ -->
<div class="tab-panel active" id="page-safety">
  <div class="page-header">
    <div class="page-header-left">
      <div class="page-title">⚠️ Safety Report</div>
      <div class="page-sub" id="sr-count-sub">Đang tải...</div>
    </div>
    <div class="page-header-right" style="flex-wrap:wrap;gap:6px;">
      <div class="btn-group-sm" id="sr-time-btns">
        <button class="btn btn-sm btn-navy" id="sr-btn-all"   onclick="setSRTimeType('all')">Tất cả</button>
        <button class="btn btn-sm btn-ghost" id="sr-btn-month" onclick="setSRTimeType('month')">Theo tháng</button>
        <button class="btn btn-sm btn-ghost" id="sr-btn-week"  onclick="setSRTimeType('week')">Theo tuần</button>
      </div>
      <span id="sr-fl-month-row" style="display:none;" class="fl-row">
        <select class="fsel-sm" id="sr-fl-year-m" onchange="applyFilters()"></select>
        <select class="fsel-sm" id="sr-fl-month"  onchange="applyFilters()"></select>
      </span>
      <span id="sr-fl-week-row" style="display:none;" class="fl-row">
        <select class="fsel-sm" id="sr-fl-year-w" onchange="applyFilters()"></select>
        <select class="fsel-sm" id="sr-fl-week"   onchange="applyFilters()"></select>
      </span>
      <select class="fsel-sm" id="sr-fl-ws" onchange="applyFilters()">
        <option value="">Tất cả xưởng</option>
        <option>Xuong F0</option><option>Xuong F1</option><option>Xuong F2</option><option>Xuong F3</option>
        <option>Kho van</option><option>Utility</option><option>ADM</option>
      </select>
      <div class="fsearch" style="min-width:200px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" id="sr-search" placeholder="Tìm mã BC, tên, nội dung..." oninput="applyFilters()">
      </div>
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

    <!-- Bảng tổng hợp -->
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

<!-- ══ PAGE: GEMBA WALK ══ -->
<div class="tab-panel" id="page-gemba">
  <div class="page-header">
    <div class="page-header-left"><div class="page-title">🔍 Gemba Walk</div></div>
    <div class="page-header-right" style="flex-wrap:wrap;gap:6px;">
      <select class="fsel-sm" id="gb-fl-type" onchange="toggleGembaFilter()">
        <option value="all" selected>Tất cả</option>
        <option value="month">Theo tháng</option>
        <option value="week">Theo tuần</option>
      </select>
      <select class="fsel-sm" id="gb-fl-month" onchange="renderGembaPage()" style="display:none;"></select>
      <select class="fsel-sm" id="gb-fl-week"  onchange="renderGembaPage()" style="display:none;"></select>
      <input type="text" id="gb-search" class="fsel-sm" placeholder="🔍 Tìm người nhập..." oninput="renderGembaPage()" style="min-width:160px;">
    </div>
  </div>
  <div class="page-body">
    <div class="gb-stat-grid">
      <div class="gb-sc"><div class="gb-sn" id="gb-st0">0</div><div class="gb-sl">🚶 Lượt Gemba</div></div>
      <div class="gb-sc"><div class="gb-sn" id="gb-st1">0</div><div class="gb-sl">👥 Người tham gia</div></div>
      <div class="gb-sc"><div class="gb-sn" id="gb-st2">0</div><div class="gb-sl">⚠️ Sự cố</div></div>
    </div>

    <div class="wcard" style="margin-bottom:14px;">
      <div class="wcard-hd"><div class="wcard-title">🏭 Lượt Gemba theo xưởng</div></div>
      <div class="wcard-body"><div style="position:relative;height:220px;"><canvas id="gemba-ws-chart"></canvas></div></div>
    </div>

    <div class="wcard" style="margin-bottom:14px;">
      <div class="wcard-hd"><div class="wcard-title">🏆 Người tham gia Gemba</div></div>
      <div class="wcard-body" style="padding:0;">
        <div class="tbl-wrap">
          <table class="tbl">
            <thead><tr><th>#</th><th>Họ tên</th><th>Mã NV</th><th>Bộ phận</th><th>Lượt</th><th>Sự cố</th></tr></thead>
            <tbody id="gb-person-tbody"></tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="wcard">
      <div class="wcard-hd">
        <div class="wcard-title">📋 Danh sách chi tiết</div>
        <div style="display:flex;gap:6px;">
          <select class="fsel-sm" id="gb-filter-xuong" onchange="renderGembaList()">
            <option value="">Tất cả xưởng</option>
            <option>Xuong F0</option><option>Xuong F1</option><option>Xuong F2</option><option>Xuong F3</option>
            <option>Kho van</option><option>Utility</option><option>ADM</option>
          </select>
        </div>
      </div>
      <div class="wcard-body" style="padding:0;"><div class="tbl-wrap">
        <table class="tbl">
          <thead><tr>
            <th>ID</th><th>Khu vực</th><th>Xưởng</th><th>Người KT</th><th>Bộ phận</th>
            <th>Interlock</th><th>Che chắn</th><th>Tủ điện</th><th>S5 MT</th>
            <th>Sự cố</th><th>Ghi chú</th><th>Thời gian</th>
          </tr></thead>
          <tbody id="gb-list-body"></tbody>
        </table>
      </div></div>
    </div>
  </div>
</div>

</div><!-- /main-content -->
</div><!-- /app-shell -->

<!-- SAFETY REPORT DETAIL MODAL -->
<div class="modal-ov" id="modal-detail">
  <div class="modal">
    <div class="modal-hd">
      <h3 id="md-title">Chi tiết báo cáo</h3>
      <button class="modal-close" onclick="closeModal('modal-detail')">&times;</button>
    </div>
    <div class="modal-body" id="md-body"></div>
    <div class="modal-footer" id="md-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-detail')">Đóng</button>
    </div>
  </div>
</div>

<!-- GEMBA DETAIL MODAL -->
<div class="modal-ov" id="modal-gb-detail">
  <div class="modal">
    <div class="modal-hd">
      <h3>Chi tiết Gemba Walk</h3>
      <button class="modal-close" onclick="closeModal('modal-gb-detail')">&times;</button>
    </div>
    <div class="modal-body" id="md-gb-body"></div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-gb-detail')">Đóng</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>
<script src="/assets/js/app.js?v=<?php echo time(); ?>"></script>
<script src="/assets/js/charts.js?v=<?php echo time(); ?>"></script>
<script src="/assets/js/manager.js?v=<?php echo time(); ?>"></script>
</body>
</html>
