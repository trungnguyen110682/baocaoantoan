<!-- ══ PAGE: CÀI ĐẶT ══ -->
<div class="tab-panel" id="page-settings">
  <div class="page-header">
    <div class="page-header-left"><div class="page-title">⚙️ Cài đặt</div><div class="page-sub">Webhook & mật khẩu</div></div>
  </div>
  <div class="page-body">

    <div class="set-section">
      <div class="set-section-title">🔗 Cấu hình Webhook (N8N)</div>
      <div id="webhook-list"><div class="empty"><p>Đang tải...</p></div></div>
    </div>

    <div class="set-section">
      <div class="set-section-title">🔒 Đổi mật khẩu</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" class="chart-grid-2">
        <div>
          <div style="font-size:13px;font-weight:700;color:var(--navy);margin-bottom:10px;">Mật khẩu Admin</div>
          <div class="mf-row"><label class="mfl">Mật khẩu mới</label><input type="password" id="pw-admin-new" class="fc" placeholder="Tối thiểu 4 ký tự"></div>
          <div class="mf-row"><label class="mfl">Xác nhận lại</label><input type="password" id="pw-admin-confirm" class="fc" placeholder="Nhập lại mật khẩu"></div>
          <button class="btn btn-navy" onclick="changePassword('admin')" style="width:100%;">💾 Lưu mật khẩu Admin</button>
        </div>
        <div>
          <div style="font-size:13px;font-weight:700;color:var(--denim);margin-bottom:10px;">Mật khẩu Viewer</div>
          <div class="mf-row"><label class="mfl">Mật khẩu mới</label><input type="password" id="pw-viewer-new" class="fc" placeholder="Tối thiểu 4 ký tự"></div>
          <div class="mf-row"><label class="mfl">Xác nhận lại</label><input type="password" id="pw-viewer-confirm" class="fc" placeholder="Nhập lại mật khẩu"></div>
          <button class="btn btn-or" onclick="changePassword('viewer')" style="width:100%;">💾 Lưu mật khẩu Viewer</button>
        </div>
      </div>
    </div>
  </div>
</div>
