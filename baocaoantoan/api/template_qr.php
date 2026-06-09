<?php
/**
 * Tạo file Excel (.xlsx) mẫu import Xưởng / Khu vực — thuần PHP, không cần thư viện ngoài.
 * GET /api/template_qr.php
 */
require_once __DIR__ . '/../includes/auth.php';
if (!isAdmin()) { http_response_code(403); exit('Forbidden'); }

$zip = new ZipArchive();
$tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_qr_');
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

$zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

$zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"     Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"     Target="worksheets/sheet2.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
  <Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"        Target="styles.xml"/>
</Relationships>');

$zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="XuongKhuVuc" sheetId="1" r:id="rId1"/>
    <sheet name="HuongDan"    sheetId="2" r:id="rId2"/>
  </sheets>
</workbook>');

// ── Shared strings ────────────────────────────────────────────────────────
$strings = [];
function si(string $text): int {
    global $strings;
    $key = array_search($text, $strings, true);
    if ($key !== false) return $key;
    $strings[] = $text;
    return count($strings) - 1;
}

// ── xl/styles.xml ─────────────────────────────────────────────────────────
$zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="6">
    <font><sz val="10"/><name val="Arial"/></font>
    <font><sz val="13"/><b/><color rgb="FFFFFFFF"/><name val="Arial"/></font>
    <font><sz val="11"/><b/><color rgb="FFFFFFFF"/><name val="Arial"/></font>
    <font><sz val="8"/><i/><color rgb="FF6b7280"/><name val="Arial"/></font>
    <font><sz val="9"/><i/><color rgb="FF92400e"/><name val="Arial"/></font>
    <font><sz val="11"/><b/><color rgb="FFFFFFFF"/><name val="Arial"/></font>
  </fonts>
  <fills count="10">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF1e3a5f"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF0d9488"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFe5e7eb"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFf0fdf9"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFfef9c3"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFf9fafb"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFdbeafe"/></patternFill></fill>
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
  <cellXfs count="10">
    <xf numFmtId="0" fontId="0" fillId="0"  borderId="0" xfId="0"><alignment vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="1" fillId="2"  borderId="0" xfId="0"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="2" fillId="3"  borderId="2" xfId="0"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="3" fillId="4"  borderId="1" xfId="0"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="0" fillId="5"  borderId="1" xfId="0"><alignment vertical="center"/></xf>
    <xf numFmtId="0" fontId="0" fillId="6"  borderId="1" xfId="0"><alignment vertical="center"/></xf>
    <xf numFmtId="0" fontId="4" fillId="7"  borderId="0" xfId="0"><alignment vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="5" fillId="2"  borderId="0" xfId="0"><alignment horizontal="left" vertical="center" indent="1"/></xf>
    <xf numFmtId="0" fontId="5" fillId="3"  borderId="0" xfId="0"><alignment horizontal="left" vertical="center" indent="1"/></xf>
    <xf numFmtId="0" fontId="0" fillId="8"  borderId="1" xfId="0"><alignment horizontal="left" vertical="center" indent="2" wrapText="1"/></xf>
  </cellXfs>
</styleSheet>');

// ── Helper cells ──────────────────────────────────────────────────────────
function cellS(string $col, int $row, string $text, int $style = 0): string {
    $idx = si($text);
    return "<c r=\"{$col}{$row}\" t=\"s\" s=\"{$style}\"><v>{$idx}</v></c>";
}
function cellBlank(string $col, int $row, int $style = 0): string {
    return "<c r=\"{$col}{$row}\" s=\"{$style}\"/>";
}

// ── Sheet 1: XuongKhuVuc ─────────────────────────────────────────────────
$cols      = ['A','B','C'];
$colWidths = [30, 30, 25];
$headers   = ['Xuong','Khu vuc','Phu trach'];
$techNames = ['xuong','khu_vuc','phu_trach'];

$sampleData = [
    ['F5 - Tuong ot',  'So che',       'Thanh NC'],
    ['F5 - Tuong ot',  'Dong goi',     'Hung NV'],
    ['F5 - Tuong ot',  'May chiet',    'Minh PT'],
    ['F6 - Nuoc mam',  'Len men',      'Hoa LT'],
    ['F6 - Nuoc mam',  'Loc & loc',    'Hoa LT'],
    ['F6 - Nuoc mam',  'Dong chai',    'Lan NTH'],
    ['Utility',        'Lo hoi',       'Tuan NV'],
    ['Utility',        'Xu ly nuoc',   'Tuan NV'],
    ['Kho van',        'Kho thanh pham','Binh NT'],
    ['Kho van',        'Kho nguyen lieu','Binh NT'],
];

ob_start();
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
echo '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
echo '<sheetViews><sheetView workbookViewId="0"><pane ySplit="4" topLeftCell="A5" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';
echo '<cols>';
foreach ($colWidths as $i => $w) { $n=$i+1; echo "<col min=\"{$n}\" max=\"{$n}\" width=\"{$w}\" customWidth=\"1\"/>"; }
echo '</cols>';
echo '<sheetData>';

// Row 1: Title
echo '<row r="1" ht="32" customHeight="1">';
echo cellS('A', 1, 'DANH SACH XUONG / KHU VUC — IMPORT HE THONG BAO CAO AN TOAN', 1);
echo cellBlank('B', 1, 1); echo cellBlank('C', 1, 1);
echo '</row>';

// Row 2: Note
echo '<row r="2" ht="20" customHeight="1">';
echo cellS('A', 2, 'Cot Xuong va Khu vuc bat buoc. Ma QR se duoc tu dong sinh. Du lieu QR cu se bi XOA va thay bang du lieu moi.', 6);
echo cellBlank('B', 2, 6); echo cellBlank('C', 2, 6);
echo '</row>';

// Row 3: Tech names
echo '<row r="3" ht="16" customHeight="1">';
foreach ($cols as $i => $c) echo cellS($c, 3, $techNames[$i], 3);
echo '</row>';

// Row 4: Headers
echo '<row r="4" ht="26" customHeight="1">';
foreach ($cols as $i => $c) echo cellS($c, 4, $headers[$i], 2);
echo '</row>';

// Sample rows 5-14
foreach ($sampleData as $ri => $row) {
    $r = $ri + 5;
    $bgStyle = ($ri % 2 === 0) ? 4 : 5;
    echo "<row r=\"{$r}\" ht=\"20\" customHeight=\"1\">";
    foreach ($cols as $ci => $c) echo cellS($c, $r, $row[$ci], $bgStyle);
    echo '</row>';
}

// Blank rows 15-35
for ($r = 15; $r <= 35; $r++) {
    $bgStyle = ($r % 2 === 0) ? 5 : 4;
    echo "<row r=\"{$r}\" ht=\"20\" customHeight=\"1\">";
    foreach ($cols as $c) echo cellBlank($c, $r, $bgStyle);
    echo '</row>';
}

echo '</sheetData>';
echo '<mergeCells count="2"><mergeCell ref="A1:C1"/><mergeCell ref="A2:C2"/></mergeCells>';
echo '</worksheet>';
$sheet1 = ob_get_clean();
$zip->addFromString('xl/worksheets/sheet1.xml', $sheet1);

// ── Sheet 2: HuongDan ─────────────────────────────────────────────────────
$guideRows = [
    ['text' => 'HUONG DAN IMPORT XUONG / KHU VUC', 'style' => 7, 'ht' => 34],
    ['text' => '', 'style' => 0, 'ht' => 8],
    ['text' => 'CAU TRUC COT', 'style' => 7, 'ht' => 24],
    ['text' => 'xuong      - Ten xuong (VD: F5 - Tuong ot, F6 - Nuoc mam, Utility, Kho van...). BAT BUOC.', 'style' => 9, 'ht' => 20],
    ['text' => 'khu_vuc    - Ten khu vuc trong xuong (VD: So che, Dong goi, May chiet...). BAT BUOC.', 'style' => 9, 'ht' => 20],
    ['text' => 'phu_trach  - Ho ten nguoi phu trach khu vuc (tuy chon).', 'style' => 9, 'ht' => 20],
    ['text' => '', 'style' => 0, 'ht' => 8],
    ['text' => 'MA QR TU DONG SINH', 'style' => 8, 'ht' => 24],
    ['text' => 'Ma QR duoc tu dong tao tu ten xuong + khu vuc (khong dau).', 'style' => 9, 'ht' => 20],
    ['text' => 'Vi du: Xuong "F5 - Tuong ot" + Khu vuc "So che" => QR-F5-TuongOt-Soche', 'style' => 9, 'ht' => 20],
    ['text' => '', 'style' => 0, 'ht' => 8],
    ['text' => 'LUU Y QUAN TRONG', 'style' => 7, 'ht' => 24],
    ['text' => '1. Khi import: TOAN BO du lieu QR cu se bi XOA va thay bang du lieu trong file nay.', 'style' => 9, 'ht' => 20],
    ['text' => '2. Xoa cac dong vi du (hang 5-14) truoc khi import thuc te.', 'style' => 9, 'ht' => 20],
    ['text' => '3. Moi dong = 1 khu vuc. Mot xuong co nhieu dong (nhieu khu vuc).', 'style' => 9, 'ht' => 20],
    ['text' => '4. File ho tro: .xlsx (Excel 2007+) va .xls (Excel 97-2003).', 'style' => 9, 'ht' => 20],
    ['text' => '', 'style' => 0, 'ht' => 8],
    ['text' => 'CAC BUOC THUC HIEN', 'style' => 8, 'ht' => 24],
    ['text' => 'B1. Tai file mau nay tu nut "Tai mau Excel" tren trang QR Manager.', 'style' => 9, 'ht' => 20],
    ['text' => 'B2. Dien day du danh sach xuong va khu vuc vao file.', 'style' => 9, 'ht' => 20],
    ['text' => 'B3. Luu file, vao Admin > QR Manager > nhan nut "Import Excel".', 'style' => 9, 'ht' => 20],
    ['text' => 'B4. Keo tha hoac chon file, kiem tra preview, nhan "Xac nhan Import".', 'style' => 9, 'ht' => 20],
];

ob_start();
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
echo '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
echo '<cols><col min="1" max="1" width="80" customWidth="1"/></cols>';
echo '<sheetData>';
$merges = [];
foreach ($guideRows as $ri => $rd) {
    $r = $ri + 1;
    echo "<row r=\"{$r}\" ht=\"{$rd['ht']}\" customHeight=\"1\">";
    if ($rd['text'] !== '') echo cellS('A', $r, $rd['text'], $rd['style']);
    else echo cellBlank('A', $r, 0);
    echo '</row>';
    $merges[] = "A{$r}:A{$r}";
}
echo '</sheetData>';
echo '</worksheet>';
$sheet2 = ob_get_clean();
$zip->addFromString('xl/worksheets/sheet2.xml', $sheet2);

// ── xl/sharedStrings.xml ──────────────────────────────────────────────────
$ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
       . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($strings) . '" uniqueCount="' . count($strings) . '">';
foreach ($strings as $s) {
    $ssXml .= '<si><t xml:space="preserve">' . htmlspecialchars($s, ENT_XML1, 'UTF-8') . '</t></si>';
}
$ssXml .= '</sst>';
$zip->addFromString('xl/sharedStrings.xml', $ssXml);
$zip->close();

$filename = 'mau_import_xuong_khuvuc_' . date('Ymd') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($tmpFile));
header('Cache-Control: max-age=0');
readfile($tmpFile);
unlink($tmpFile);
exit;
