<?php
// generate_excel.php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// 1) Grab POST data
$clientName   = $_POST['client_name']    ?? '';
$projectScope = $_POST['project_scope']  ?? '';
$orderDate    = $_POST['order_date']     ?? '';
$quotationNo  = $_POST['quotation_no']   ?? '';
$installType  = $_POST['install_type']   ?? '';
$contactNo    = $_POST['contact_no']     ?? '';
$address      = $_POST['address']        ?? '';
$employeeName = $_POST['employee_name']  ?? '';
$discount     = floatval($_POST['discount'] ?? 0);
$cart         = json_decode($_POST['cart_data'] ?? '[]', true);

// 2) Compute subtotals & grand total
$grand = 0;
foreach ($cart as &$item) {
    $price     = floatval($item['price']);
    $qty       = intval($item['quantity']);
    $subtotal  = $price * $qty;
    $item['subtotal'] = $subtotal;
    $grand   += $subtotal;
}
$final = $grand * (1 - $discount / 100.0);

// 3) Load the NOBLEHOME template
$template = __DIR__ . '/../templates/NOBLEHOME_QUOTATION.xlsx';
if (!file_exists($template)) {
    die("Template not found at: $template");
}
$spreadsheet = IOFactory::load($template);
$sheet       = $spreadsheet->getActiveSheet();

// 4) Header fields
$sheet->setCellValue('D5',  $clientName);
$sheet->setCellValue('D7',  $projectScope);
$sheet->setCellValue('K5',  $orderDate);
$sheet->setCellValue('K6',  $quotationNo);
$sheet->setCellValue('K7',  $contactNo);
$sheet->setCellValue('K8',  $address);
$sheet->setCellValue('A11', $installType);

// 5) Dynamic rows setup
$startRow        = 14;
$dataTemplateRow = $startRow;
$firstCol        = 'A';
$lastCol         = 'K';  // now uppercase K

// Grab the style and height from the template row
$dataStyle  = $sheet->getStyle("{$firstCol}{$dataTemplateRow}:{$lastCol}{$dataTemplateRow}");
$dataHeight = $sheet->getRowDimension($dataTemplateRow)->getRowHeight();

$row = $startRow;
foreach ($cart as $idx => $it) {
    if ($idx > 0) {
        $sheet->insertNewRowBefore($row, 1);
        $sheet->duplicateStyle($dataStyle, "{$firstCol}{$row}:{$lastCol}{$row}");
        $sheet->getRowDimension($row)->setRowHeight($dataHeight);
    }

    // Clear any fill & reset font
    $style = $sheet->getStyle("{$firstCol}{$row}:{$lastCol}{$row}");
    $style->getFill()->setFillType(Fill::FILL_NONE);
    $style->getFont()->setBold(false)->getColor()->setRGB('000000');

    // Column A: No.
    $sheet->setCellValue("A{$row}", $idx + 1);

    // Columns B–D: Name (merged)
    $sheet->mergeCells("B{$row}:D{$row}");
    $sheet->setCellValue("B{$row}", $it['name']);

    // Column E: Description
    $sheet->setCellValue("E{$row}", $it['description']);

    // Column F: Unit
    $sheet->setCellValue("F{$row}", $it['unit']);

    // Column G: Quantity
    $sheet->setCellValue("G{$row}", $it['quantity']);

    // Column H: Unit Price
    $sheet->setCellValue("H{$row}", round($it['price'], 2));

    // Columns J–K: Subtotal (merged)
    $sheet->mergeCells("J{$row}:K{$row}");
    $sheet->setCellValue("J{$row}", round($it['subtotal'], 2));

    $row++;
}

// 6) Summary
$sheet->setCellValue("A{$row}", 'Grand Total:');
$sheet->setCellValue("J{$row}", round($grand, 2));
$sheet->getStyle("J{$row}")->getFont()->setBold(true);
$row++;

$sheet->setCellValue("A{$row}", "Discount ({$discount}%):");
$sheet->setCellValue("J{$row}", round(-$grand * $discount / 100, 2));
$sheet->getStyle("J{$row}")->getFont()->setBold(true);
$row++;

$sheet->setCellValue("A{$row}", 'Final Total:');
$sheet->setCellValue("J{$row}", round($final, 2));
$sheet->getStyle("J{$row}")->getFont()->setBold(true);
$row += 16;

// Signature line
$sheet->setCellValue("A{$row}", strtoupper($employeeName));

// 7) Send download with client name in filename
// Sanitize client name for filename
$cleanName = preg_replace('/[^A-Za-z0-9]+/', '_', strtoupper($clientName));
$filename  = "Quotation_{$cleanName}.xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
