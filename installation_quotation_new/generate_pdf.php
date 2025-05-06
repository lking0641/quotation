<?php
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

// 2) Compute grand & total labor & final totals with JS-like logic
$grand = 0;
$laborSum = 0;
foreach ($cart as &$item) {
    $width  = floatval($item['width']  ?? 0);
    $height = floatval($item['height'] ?? 0);
    $length = floatval($item['length'] ?? 0);
    $origH  = floatval($item['originalHeight'] ?? 0);
    $origL  = floatval($item['originalLength'] ?? 0);

    // Only include height/length if changed from original
    if ($height === $origH) {
        $height = 0;
    }
    if ($length === $origL) {
        $length = 0;
    }

    $area = ($width + $height + $length) / 1000;
    $cab  = $area * floatval($item['price']) * intval($item['quantity']);
    $labT = $area * floatval($item['labor']) * intval($item['quantity']);

    $grand += $cab + $labT;
    $laborSum += $labT;

    // Store computed values for later use
    $item['computedArea'] = $area;
    $item['cabinetCost']  = $cab;
    $item['laborCost']    = $labT;
}
$final = $grand * (1 - $discount / 100);

// 3) Load template
$template    = __DIR__ . '/../templates/RL_QUOTATION.xlsx';
$spreadsheet = IOFactory::load($template);
$sheet       = $spreadsheet->getActiveSheet();

// 4) Static header fields
$sheet->setCellValue('K6', $clientName);
$sheet->setCellValue('D7', $projectScope);
$sheet->setCellValue('K7', $orderDate);
$sheet->setCellValue('K6', $quotationNo);
$sheet->setCellValue('K7', $contactNo);
$sheet->setCellValue('D7', $address);
$sheet->setCellValue('D6', $installType);
$sheet->setCellValue('B12', $installType);

// 5) Prepare dynamic-row template styles
$startRow            = 13;
$dataTemplateRow     = $startRow + 1;
$headerTemplateRow   = $startRow;
$firstCol            = 'A';
$lastCol             = 'M';

$dataStyle    = $sheet->getStyle("{$firstCol}{$dataTemplateRow}:{$lastCol}{$dataTemplateRow}");
$dataHeight   = $sheet->getRowDimension($dataTemplateRow)->getRowHeight();
$headerStyle  = $sheet->getStyle("{$firstCol}{$headerTemplateRow}:{$lastCol}{$headerTemplateRow}");
$headerHeight = $sheet->getRowDimension($headerTemplateRow)->getRowHeight();

$row     = $startRow;
$prevCat = null;

foreach ($cart as $idx => $it) {
    // Category header
    if ($it['category'] !== $prevCat) {
        if ($prevCat !== null) {
            $sheet->insertNewRowBefore($row, 1);
            $sheet->duplicateStyle($headerStyle, "{$firstCol}{$row}:{$lastCol}{$row}");
            $sheet->getRowDimension($row)->setRowHeight($headerHeight);
        }
        $sheet->setCellValue("B{$row}", strtoupper($it['category']));
        $sheet->mergeCells("B{$row}:{$lastCol}{$row}");
        $h = $sheet->getStyle("B{$row}:{$lastCol}{$row}");
        $h->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');
        $h->getFont()->getColor()->setRGB('FF0000');
        $h->getFont()->setBold(true);

        $prevCat = $it['category'];
        $row++;
    }

    // Data row
    if ($idx > 0) {
        $sheet->insertNewRowBefore($row, 1);
        $sheet->duplicateStyle($dataStyle, "{$firstCol}{$row}:{$lastCol}{$row}");
        $sheet->getRowDimension($row)->setRowHeight($dataHeight);
    }
    $fullRange = "{$firstCol}{$row}:{$lastCol}{$row}";
    $r = $sheet->getStyle($fullRange);
    $r->getFill()->setFillType(Fill::FILL_NONE);
    $r->getFont()->getColor()->setRGB('000000');
    $r->getFont()->setBold(false);

    $sheet->getStyle("E{$row}")->getAlignment()->setWrapText(true);
    $sheet->getRowDimension($row)->setRowHeight(-1);

    // Use computed values
    $area = $it['computedArea'];
    $cab  = $it['cabinetCost'];
    $labT = $it['laborCost'];

    // No.
    $sheet->setCellValue("A{$row}", $idx + 1);
    $sheet->getStyle("A{$row}")->getNumberFormat()->setFormatCode(
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER
    );

    // Name B-D
    $sheet->mergeCells("D{$row}:F{$row}");
    $sheet->setCellValue("D{$row}", $it['name']);

    // Description E: dims
    $sheet->setCellValue("E{$row}", "{$it['width']}×{$it['height']}×{$it['length']}");


     if ($it['category'] !== $prevCat) {
        if ($prevCat !== null) {
            $sheet->insertNewRowBefore($row, 1);
            $sheet->duplicateStyle($headerStyle, "{$firstCol}{$row}:{$lastCol}{$row}");
            $sheet->getRowDimension($row)->setRowHeight($headerHeight);
        }
        $sheet->setCellValue("D{$row}", strtoupper($it['category']));
        $sheet->mergeCells("D{$row}:{$lastCol}{$row}");
        $h = $sheet->getStyle("D{$row}:{$lastCol}{$row}");
        $h->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');
        $h->getFont()->getColor()->setRGB('FF0000');
        $h->getFont()->setBold(true);

        $prevCat = $it['category'];
        $row++;
    }
    // Area, Unit, Qty
    $sheet->setCellValue("D{$row}", round($area, 3));
    $sheet->setCellValue("G{$row}", $it['unit']);
    $sheet->setCellValue("H{$row}", $it['quantity']);
    $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode(
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER
    );

    // Price & costs I-L
    $sheet->setCellValue("I{$row}", round($it['price'], 2));
    $sheet->setCellValue("J{$row}", round($cab, 2));
    $sheet->setCellValue("K{$row}", round($it['labor'], 2));
    //$sheet->setCellValue("L{$row}", round($labT, 2));

    // Total Amount M
    $sheet->setCellValue("L{$row}", round($cab + $labT, 2));
    $sheet->getStyle("L{$row}")->getFont()->setBold(true);
    $sheet->getStyle("I{$row}:L{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

    $row++;
}

// Summary below data rows
$sumStart = $row;
$sheet->setCellValue("A{$sumStart}", 'Grand Total:');
$sheet->setCellValue("M{$sumStart}", round($grand, 2));
$sheet->getStyle("M{$sumStart}")->getFont()->setBold(true);
$sheet->getStyle("M{$sumStart}")->getNumberFormat()->setFormatCode('#,##0.00');

$sheet->setCellValue("A" . ($sumStart+1), 'Total Labor:');
$sheet->setCellValue("M" . ($sumStart+1), round($laborSum, 2));
$sheet->getStyle("M" . ($sumStart+1))->getFont()->setBold(true);
$sheet->getStyle("M" . ($sumStart+1))->getNumberFormat()->setFormatCode('#,##0.00');

$sheet->setCellValue("A" . ($sumStart+2), "Discount ({$discount}%):");
$sheet->setCellValue("M" . ($sumStart+2), round(-$grand * $discount / 100, 2));
$sheet->getStyle("M" . ($sumStart+2))->getFont()->setBold(true);
$sheet->getStyle("M" . ($sumStart+2))->getNumberFormat()->setFormatCode('#,##0.00');

$sheet->setCellValue("A" . ($sumStart+3), 'Final Total:');
$sheet->setCellValue("M" . ($sumStart+3), round($final, 2));
$sheet->getStyle("M" . ($sumStart+3))->getFont()->setBold(true);
$sheet->getStyle("M" . ($sumStart+3))->getNumberFormat()->setFormatCode('#,##0.00');

// Signature
$sheet->setCellValue("A" . ($sumStart+20), strtoupper($employeeName));

// 7) Output to browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header(
    'Content-Disposition: attachment; filename="Quotation_' .
     preg_replace('/\W+/', '_', $clientName) . '.xlsx"'
);
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
