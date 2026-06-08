<?php
/**
 * Tạo file Excel (.xlsx) mẫu import thành viên — thuần PHP, không cần thư viện ngoài.
 * GET /api/template.php?type=thanhvien
 */
require_once __DIR__ . '/../includes/auth.php';
if (!isAdmin()) { http_response_code(403); exit('Forbidden'); }

$type = $_GET['type'] ?? 'thanhvien';
if ($type !== 'thanhvien') { http_response_code(400); exit('Unknown type'); }

// ── Build XLSX (ZIP of XML files) ──────────────────────────────────────────

$zip = new ZipArchive();
$tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_');
$zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

// ── [Content_Types].xml ───────────────────────────────────────────────────
$zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml"  ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml"           ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml"  ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/worksheets/sheet2.xml"  ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/sharedStrings.xml"      ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
  <Override PartName="/xl/styles.xml"             ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>');

// ── _rels/.rels ───────────────────────────────────────────────────────────
$zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

// ── xl/_rels/workbook.xml.rels ────────────────────────────────────────────
$zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"     Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"     Target="worksheets/sheet2.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
  <Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"        Target="styles.xml"/>
</Relationships>');

// ── xl/workbook.xml ───────────────────────────────────────────────────────
$zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="DanhSachNhanVien" sheetId="1" r:id="rId1"/>
    <sheet name="HuongDan"         sheetId="2" r:id="rId2"/>
  </sheets>
</workbook>');

// ── Shared strings ────────────────────────────────────────────────────────
// Tất cả text lưu ở đây, cell dùng index.
$strings = [];
function si(string $text): int {
    global $strings;
    $key = array_search($text, $strings, true);
    if ($key !== false) return $key;
    $strings[] = $text;
    return count($strings) - 1;
}

// ── Style indexes (định nghĩa trong styles.xml) ───────────────────────────
// 0 = default
// 1 = header navy (trắng, bold, nền navy)
// 2 = header teal (trắng, bold, nền teal)
// 3 = field label (gray, italic, nền xám nhạt)
// 4 = data row odd (nền xanh nhạt)
// 5 = data row even (trắng)
// 6 = role nhanvien (xanh dương nhạt, bold)
// 7 = role nguoikhacphuc (xanh lá nhạt, bold)
// 8 = role quanly (cam nhạt, bold)
// 9 = note yellow (cam đậm, italic, nền vàng nhạt)
// 10 = section header huongdan (trắng, bold, nền navy)
// 11 = section header teal
// 12 = section header blue
// 13 = guide item (nền trắng xám nhạt)

// ── xl/styles.xml ─────────────────────────────────────────────────────────
$stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="10">
    <font><sz val="10"/><name val="Arial"/></font>
    <font><sz val="13"/><b/><color rgb="FFFFFFFF"/><name val="Arial"/></font>
    <font><sz val="11"/><b/><color rgb="FFFFFFFF"/><name val="Arial"/></font>
    <font><sz val="8"/><i/><color rgb="FF6b7280"/><name val="Arial"/></font>
    <font><sz val="10"/><color rgb="FFFFFFFF"/><name val="Arial"/></font>
    <font><sz val="10"/><b/><color rgb="FF1d4ed8"/><name val="Arial"/></font>
    <font><sz val="10"/><b/><color rgb="FF065f46"/><name val="Arial"/></font>
    <font><sz val="10"/><b/><color rgb="FF9a3412"/><name val="Arial"/></font>
    <font><sz val="9"/><i/><color rgb="FF92400e"/><name val="Arial"/></font>
    <font><sz val="11"/><b/><color rgb="FFFFFFFF"/><name val="Arial"/></font>
  </fonts>
  <fills count="16">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF1e3a5f"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF0d9488"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFe5e7eb"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFf0fdf9"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFdbeafe"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFd1fae5"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFffedd5"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFfef9c3"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFf9fafb"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF0369a1"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF374151"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF6b7280"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFf8fafc"/></patternFill></fill>
  </fills>
  <borders count="3">
    <border><left/><right/><top/><bottom/></border>
    <border>
      <left style="thin"><color rgb="FFd1d5db"/></left>
      <right style="thin"><color rgb="FFd1d5db"/></right>
      <top style="thin"><color rgb="FFd1d5db"/></top>
      <bottom style="thin"><color rgb="FFd1d5db"/></bottom>
    </border>
    <border>
      <left style="thin"><color rgb="FF0f766e"/></left>
      <right style="thin"><color rgb="FF0f766e"/></right>
      <top style="thin"><color rgb="FF0f766e"/></top>
      <bottom style="thin"><color rgb="FF0f766e"/></bottom>
    </border>
  </borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="14">
    <xf numFmtId="0" fontId="0" fillId="0"  borderId="0" xfId="0"><alignment vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="1" fillId="2"  borderId="0" xfId="0"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="2" fillId="3"  borderId="2" xfId="0"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="3" fillId="4"  borderId="1" xfId="0"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="0" fillId="5"  borderId="1" xfId="0"><alignment vertical="center"/></xf>
    <xf numFmtId="0" fontId="0" fillId="6"  borderId="1" xfId="0"><alignment vertical="center"/></xf>
    <xf numFmtId="0" fontId="5" fillId="7"  borderId="1" xfId="0"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="6" fillId="8"  borderId="1" xfId="0"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="7" fillId="9"  borderId="1" xfId="0"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="8" fillId="10" borderId="0" xfId="0"><alignment vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="9" fillId="2"  borderId="0" xfId="0"><alignment horizontal="left"  vertical="center" indent="1"/></xf>
    <xf numFmtId="0" fontId="9" fillId="3"  borderId="0" xfId="0"><alignment horizontal="left"  vertical="center" indent="1"/></xf>
    <xf numFmtId="0" fontId="9" fillId="12" borderId="0" xfId="0"><alignment horizontal="left"  vertical="center" indent="1"/></xf>
    <xf numFmtId="0" fontId="0" fillId="11" borderId="1" xfId="0"><alignment horizontal="left"  vertical="center" indent="2" wrapText="1"/></xf>
  </cellXfs>
</styleSheet>';
$zip->addFromString('xl/styles.xml', $stylesXml);

// ── Helper: tạo cell text (shared string) ────────────────────────────────
function cellS(string $col, int $row, string $text, int $style = 0): string {
    $idx = si($text);
    return "<c r=\"{$col}{$row}\" t=\"s\" s=\"{$style}\"><v>{$idx}</v></c>";
}
// Cell trống với style
function cellBlank(string $col, int $row, int $style = 0): string {
    return "<c r=\"{$col}{$row}\" s=\"{$style}\"/>";
}

// ── Sheet 1: DanhSachNhanVien ─────────────────────────────────────────────
// Cấu hình cột
$cols = ['A','B','C','D','E','F','G'];
$colWidths   = [16, 26, 24, 14, 20, 30, 18];
$fieldNames  = ['ma_nv','ho_ten (*)','bo_phan','xuong','chuc_vu','email','vai_tro'];
$fieldLabels = ['Ma NV','Ho va Ten *','Bo Phan','Xuong','Chuc Vu','Email','Vai Tro'];

$sampleData = [
    ['17MB01275','Nguyen Van An',  'SHE',               'Xuong F2','Nhan vien SHE', 'nva@msc.masangroup.com','nhanvien'],
    ['17MB01276','Tran Thi Bich',  'San xuat - Noodle', 'Xuong F3','Bao tri F3',    'ttb@msc.masangroup.com','nguoikhacphuc'],
    ['17MB01277','Le Van Cuong',   'SHE',               'Xuong F1','TBP Bao tri',   'lvc@msc.masangroup.com','quanly'],
    ['17MB01278','Pham Thi Dung',  'San xuat - Pho',    'Xuong F3','Truong ca',     'ptd@msc.masangroup.com','nhanvien'],
    ['17MB01279','Hoang Van Em',   'Kho',               'Kho van', 'TBP Kho',       'hve@msc.masangroup.com','nguoikhacphuc'],
];

$roleStyle = ['nhanvien'=>6,'nguoikhacphuc'=>7,'quanly'=>8];

ob_start();
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
echo '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

// sheetViews phải đứng trước cols và sheetData
echo '<sheetViews><sheetView workbookViewId="0"><pane ySplit="4" topLeftCell="A5" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';

// Column widths
echo '<cols>';
foreach ($colWidths as $i => $w) {
    $n = $i + 1;
    echo "<col min=\"{$n}\" max=\"{$n}\" width=\"{$w}\" customWidth=\"1\"/>";
}
echo '</cols>';

echo '<sheetData>';

// Row 1: Title (merge handled by mergeCells below)
echo '<row r="1" ht="32" customHeight="1">';
echo cellS('A', 1, 'DANH SACH NHAN VIEN — IMPORT HE THONG BAO CAO AN TOAN MMB', 1);
foreach (['B','C','D','E','F','G'] as $c) echo cellBlank($c, 1, 1);
echo '</row>';

// Row 2: Note
echo '<row r="2" ht="20" customHeight="1">';
echo cellS('A', 2, 'Cot ho_ten bat buoc (*). vai_tro: nhanvien / nguoikhacphuc / quanly. Xoa dong vi du (hang 5-9) truoc khi import.', 9);
foreach (['B','C','D','E','F','G'] as $c) echo cellBlank($c, 2, 9);
echo '</row>';

// Row 3: Field names (technical)
echo '<row r="3" ht="16" customHeight="1">';
foreach ($cols as $i => $c) echo cellS($c, 3, $fieldNames[$i], 3);
echo '</row>';

// Row 4: Display headers
echo '<row r="4" ht="26" customHeight="1">';
foreach ($cols as $i => $c) echo cellS($c, 4, $fieldLabels[$i], 2);
echo '</row>';

// Rows 5-9: Sample data
foreach ($sampleData as $ri => $row) {
    $r = $ri + 5;
    $bgStyle = ($ri % 2 === 0) ? 4 : 5; // odd=teal-light, even=white
    echo "<row r=\"{$r}\" ht=\"20\" customHeight=\"1\">";
    foreach ($cols as $ci => $c) {
        $val = $row[$ci];
        if ($c === 'G') {
            echo cellS($c, $r, $val, $roleStyle[$val] ?? 5);
        } else {
            echo cellS($c, $r, $val, $bgStyle);
        }
    }
    echo '</row>';
}

// Rows 10-25: blank input rows
for ($r = 10; $r <= 25; $r++) {
    $bgStyle = ($r % 2 === 0) ? 5 : 4;
    echo "<row r=\"{$r}\" ht=\"20\" customHeight=\"1\">";
    foreach ($cols as $c) echo cellBlank($c, $r, $bgStyle);
    echo '</row>';
}

echo '</sheetData>';

// Merge cells
echo '<mergeCells count="2">';
echo '<mergeCell ref="A1:G1"/>';
echo '<mergeCell ref="A2:G2"/>';
echo '</mergeCells>';

echo '</worksheet>';
$sheet1 = ob_get_clean();
$zip->addFromString('xl/worksheets/sheet1.xml', $sheet1);

// ── Sheet 2: HuongDan ─────────────────────────────────────────────────────
$guideRows = [];

// Title
$guideRows[] = ['text' => 'HUONG DAN SU DUNG FILE IMPORT THANH VIEN', 'style' => 10, 'ht' => 34, 'merge' => true];
$guideRows[] = ['text' => '', 'style' => 0, 'ht' => 8, 'merge' => true];

// Section 1
$guideRows[] = ['text' => 'CAU TRUC COT', 'style' => 10, 'ht' => 24, 'merge' => true];
$items1 = [
    'ma_nv        - Ma nhan vien (VD: 17MB01275). Dung de cap nhat neu da ton tai trong he thong.',
    'ho_ten (*)   - Ho va ten day du. BAT BUOC. Dong thieu cot nay se bi bo qua khi import.',
    'bo_phan      - Bo phan (VD: SHE, San xuat - Noodle, Kho, ADM, Utility...)',
    'xuong        - Xuong (VD: Xuong F0, F1, F2, F3, Kho van, Utility, ADM)',
    'chuc_vu      - Chuc vu / chuc danh (VD: Truong ca, Bao tri F2, TBP Kho...)',
    'email        - Dia chi email (VD: abc@msc.masangroup.com)',
    'vai_tro      - Vai tro trong he thong. Xem gia tri hop le o phan duoi.',
];
foreach ($items1 as $item) $guideRows[] = ['text' => $item, 'style' => 13, 'ht' => 20, 'merge' => true];
$guideRows[] = ['text' => '', 'style' => 0, 'ht' => 8, 'merge' => true];

// Section 2
$guideRows[] = ['text' => 'GIA TRI HOP LE CHO COT vai_tro', 'style' => 11, 'ht' => 24, 'merge' => true];
$items2 = [
    'nhanvien        -> Nhan vien thuong (mac dinh neu de trong)',
    'nguoikhacphuc   -> Nguoi khac phuc su co — co ten trong danh sach phan cong',
    'quanly          -> Quan ly — co quyen xem bao cao va thong ke',
];
foreach ($items2 as $item) $guideRows[] = ['text' => $item, 'style' => 13, 'ht' => 20, 'merge' => true];
$guideRows[] = ['text' => '', 'style' => 0, 'ht' => 8, 'merge' => true];

// Section 3
$guideRows[] = ['text' => 'QUY TAC KHI IMPORT', 'style' => 10, 'ht' => 24, 'merge' => true];
$items3 = [
    '1. Dong tieu de (hang 4) khong duoc xoa.',
    '2. Xoa cac dong vi du (hang 5-9) truoc khi import thuc te.',
    '3. Neu ma_nv da ton tai: chon "Cap nhat" de ghi de, hoac "Bo qua" de giu nguyen.',
    '4. He thong chap nhan ten cot tieng Anh (ma_nv) lan tieng Viet (Ho ten, Bo phan...).',
    '5. Khong can xoa dong trong o cuoi file — he thong tu bo qua.',
    '6. File ho tro: .xlsx (Excel 2007+) va .xls (Excel 97-2003).',
];
foreach ($items3 as $item) $guideRows[] = ['text' => $item, 'style' => 13, 'ht' => 20, 'merge' => true];
$guideRows[] = ['text' => '', 'style' => 0, 'ht' => 8, 'merge' => true];

// Section 4
$guideRows[] = ['text' => 'CAC BUOC THUC HIEN', 'style' => 12, 'ht' => 24, 'merge' => true];
$items4 = [
    'B1. Tai file mau nay tu nut "Tai mau Excel" tren giao dien Admin.',
    'B2. Dien du lieu vao cac dong trong (hang 10 tro xuong), hoac paste tu file khac.',
    'B3. Xoa cac dong vi du (hang 5-9) neu khong can.',
    'B4. Luu file, vao Admin > Thanh vien > nhan nut "Import Excel".',
    'B5. Keo tha hoac chon file da luu, kiem tra preview, chon che do xu ly trung.',
    'B6. Nhan "Xac nhan Import" de hoan tat.',
];
foreach ($items4 as $item) $guideRows[] = ['text' => $item, 'style' => 13, 'ht' => 20, 'merge' => true];

ob_start();
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
echo '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
echo '<cols><col min="1" max="1" width="72" customWidth="1"/><col min="2" max="4" width="5" customWidth="1"/></cols>';
echo '<sheetData>';

$merges = [];
foreach ($guideRows as $ri => $rd) {
    $r = $ri + 1;
    $ht = $rd['ht'];
    echo "<row r=\"{$r}\" ht=\"{$ht}\" customHeight=\"1\">";
    if ($rd['text'] !== '') {
        echo cellS('A', $r, $rd['text'], $rd['style']);
    } else {
        echo cellBlank('A', $r, 0);
    }
    foreach (['B','C','D'] as $c) echo cellBlank($c, $r, 0);
    echo '</row>';
    if ($rd['merge']) $merges[] = "A{$r}:D{$r}";
}

echo '</sheetData>';
echo '<mergeCells count="' . count($merges) . '">';
foreach ($merges as $m) echo "<mergeCell ref=\"{$m}\"/>";
echo '</mergeCells>';
echo '</worksheet>';
$sheet2 = ob_get_clean();
$zip->addFromString('xl/worksheets/sheet2.xml', $sheet2);

// ── xl/sharedStrings.xml ──────────────────────────────────────────────────
// (strings được build khi gọi si() ở trên — nhưng cần gọi TRƯỚC khi build sharedStrings)
// Vấn đề: si() được gọi bên trong ob_start() nên strings[] đã đầy đủ rồi.
$ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
       . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($strings) . '" uniqueCount="' . count($strings) . '">';
foreach ($strings as $s) {
    $ssXml .= '<si><t xml:space="preserve">' . htmlspecialchars($s, ENT_XML1, 'UTF-8') . '</t></si>';
}
$ssXml .= '</sst>';
$zip->addFromString('xl/sharedStrings.xml', $ssXml);

$zip->close();

// ── Gửi file về client ────────────────────────────────────────────────────
$filename = 'mau_import_thanhvien_' . date('Ymd') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($tmpFile));
header('Cache-Control: max-age=0');
readfile($tmpFile);
unlink($tmpFile);
exit;
