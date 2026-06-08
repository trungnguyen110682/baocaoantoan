-- Migration: cập nhật giá trị các dropdown theo danh mục mới
-- Chạy file này 1 lần trên DB live để đồng bộ data cũ

-- ── severity: Nguy hiểm tiềm ẩn → Suýt bị ──────────────────────────────────
UPDATE baocao SET severity = 'Suýt bị' WHERE severity = 'Nguy hiểm tiềm ẩn';

-- ── golden_rule ──────────────────────────────────────────────────────────────
UPDATE baocao SET golden_rule = 'AT điện'           WHERE golden_rule = 'An toàn điện';
UPDATE baocao SET golden_rule = 'AT chuyển động'    WHERE golden_rule = 'Chuyển động';
UPDATE baocao SET golden_rule = 'AT hóa chất'       WHERE golden_rule = 'Hóa chất';
UPDATE baocao SET golden_rule = 'AT xe nâng'        WHERE golden_rule = 'Nâng hạ';
UPDATE baocao SET golden_rule = 'AT trên cao'       WHERE golden_rule = 'Làm việc trên cao';
UPDATE baocao SET golden_rule = 'AT không gian kín' WHERE golden_rule = 'Không gian hạn chế';

-- ── category ─────────────────────────────────────────────────────────────────
UPDATE baocao SET category = 'Xe nâng' WHERE category = 'Nâng hạ';
UPDATE baocao SET category = 'Khác'    WHERE category IN ('Điện', 'Hóa chất');

-- ── area ─────────────────────────────────────────────────────────────────────
UPDATE baocao SET area = 'Utility' WHERE area = 'Tiện ích';
UPDATE baocao SET area = 'Khác'    WHERE area = 'Văn phòng';
