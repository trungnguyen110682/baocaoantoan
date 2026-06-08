<!-- ══ MODAL: DETAIL ══ -->
<div class="modal-ov" id="modal-detail">
  <div class="modal" style="max-width:620px;">
    <div class="modal-hd"><h3 id="md-title">Chi tiết báo cáo</h3><button class="modal-close" onclick="closeModal('modal-detail')">&times;</button></div>
    <div class="modal-body" id="md-body"></div>
    <div class="modal-footer" id="md-footer"><button class="btn btn-ghost" onclick="closeModal('modal-detail')">Đóng</button></div>
  </div>
</div>

<!-- ══ MODAL: APPROVE ══ -->
<div class="modal-ov" id="modal-approve">
  <div class="modal">
    <div class="modal-hd" style="background:linear-gradient(135deg,var(--navy),var(--denim));"><h3>Phê duyệt & phân công KP</h3><button class="modal-close" onclick="closeModal('modal-approve')">&times;</button></div>
    <div class="modal-body">
      <input type="hidden" id="ap-id">
      <div class="mf-row"><label class="mfl">Phân loại</label>
        <select id="ap-category" class="fc"><option value="">-- Chọn --</option>
          <option>Che chắn</option><option>Interlock</option><option>Tủ điện</option>
          <option>Xe nâng</option><option>Rò rỉ</option><option>5S</option><option>Môi trường</option><option>Khác</option>
        </select></div>
      <div class="mf-row"><label class="mfl">Golden Rule</label>
        <select id="ap-goldenrule" class="fc"><option value="">-- Chọn --</option>
          <option>AT điện</option><option>AT chuyển động</option><option>AT trên cao</option>
          <option>AT xe nâng</option><option>AT hóa chất</option><option>AT loto</option>
          <option>AT hàn cắt</option><option>AT bỏng</option><option>AT không gian kín</option>
        </select></div>
      <div class="frow">
        <div class="mf-row"><label class="mfl">Mức độ</label>
          <select id="ap-severity" class="fc"><option value="">-- Chọn --</option>
            <option>Không phù hợp</option><option>Suýt bị</option><option>Sự cố</option>
          </select></div>
        <div class="mf-row"><label class="mfl">Khu vực</label>
          <select id="ap-area" class="fc"><option value="">-- Chọn --</option>
            <option>Công nghệ</option><option>Đóng gói</option><option>Đóng thùng</option>
            <option>Thành phẩm</option><option>Kho</option><option>Muối cốt</option><option>Utility</option><option>Khác</option>
          </select></div>
      </div>
      <div class="mf-row"><label class="mfl">Người khắc phục <span class="req">*</span></label>
        <select id="ap-nkp" class="fc" onchange="onNKPSelect()"><option value="">-- Chọn --</option>
          <?php foreach ($nguoiKP as $n): ?>
          <option value="<?= e($n['ho_ten']) ?>" data-cv="<?= e($n['chuc_vu']) ?>" data-email="<?= e($n['email']) ?>"><?= e($n['ho_ten']) ?> — <?= e($n['chuc_vu']) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="frow">
        <div class="mf-row"><label class="mfl">Chức vụ NKP</label><input type="text" id="ap-nkp-cv" class="fc" readonly placeholder="Tự động"></div>
        <div class="mf-row"><label class="mfl">Hạn KP <span class="req">*</span></label><input type="date" id="ap-deadline" class="fc"></div>
      </div>
      <input type="hidden" id="ap-nkp-email">
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-approve')">Hủy</button>
      <button class="btn btn-green" onclick="doApprove()">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="white"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
        Phê duyệt
      </button>
    </div>
  </div>
</div>

<!-- ══ MODAL: REJECT ══ -->
<div class="modal-ov" id="modal-reject">
  <div class="modal" style="max-width:400px;">
    <div class="modal-hd" style="background:linear-gradient(135deg,#7f1d1d,var(--red));"><h3>Từ chối báo cáo</h3><button class="modal-close" onclick="closeModal('modal-reject')">&times;</button></div>
    <div class="modal-body">
      <p style="font-size:13px;color:var(--muted);margin-bottom:14px;">Bạn có chắc muốn từ chối báo cáo <b id="rj-id-show"></b>?</p>
      <input type="hidden" id="rj-id">
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-reject')">Hủy</button>
      <button class="btn btn-red" onclick="doReject()">Từ chối</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: EDIT REPORT ══ -->
<div class="modal-ov" id="modal-edit-report">
  <div class="modal" style="max-width:680px;">
    <div class="modal-hd" style="background:linear-gradient(135deg,#1e3a5f,var(--denim));"><h3>✏️ Chỉnh sửa báo cáo <span id="er-id-show" style="font-size:13px;font-weight:400;opacity:.8;"></span></h3><button class="modal-close" onclick="closeModal('modal-edit-report')">&times;</button></div>
    <div class="modal-body" style="max-height:75vh;overflow-y:auto;">
      <input type="hidden" id="er-id">
      <div style="font-size:11px;font-weight:700;color:var(--navy);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Thông tin người báo cáo</div>
      <div class="frow">
        <div class="mf-row"><label class="mfl">Mã NV</label><input type="text" id="er-manv" class="fc" placeholder="MMB001"></div>
        <div class="mf-row"><label class="mfl">Họ và tên</label><input type="text" id="er-hoten" class="fc" placeholder="Nguyễn Văn A"></div>
      </div>
      <div class="frow">
        <div class="mf-row"><label class="mfl">Bộ phận</label><input type="text" id="er-bophan" class="fc"></div>
        <div class="mf-row"><label class="mfl">Xưởng</label>
          <select id="er-xuong" class="fc">
            <option value="">-- Chọn --</option>
            <option>Xuong F0</option><option>Xuong F1</option><option>Xuong F2</option><option>Xuong F3</option>
            <option>Kho van</option><option>Utility</option><option>ADM</option>
          </select>
        </div>
      </div>
      <div class="mf-row"><label class="mfl">Vị trí sự cố</label><input type="text" id="er-vitri" class="fc"></div>
      <div class="mf-row"><label class="mfl">Nội dung</label><textarea id="er-noidung" class="fc" rows="3" style="resize:vertical;"></textarea></div>
      <div class="mf-row"><label class="mfl">Ảnh báo cáo (URL)</label><input type="url" id="er-hinhanh" class="fc" placeholder="https://..."></div>
      <hr style="margin:14px 0;border-color:var(--border);">
      <div style="font-size:11px;font-weight:700;color:var(--navy);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Phân loại & phân công</div>
      <div class="frow">
        <div class="mf-row"><label class="mfl">Phân loại</label>
          <select id="er-category" class="fc"><option value="">-- Chọn --</option>
            <option>Che chắn</option><option>Interlock</option><option>Tủ điện</option>
            <option>Xe nâng</option><option>Rò rỉ</option><option>5S</option><option>Môi trường</option><option>Khác</option>
          </select></div>
        <div class="mf-row"><label class="mfl">Mức độ</label>
          <select id="er-severity" class="fc"><option value="">-- Chọn --</option>
            <option>Không phù hợp</option><option>Suýt bị</option><option>Sự cố</option>
          </select></div>
      </div>
      <div class="frow">
        <div class="mf-row"><label class="mfl">Golden Rule</label>
          <select id="er-goldenrule" class="fc"><option value="">-- Chọn --</option>
            <option>AT điện</option><option>AT chuyển động</option><option>AT trên cao</option>
            <option>AT xe nâng</option><option>AT hóa chất</option><option>AT loto</option>
            <option>AT hàn cắt</option><option>AT bỏng</option><option>AT không gian kín</option>
          </select></div>
        <div class="mf-row"><label class="mfl">Khu vực</label>
          <select id="er-area" class="fc"><option value="">-- Chọn --</option>
            <option>Công nghệ</option><option>Đóng gói</option><option>Đóng thùng</option>
            <option>Thành phẩm</option><option>Kho</option><option>Muối cốt</option><option>Utility</option><option>Khác</option>
          </select></div>
      </div>
      <div class="mf-row"><label class="mfl">Người KP</label>
        <select id="er-nkp" class="fc" onchange="onERNKPSelect()"><option value="">-- Chọn --</option>
          <?php foreach ($nguoiKP as $n): ?>
          <option value="<?= e($n['ho_ten']) ?>" data-cv="<?= e($n['chuc_vu']) ?>" data-email="<?= e($n['email']) ?>"><?= e($n['ho_ten']) ?> — <?= e($n['chuc_vu']) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="frow">
        <div class="mf-row"><label class="mfl">Chức vụ NKP</label><input type="text" id="er-nkp-cv" class="fc" placeholder="Tự động"></div>
        <div class="mf-row"><label class="mfl">Hạn KP</label><input type="date" id="er-deadline" class="fc"></div>
      </div>
      <input type="hidden" id="er-nkp-email">
      <div class="frow">
        <div class="mf-row"><label class="mfl">Trạng thái</label>
          <select id="er-status" class="fc">
            <option value="pending">Chờ duyệt</option>
            <option value="approved">Đã duyệt</option>
            <option value="fixed">Đã KP</option>
            <option value="rejected">Từ chối</option>
          </select></div>
      </div>
      <hr style="margin:14px 0;border-color:var(--border);">
      <div style="font-size:11px;font-weight:700;color:var(--teal);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">🔗 Link khắc phục</div>
      <div style="display:flex;gap:8px;align-items:center;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:10px 12px;">
        <span id="er-kp-link" style="font-size:12px;color:var(--navy);word-break:break-all;flex:1;">—</span>
        <button class="btn btn-ghost btn-xs" onclick="copyKPLink()">📋 Copy</button>
        <a id="er-kp-open" href="#" target="_blank" class="btn btn-ghost btn-xs" style="text-decoration:none;">↗ Mở</a>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-edit-report')">Hủy</button>
      <button class="btn btn-orange btn-sm" onclick="openFixFromEdit()">🔧 Khắc phục</button>
      <button class="btn btn-navy" onclick="doEditReport()">💾 Lưu thay đổi</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: ADMIN FIX ══ -->
<div class="modal-ov" id="modal-admin-fix">
  <div class="modal" style="max-width:480px;">
    <div class="modal-hd" style="background:linear-gradient(135deg,#064e3b,var(--green));"><h3>🔧 Khắc phục báo cáo <span id="af-id-show" style="font-size:13px;font-weight:400;opacity:.8;"></span></h3><button class="modal-close" onclick="closeModal('modal-admin-fix')">&times;</button></div>
    <div class="modal-body">
      <input type="hidden" id="af-id">
      <input type="hidden" id="af-fixer">
      <div class="mf-row"><label class="mfl">Nội dung khắc phục <span class="req">*</span></label><textarea id="af-note" class="fc" rows="3" placeholder="Mô tả chi tiết..." style="resize:vertical;"></textarea></div>
      <div class="mf-row">
        <label class="mfl">Hình ảnh sau KP <span class="req">*</span></label>
        <div class="uarea" id="af-uarea">
          <div class="ubtn-row">
            <button type="button" class="ubtn" id="af-btn-camera">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M20 5h-3.17L15 3H9L7.17 5H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm-8 13c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.65 0-3 1.35-3 3s1.35 3 3 3 3-1.35 3-3-1.35-3-3-3z"/></svg>
              Chụp ảnh
            </button>
            <button type="button" class="ubtn" id="af-btn-lib">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
              Chọn ảnh
            </button>
          </div>
          <p>Kéo thả hoặc nhấn nút bên trên để chọn ảnh sau khi KP</p>
          <input type="file" id="af-file-input" accept="image/*" style="display:none">
          <div id="af-img-preview" class="iprev">
            <img src="" alt="preview">
            <button type="button" class="xbtn">&times;</button>
          </div>
        </div>
        <div class="ferr" id="err-af-img">Vui lòng chọn hình ảnh sau khắc phục</div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-admin-fix')">Hủy</button>
      <button class="btn btn-green" onclick="doAdminFix()">✅ Xác nhận đã KP</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: GEMBA DETAIL ══ -->
<div class="modal-ov" id="modal-gb-detail" data-id="">
  <div class="modal"><div class="modal-hd"><h3>Chi tiết Gemba Walk</h3><button class="modal-close" onclick="closeModal('modal-gb-detail')">&times;</button></div>
    <div class="modal-body" id="md-gb-body"></div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-gb-detail')">Đóng</button>
      <button class="btn btn-blue" onclick="editGembaFromDetail()">✏️ Sửa</button>
      <button class="btn btn-red" onclick="deleteGembaFromDetail()">🗑 Xóa</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: GEMBA EDIT ══ -->
<div class="modal-ov" id="modal-gb-edit">
  <div class="modal" style="max-width:640px;">
    <div class="modal-hd" style="background:linear-gradient(135deg,#1e3a5f,var(--denim));"><h3>✏️ Chỉnh sửa Gemba Walk</h3><button class="modal-close" onclick="closeModal('modal-gb-edit')">&times;</button></div>
    <div class="modal-body" style="max-height:75vh;overflow-y:auto;">
      <input type="hidden" id="gb-ed-id">
      <div style="font-size:11px;font-weight:700;color:var(--navy);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Thông tin khu vực</div>
      <div class="frow">
        <div class="mf-row"><label class="mfl">Mã QR</label><input type="text" id="gb-ed-ma_qr" class="fc" placeholder="VD: QR-001"></div>
        <div class="mf-row"><label class="mfl">Xưởng</label>
          <select id="gb-ed-xuong" class="fc">
            <option value="">-- Chọn --</option>
            <option>Xuong F0</option><option>Xuong F1</option><option>Xuong F2</option><option>Xuong F3</option>
            <option>Kho van</option><option>Utility</option><option>ADM</option>
          </select>
        </div>
      </div>
      <div class="frow">
        <div class="mf-row"><label class="mfl">Khu vực</label><input type="text" id="gb-ed-khu_vuc" class="fc" placeholder="VD: Line A"></div>
        <div class="mf-row"><label class="mfl">Người phụ trách khu vực</label><input type="text" id="gb-ed-phu_trach" class="fc"></div>
      </div>
      <hr style="margin:12px 0;border-color:var(--border);">
      <div style="font-size:11px;font-weight:700;color:var(--navy);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Thông tin người kiểm tra</div>
      <div class="frow">
        <div class="mf-row"><label class="mfl">Mã NV</label><input type="text" id="gb-ed-ma_nv" class="fc" placeholder="VD: MMB001"></div>
        <div class="mf-row"><label class="mfl">Họ và tên</label><input type="text" id="gb-ed-ho_ten" class="fc"></div>
      </div>
      <div class="mf-row"><label class="mfl">Bộ phận</label><input type="text" id="gb-ed-bo_phan" class="fc"></div>
      <hr style="margin:12px 0;border-color:var(--border);">
      <div style="font-size:11px;font-weight:700;color:var(--navy);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Checklist</div>
      <div class="frow">
        <div class="mf-row"><label class="mfl">Interlock</label>
          <select id="gb-ed-interlock" class="fc"><option value="Đạt">Đạt</option><option value="Không đạt">Không đạt</option></select></div>
        <div class="mf-row"><label class="mfl">Che chắn</label>
          <select id="gb-ed-checan" class="fc"><option value="Đạt">Đạt</option><option value="Không đạt">Không đạt</option></select></div>
      </div>
      <div class="frow">
        <div class="mf-row"><label class="mfl">Tủ điện</label>
          <select id="gb-ed-tudien" class="fc"><option value="Đạt">Đạt</option><option value="Không đạt">Không đạt</option></select></div>
        <div class="mf-row"><label class="mfl">S5 Môi trường</label>
          <select id="gb-ed-s5_moitruong" class="fc"><option value="Đạt">Đạt</option><option value="Không đạt">Không đạt</option></select></div>
      </div>
      <div class="frow">
        <div class="mf-row"><label class="mfl">Sự cố</label>
          <select id="gb-ed-co_su_co" class="fc"><option value="0">Không</option><option value="1">Có</option></select></div>
      </div>
      <div class="mf-row"><label class="mfl">Ghi chú</label><textarea id="gb-ed-ghi_chu" class="fc" rows="3" style="resize:vertical;"></textarea></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-gb-edit')">Hủy</button>
      <button class="btn btn-navy" onclick="saveGembaEdit()">💾 Lưu thay đổi</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: TOP NV ══ -->
<div class="modal-ov" id="modal-top">
  <div class="modal" style="max-width:600px;">
    <div class="modal-hd" style="background:linear-gradient(135deg,var(--navy),var(--denim));"><h3>🏆 Nhân viên báo cáo nhiều nhất</h3><button class="modal-close" onclick="closeModal('modal-top')">&times;</button></div>
    <div class="modal-body">
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:12px;padding:10px 12px;background:var(--bg);border-radius:8px;border:1px solid var(--border);">
        <span style="font-size:11px;font-weight:700;color:var(--navy);">🔎 Lọc:</span>
        <select class="fsel-sm" id="top-type" onchange="renderTop()"><option value="all">Tất cả</option><option value="month">Theo tháng</option></select>
        <select class="fsel-sm" id="top-year" onchange="renderTop()"></select>
        <select class="fsel-sm" id="top-month" onchange="renderTop()" style="display:none;"></select>
        <input type="text" id="top-search" placeholder="🔍 Tìm tên..." oninput="renderTop()"
          style="flex:1;min-width:120px;padding:5px 10px;border:1.5px solid var(--border);border-radius:7px;font-size:12px;background:white;">
      </div>
      <div style="font-size:11px;color:var(--blue);font-weight:600;margin-bottom:8px;" id="top-lbl"></div>
      <div id="top-list"></div>
    </div>
    <div class="modal-footer"><button class="btn btn-ghost" onclick="closeModal('modal-top')">Đóng</button></div>
  </div>
</div>

<!-- ══ MODAL: THÀNH VIÊN ADD/EDIT ══ -->
<div class="modal-ov" id="modal-tv">
  <div class="modal" style="max-width:560px;">
    <div class="modal-hd" style="background:linear-gradient(135deg,var(--navy),var(--denim));">
      <h3 id="tv-modal-title">Thêm thành viên</h3>
      <button class="modal-close" onclick="closeModal('modal-tv')">&times;</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="tv-id">
      <div class="frow">
        <div class="mf-row"><label class="mfl">Mã NV</label><input type="text" id="tv-manv" class="fc" placeholder="VD: MMB001"></div>
        <div class="mf-row"><label class="mfl">Họ và tên <span class="req">*</span></label><input type="text" id="tv-hoten" class="fc" placeholder="Nguyễn Văn A"></div>
      </div>
      <div class="frow">
        <div class="mf-row"><label class="mfl">Bộ phận</label><input type="text" id="tv-bophan" class="fc" placeholder="VD: SHE, Sản xuất..."></div>
        <div class="mf-row"><label class="mfl">Xưởng</label>
          <select id="tv-xuong" class="fc">
            <option value="">-- Chọn --</option>
            <option>Xuong F0</option><option>Xuong F1</option><option>Xuong F2</option><option>Xuong F3</option>
            <option>Kho van</option><option>Utility</option><option>ADM</option>
          </select>
        </div>
      </div>
      <div class="frow">
        <div class="mf-row"><label class="mfl">Chức vụ</label><input type="text" id="tv-chucvu" class="fc" placeholder="VD: Trưởng ca, Kỹ thuật..."></div>
        <div class="mf-row"><label class="mfl">Email</label><input type="email" id="tv-email" class="fc" placeholder="email@company.com"></div>
      </div>
      <div class="mf-row"><label class="mfl">Vai trò <span class="req">*</span></label>
        <select id="tv-vaitro" class="fc">
          <option value="nhanvien">Nhân viên</option>
          <option value="nguoikhacphuc">Người khắc phục</option>
          <option value="quanly">Quản lý</option>
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-tv')">Hủy</button>
      <button class="btn btn-navy" onclick="saveTVMember()">💾 Lưu</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: WEBHOOK EDIT ══ -->
<div class="modal-ov" id="modal-webhook">
  <div class="modal" style="max-width:560px;">
    <div class="modal-hd"><h3 id="wh-modal-title">Cấu hình Webhook</h3><button class="modal-close" onclick="closeModal('modal-webhook')">&times;</button></div>
    <div class="modal-body">
      <input type="hidden" id="wh-id">
      <div style="font-size:12px;color:var(--muted);margin-bottom:12px;" id="wh-desc-text"></div>
      <div class="mf-row">
        <label class="mfl">URL 1 (chính)</label>
        <input type="url" id="wh-url1" class="fc" placeholder="https://...">
      </div>
      <div class="mf-row">
        <label class="mfl">URL 2 (dự phòng)</label>
        <input type="url" id="wh-url2" class="fc" placeholder="https://... (tùy chọn)">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-webhook')">Hủy</button>
      <button class="btn btn-navy" onclick="saveWebhook()">💾 Lưu</button>
    </div>
  </div>
</div>

<div id="lightbox" onclick="closeLightbox()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:500;align-items:center;justify-content:center;cursor:zoom-out;">
  <img id="lb-img" src="" alt="" style="max-width:92vw;max-height:90vh;object-fit:contain;border-radius:6px;">
</div>
<div class="toast" id="toast"></div>
