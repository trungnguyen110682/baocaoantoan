<!-- ══ PAGE: QR MANAGER ══ -->
<div class="tab-panel" id="page-qrmanager">
  <div class="page-header">
    <div class="page-header-left">
      <div class="page-title">📷 QR Manager</div>
      <div class="page-sub" id="qr-sub">Quản lý mã QR khu vực</div>
    </div>
    <div class="page-header-right">
      <select class="fsel-sm" id="qr-filter-xuong" onchange="renderQRList()">
        <option value="">Tất cả xưởng</option>
      </select>
      <button class="btn btn-ghost btn-sm" onclick="openQRImport()" style="color:var(--teal);">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M9 16h6v-6h4l-7-7-7 7h4v6zm-4 2h14v2H5v-2z"/></svg>
        Import Excel
      </button>
      <button class="btn btn-ghost btn-sm" onclick="exportQRList()">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M19 9h-4V3H9v6H5l7 7 7-7zm-8 9v2H5v-2h6zm4 0v2h-2v-2h2zm4 0v2h-2v-2h2z"/></svg>
        Xuất danh sách
      </button>
    </div>
  </div>

  <!-- MODAL IMPORT EXCEL -->
  <div class="modal-ov" id="modal-qr-import">
    <div class="modal" style="max-width:820px;width:95%;">
      <div class="modal-hd">
        <div class="modal-title">📥 Import Xưởng / Khu vực từ Excel</div>
        <button class="modal-close" onclick="closeModal('modal-qr-import')">&times;</button>
      </div>
      <div class="modal-bd" style="padding:20px;">

        <!-- Bước 1: chọn file -->
        <div id="qr-imp-step1">
          <div style="background:var(--teal-bg);border:1px solid var(--teal-bd);border-radius:9px;padding:14px 16px;margin-bottom:16px;">
            <div style="font-size:12px;font-weight:700;color:var(--teal);margin-bottom:8px;">📋 CẤU TRÚC FILE EXCEL</div>
            <table style="width:100%;font-size:11px;border-collapse:collapse;">
              <thead>
                <tr style="background:var(--teal);color:white;">
                  <th style="padding:5px 8px;text-align:left;">Xưởng</th>
                  <th style="padding:5px 8px;text-align:left;">Khu vực</th>
                  <th style="padding:5px 8px;text-align:left;">Phụ trách</th>
                </tr>
              </thead>
              <tbody>
                <tr style="background:white;">
                  <td style="padding:5px 8px;border:1px solid var(--border);">F5 - Tương ớt</td>
                  <td style="padding:5px 8px;border:1px solid var(--border);">Sơ chế</td>
                  <td style="padding:5px 8px;border:1px solid var(--border);color:var(--muted);">Thanh NC</td>
                </tr>
              </tbody>
            </table>
            <div style="margin-top:10px;font-size:11px;color:var(--muted);line-height:1.7;">
              • Cột <b>Xưởng</b> và <b>Khu vực</b> bắt buộc<br>
              • Mã QR sẽ được <b>tự động sinh</b> từ tên xưởng + khu vực<br>
              • <b style="color:var(--red);">Toàn bộ dữ liệu QR cũ sẽ bị xóa</b> và thay bằng dữ liệu mới
            </div>
          </div>

          <div id="qr-imp-dropzone" style="border:2px dashed var(--border);border-radius:10px;padding:30px;text-align:center;cursor:pointer;transition:border-color .2s;"
            onclick="document.getElementById('qr-imp-file').click()"
            ondragover="event.preventDefault();this.style.borderColor='var(--teal)'"
            ondragleave="this.style.borderColor='var(--border)'"
            ondrop="event.preventDefault();this.style.borderColor='var(--border)';handleQRImportFile(event.dataTransfer.files[0])">
            <div style="font-size:36px;margin-bottom:8px;">📊</div>
            <div style="font-size:14px;font-weight:600;color:var(--navy);">Kéo thả hoặc nhấn để chọn file</div>
            <div style="font-size:12px;color:var(--muted);margin-top:4px;">Hỗ trợ: .xlsx, .xls</div>
            <input type="file" id="qr-imp-file" accept=".xlsx,.xls" style="display:none" onchange="handleQRImportFile(this.files[0])">
          </div>
        </div>

        <!-- Bước 2: preview -->
        <div id="qr-imp-step2" style="display:none;">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <div id="qr-imp-summary" style="font-size:13px;font-weight:600;color:var(--navy);"></div>
            <div style="display:flex;align-items:center;gap:8px;">
              <div style="font-size:11px;padding:4px 10px;background:#fef2f2;color:var(--red);border:1px solid #fca5a5;border-radius:6px;font-weight:600;">
                ⚠️ Dữ liệu QR cũ sẽ bị xóa hoàn toàn
              </div>
            </div>
          </div>

          <div style="max-height:360px;overflow-y:auto;border:1px solid var(--border);border-radius:8px;">
            <table style="width:100%;font-size:11px;border-collapse:collapse;" id="qr-imp-preview-tbl">
              <thead style="position:sticky;top:0;background:var(--navy);color:white;">
                <tr>
                  <th style="padding:7px 10px;text-align:left;">#</th>
                  <th style="padding:7px 10px;text-align:left;">Xưởng</th>
                  <th style="padding:7px 10px;text-align:left;">Khu vực</th>
                  <th style="padding:7px 10px;text-align:left;">Phụ trách</th>
                  <th style="padding:7px 10px;text-align:left;">Mã QR (dự kiến)</th>
                </tr>
              </thead>
              <tbody id="qr-imp-preview-body"></tbody>
            </table>
          </div>

          <div style="margin-top:12px;">
            <button class="btn btn-ghost btn-sm" onclick="resetQRImport()">← Chọn lại file</button>
          </div>
        </div>

      </div>
      <div class="modal-ft">
        <button class="btn btn-ghost" onclick="closeModal('modal-qr-import')">Hủy</button>
        <button class="btn btn-red" id="qr-imp-confirm-btn" onclick="confirmQRImport()" style="display:none;">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="white"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
          Xác nhận Import (Xóa & Thay mới)
        </button>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="wcard"><div class="wcard-body" style="padding:0;"><div class="tbl-wrap">
      <table class="tbl">
        <thead><tr>
          <th>Mã QR</th><th>Xưởng</th><th>Khu vực</th><th>Phụ trách</th><th>Link</th><th style="text-align:center;width:80px;">Xóa</th>
        </tr></thead>
        <tbody id="qr-tbody"></tbody>
      </table>
    </div></div></div>
  </div>
</div>
