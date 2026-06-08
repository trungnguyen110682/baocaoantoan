-- Cập nhật URL webhook khắc phục xong
-- Từ: /webhook/dakhacphuc → /webhook/khacphuc
UPDATE webhook
SET url = 'https://ducvm.baotricf.io.vn/webhook/khacphuc',
    updated_at = NOW()
WHERE ten_key = 'da_khac_phuc';
