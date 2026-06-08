<!-- ══ PAGE: THÀNH VIÊN ══ -->
<div class="tab-panel" id="page-thanhvien">
  <div class="page-header">
    <div class="page-header-left"><div class="page-title">👥 Thành viên</div><div class="page-sub" id="tv-sub">Đang tải...</div></div>
    <div class="page-header-right">
      <select class="fsel-sm" id="tv-filter-vaitro" onchange="renderTVList()">
        <option value="">Tất cả vai trò</option>
        <option value="nhanvien">Nhân viên</option>
        <option value="nguoikhacphuc">Người khắc phục</option>
        <option value="quanly">Quản lý</option>
      </select>
      <select class="fsel-sm" id="tv-filter-bophan" onchange="renderTVList()">
        <option value="">Tất cả bộ phận</option>
      </select>
      <div class="fsearch" style="min-width:160px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" id="tv-search" placeholder="Tìm tên, mã NV..." oninput="renderTVList()">
      </div>
      <button class="btn btn-ghost btn-sm" onclick="downloadTVTemplate()" title="Tải file Excel mẫu">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M19 9h-4V3H9v6H5l7 7 7-7zm-8 9v2H5v-2h6zm4 0v2h-2v-2h2zm4 0v2h-2v-2h2z"/></svg>
        Tải mẫu Excel
      </button>
      <button class="btn btn-ghost btn-sm" onclick="openTVImport()" style="color:var(--teal);">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M9 16h6v-6h4l-7-7-7 7h4v6zm-4 2h14v2H5v-2z"/></svg>
        Import Excel
      </button>
      <button class="btn btn-or btn-sm" onclick="openTVModal()">+ Thêm mới</button>
    </div>
  </div>

  <!-- MODAL IMPORT EXCEL -->
  <div class="modal-ov" id="modal-tv-import">
    <div class="modal" style="max-width:780px;width:95%;">
      <div class="modal-hd">
        <div class="modal-title">📥 Import thành viên từ Excel</div>
        <button class="modal-close" onclick="closeModal('modal-tv-import')">&times;</button>
      </div>
      <div class="modal-bd" style="padding:20px;">

        <!-- Bước 1: chọn file -->
        <div id="tv-imp-step1">
          <div style="background:var(--teal-bg);border:1px solid var(--teal-bd);border-radius:9px;padding:14px 16px;margin-bottom:16px;">
            <div style="font-size:12px;font-weight:700;color:var(--teal);margin-bottom:8px;">📋 CẤU TRÚC FILE EXCEL</div>
            <div style="overflow-x:auto;">
              <table style="width:100%;font-size:11px;border-collapse:collapse;">
                <thead>
                  <tr style="background:var(--teal);color:white;">
                    <th style="padding:5px 8px;text-align:left;border-radius:4px 0 0 0;">ma_nv</th>
                    <th style="padding:5px 8px;text-align:left;">ho_ten *</th>
                    <th style="padding:5px 8px;text-align:left;">bo_phan</th>
                    <th style="padding:5px 8px;text-align:left;">xuong</th>
                    <th style="padding:5px 8px;text-align:left;">chuc_vu</th>
                    <th style="padding:5px 8px;text-align:left;">email</th>
                    <th style="padding:5px 8px;text-align:left;border-radius:0 4px 0 0;">vai_tro</th>
                  </tr>
                </thead>
                <tbody>
                  <tr style="background:white;">
                    <td style="padding:5px 8px;border:1px solid var(--border);color:var(--muted);">17MB01275</td>
                    <td style="padding:5px 8px;border:1px solid var(--border);">Nguyễn Văn A</td>
                    <td style="padding:5px 8px;border:1px solid var(--border);color:var(--muted);">SHE</td>
                    <td style="padding:5px 8px;border:1px solid var(--border);color:var(--muted);">Xuong F2</td>
                    <td style="padding:5px 8px;border:1px solid var(--border);color:var(--muted);">Trưởng ca</td>
                    <td style="padding:5px 8px;border:1px solid var(--border);color:var(--muted);">a@msc.com</td>
                    <td style="padding:5px 8px;border:1px solid var(--border);color:var(--navy);font-weight:600;">nhanvien</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div style="margin-top:10px;font-size:11px;color:var(--muted);line-height:1.7;">
              • Cột <b>ho_ten</b> bắt buộc (<span style="color:red;">*</span>), các cột khác không bắt buộc<br>
              • Cột <b>vai_tro</b>: nhập một trong 3 giá trị: <code>nhanvien</code> · <code>nguoikhacphuc</code> · <code>quanly</code> (mặc định: <code>nhanvien</code>)<br>
              • Nếu <b>ma_nv</b> đã tồn tại → cập nhật (hoặc bỏ qua tùy lựa chọn bên dưới)<br>
              • Dòng đầu tiên là <b>tiêu đề cột</b>, dữ liệu bắt đầu từ dòng 2
            </div>
          </div>

          <!-- Upload area -->
          <div id="tv-imp-dropzone" style="border:2px dashed var(--border);border-radius:10px;padding:30px;text-align:center;cursor:pointer;transition:border-color .2s;"
            onclick="document.getElementById('tv-imp-file').click()"
            ondragover="event.preventDefault();this.style.borderColor='var(--teal)'"
            ondragleave="this.style.borderColor='var(--border)'"
            ondrop="event.preventDefault();this.style.borderColor='var(--border)';handleTVImportFile(event.dataTransfer.files[0])">
            <div style="font-size:36px;margin-bottom:8px;">📊</div>
            <div style="font-size:14px;font-weight:600;color:var(--navy);">Kéo thả hoặc nhấn để chọn file</div>
            <div style="font-size:12px;color:var(--muted);margin-top:4px;">Hỗ trợ: .xlsx, .xls</div>
            <input type="file" id="tv-imp-file" accept=".xlsx,.xls" style="display:none" onchange="handleTVImportFile(this.files[0])">
          </div>
        </div>

        <!-- Bước 2: preview -->
        <div id="tv-imp-step2" style="display:none;">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:gap 8px;">
            <div id="tv-imp-summary" style="font-size:13px;font-weight:600;color:var(--navy);"></div>
            <div style="display:flex;align-items:center;gap:10px;">
              <label style="font-size:12px;font-weight:600;color:var(--navy);">Nếu mã NV đã tồn tại:</label>
              <select id="tv-imp-mode" class="fsel-sm">
                <option value="upsert">Cập nhật (ghi đè)</option>
                <option value="skip">Bỏ qua</option>
              </select>
            </div>
          </div>

          <div id="tv-imp-errors" style="display:none;background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:12px;color:var(--red);"></div>

          <div style="max-height:320px;overflow-y:auto;border:1px solid var(--border);border-radius:8px;">
            <table style="width:100%;font-size:11px;border-collapse:collapse;" id="tv-imp-preview-tbl">
              <thead style="position:sticky;top:0;background:var(--navy);color:white;">
                <tr>
                  <th style="padding:7px 10px;text-align:left;">#</th>
                  <th style="padding:7px 10px;text-align:left;">Mã NV</th>
                  <th style="padding:7px 10px;text-align:left;">Họ tên</th>
                  <th style="padding:7px 10px;text-align:left;">Bộ phận</th>
                  <th style="padding:7px 10px;text-align:left;">Xưởng</th>
                  <th style="padding:7px 10px;text-align:left;">Chức vụ</th>
                  <th style="padding:7px 10px;text-align:left;">Email</th>
                  <th style="padding:7px 10px;text-align:left;">Vai trò</th>
                  <th style="padding:7px 10px;text-align:center;">Trạng thái</th>
                </tr>
              </thead>
              <tbody id="tv-imp-preview-body"></tbody>
            </table>
          </div>

          <div style="margin-top:12px;">
            <button class="btn btn-ghost btn-sm" onclick="resetTVImport()">← Chọn lại file</button>
          </div>
        </div>

      </div>
      <div class="modal-ft" id="tv-imp-footer">
        <button class="btn btn-ghost" onclick="closeModal('modal-tv-import')">Hủy</button>
        <button class="btn btn-navy" id="tv-imp-confirm-btn" onclick="confirmTVImport()" style="display:none;">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="white"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
          Xác nhận Import
        </button>
      </div>
    </div>
  </div>
  <div class="page-body">
    <div class="stat-grid" style="grid-template-columns:repeat(4,1fr);" id="tv-kpi">
      <div class="stat-card"><div class="stat-icon navy">👥</div><div><div class="stat-num" id="tv-k0">0</div><div class="stat-lbl">Tổng</div></div></div>
      <div class="stat-card"><div class="stat-icon blue">👤</div><div><div class="stat-num" id="tv-k1">0</div><div class="stat-lbl">Nhân viên</div></div></div>
      <div class="stat-card"><div class="stat-icon green">🔧</div><div><div class="stat-num" id="tv-k2">0</div><div class="stat-lbl">Người KP</div></div></div>
      <div class="stat-card"><div class="stat-icon orange">👑</div><div><div class="stat-num" id="tv-k3">0</div><div class="stat-lbl">Quản lý</div></div></div>
    </div>
    <div class="wcard">
      <div class="wcard-body" style="padding:0;">
        <div class="tbl-wrap">
          <table class="tbl" id="tv-table">
            <thead><tr>
              <th>Mã NV</th><th>Họ và tên</th><th>Bộ phận</th><th>Xưởng</th>
              <th>Chức vụ</th><th>Email</th><th>Vai trò</th><th style="text-align:center;width:90px;">Thao tác</th>
            </tr></thead>
            <tbody id="tv-tbody"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
