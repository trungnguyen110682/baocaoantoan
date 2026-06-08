<!-- ══ ANALYSIS PAGES ══ -->
<div class="tab-panel" id="page-phanloai">
  <div class="page-header"><div class="page-header-left"><div class="page-title">📂 Phân loại</div></div></div>
  <div class="page-body">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;" class="chart-grid-2">
      <div class="wcard"><div class="wcard-hd"><div class="wcard-title">Theo phân loại</div></div><div class="wcard-body"><canvas id="ch-category" style="max-height:280px;"></canvas></div></div>
      <div class="wcard"><div class="wcard-hd"><div class="wcard-title">Top (Pareto)</div></div><div class="wcard-body"><div style="min-height:200px;"><canvas id="ch-cat-pareto"></canvas></div></div></div>
    </div>
    <div class="wcard"><div class="wcard-body" style="padding:0;"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>Phân loại</th><th>Số lượng</th><th>Đã KP</th><th>Tỷ lệ</th></tr></thead><tbody id="cat-tbody"></tbody></table></div></div></div>
  </div>
</div>
<div class="tab-panel" id="page-goldenrule">
  <div class="page-header"><div class="page-header-left"><div class="page-title">🏆 Golden Rule</div></div></div>
  <div class="page-body">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;" class="chart-grid-2">
      <div class="wcard"><div class="wcard-hd"><div class="wcard-title">Theo Golden Rule</div></div><div class="wcard-body"><canvas id="ch-gr" style="max-height:280px;"></canvas></div></div>
      <div class="wcard"><div class="wcard-hd"><div class="wcard-title">Top</div></div><div class="wcard-body"><div style="min-height:200px;"><canvas id="ch-gr-bar"></canvas></div></div></div>
    </div>
    <div class="wcard"><div class="wcard-body" style="padding:0;"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>Golden Rule</th><th>Số lượng</th><th>Đã KP</th><th>Tỷ lệ</th></tr></thead><tbody id="gr-tbody"></tbody></table></div></div></div>
  </div>
</div>
<div class="tab-panel" id="page-mucdo">
  <div class="page-header"><div class="page-header-left"><div class="page-title">📈 Mức độ</div></div></div>
  <div class="page-body">
    <div class="wcard" style="margin-bottom:14px;"><div class="wcard-hd"><div class="wcard-title">Theo mức độ</div></div><div class="wcard-body"><canvas id="ch-severity" style="max-height:280px;"></canvas></div></div>
    <div class="wcard"><div class="wcard-body" style="padding:0;"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>Mức độ</th><th>Số lượng</th><th>Đã KP</th><th>Tỷ lệ</th></tr></thead><tbody id="severity-tbody"></tbody></table></div></div></div>
  </div>
</div>
<div class="tab-panel" id="page-khuvuc">
  <div class="page-header"><div class="page-header-left"><div class="page-title">🗺️ Khu vực</div></div></div>
  <div class="page-body">
    <div class="wcard" style="margin-bottom:14px;"><div class="wcard-hd"><div class="wcard-title">Theo khu vực</div></div><div class="wcard-body"><canvas id="ch-area" style="max-height:280px;"></canvas></div></div>
    <div class="wcard"><div class="wcard-body" style="padding:0;"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>Khu vực</th><th>Số lượng</th><th>Đã KP</th><th>Tỷ lệ</th></tr></thead><tbody id="area-tbody"></tbody></table></div></div></div>
  </div>
</div>
<div class="tab-panel" id="page-hopdauca">
  <div class="page-header"><div class="page-header-left"><div class="page-title">📋 Họp đầu ca</div></div></div>
  <div class="page-body">
    <div class="fbar">
      <span class="fbar-t">Xưởng:</span>
      <select class="fsel" id="hdc-xuong" onchange="loadHopDauCa()">
        <option value="Xuong F0">Xưởng F0</option><option value="Xuong F1">Xưởng F1</option>
        <option value="Xuong F2">Xưởng F2</option><option value="Xuong F3">Xưởng F3</option>
        <option value="Kho van">Kho vận</option><option value="Utility">Utility</option>
      </select>
    </div>
    <div class="wcard"><div class="wcard-hd"><div class="wcard-title" id="hdc-title">Báo cáo xưởng</div></div>
    <div class="wcard-body" style="padding:0;"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>ID</th><th>Người BC</th><th>Vị trí</th><th>Mô tả</th><th>Trạng thái</th><th>Ngày BC</th><th>Hạn KP</th></tr></thead><tbody id="hdc-tbody"></tbody></table></div></div></div>
  </div>
</div>
