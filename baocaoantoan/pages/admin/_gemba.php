<!-- ══ PAGE: GEMBA ══ -->
<div class="tab-panel" id="page-gemba">
  <div class="page-header">
    <div class="page-header-left"><div class="page-title">🔍 Gemba Walk</div></div>
    <div class="page-header-right" style="flex-wrap:wrap;gap:6px;">
      <select class="fsel-sm" id="gb-fl-type" onchange="toggleGembaFilter()">
        <option value="all" selected>Tất cả</option>
        <option value="month">Theo tháng</option>
        <option value="week">Theo tuần</option>
      </select>
      <select class="fsel-sm" id="gb-fl-month" onchange="renderGembaPage()" style="display:none;">
        <option value="">-- Tháng --</option>
        <?php for($m=1;$m<=12;$m++): ?><option value="<?=$m?>"><?=$m?></option><?php endfor; ?>
      </select>
      <select class="fsel-sm" id="gb-fl-week" onchange="renderGembaPage()" style="display:none;">
        <option value="">-- Tuần --</option>
        <?php for($w=1;$w<=52;$w++): ?><option value="<?=$w?>"><?=$w?></option><?php endfor; ?>
      </select>
      <input type="text" id="gb-search" class="fsel-sm" placeholder="🔍 Tìm người nhập..." oninput="renderGembaPage()" style="min-width:160px;">
      <button class="btn btn-ghost btn-sm" id="btn-refresh-gemba" onclick="refreshGemba()">↻ Làm mới</button>
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
        <div class="tbl-wrap"><table class="tbl"><thead><tr><th>#</th><th>Họ tên</th><th>Mã NV</th><th>Bộ phận</th><th>Lượt</th><th>Sự cố</th></tr></thead><tbody id="gb-person-tbody"></tbody></table></div>
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
        <table class="tbl"><thead><tr><th>ID</th><th>Khu vực</th><th>Xưởng</th><th>Người KT</th><th>Bộ phận</th><th>Interlock</th><th>Che chắn</th><th>Tủ điện</th><th>S5 MT</th><th>Sự cố</th><th>Ghi chú</th><th>Thời gian</th><th>Sửa</th><th>Xóa</th></tr></thead>
        <tbody id="gb-list-body"></tbody></table>
      </div></div>
    </div>
  </div>
</div>
