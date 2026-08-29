<?php

namespace App\Http\Controllers\NAC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReportExportController extends Controller
{
    public function download(Request $request)
    {
        $user       = Auth::user();
        $isHQ       = $user->region === 'HQ' || $user->authority === RoleEnum::super_admin()->value;
        $userRegion = $isHQ ? '' : $user->region;

        $search   = $request->query('search', '');
        $dateFrom = $request->query('date_from', '');
        $dateTo   = $request->query('date_to', '');
        $type     = $request->query('type', 'pending');

        set_time_limit(300);
        ini_set('memory_limit', '512M');

        return $type === 'ready_for_account'
            ? $this->downloadReadyForAccount($userRegion)
            : $this->downloadPendingTransactions($search, $dateFrom, $dateTo, $userRegion);
    }

    private function downloadPendingTransactions(string $search, string $dateFrom, string $dateTo, string $userRegion)
    {
        $rows = $this->buildExportQuery($search, $dateFrom, $dateTo, $userRegion)->get();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pending Transactions');

        $lastCol = 'M';

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'IBEDC — Ibadan Electricity Distribution Company');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Calibri'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A3C6E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(38);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'Pending Transactions Report — Status not completed, Account No. not assigned');
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E5FA3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(22);

        $searchInfo = $search   ? 'Search: ' . $search                   : 'All Records';
        $dateInfo   = ($dateFrom && $dateTo) ? 'Date: ' . $dateFrom . ' to ' . $dateTo
                    : ($dateFrom ? 'From: ' . $dateFrom : ($dateTo ? 'To: ' . $dateTo : 'All Dates'));
        $genInfo    = 'Generated: ' . now()->format('d M Y, H:i');

        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', "{$searchInfo}    |    {$dateInfo}    |    {$genInfo}    |    Total Records: " . $rows->count());
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF2E5FA3']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8F0FE']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(18);

        $headers = [
            'A' => '#', 'B' => 'Tracking ID', 'C' => 'Customer Name', 'D' => 'MAP ID',
            'E' => 'Region', 'F' => 'Business Hub', 'G' => 'Service Center', 'H' => 'Address',
            'I' => 'Status', 'J' => 'DSS', 'K' => 'DTM Comment', 'L' => 'Billing Comment',
            'M' => 'Paid For Meter',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}4", $label);
        }

        $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF343A40']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF555555']]],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(20);

        $dataRow = 5;
        foreach ($rows as $i => $rec) {
            $s         = (int) $rec->status;
            $lecanNull = empty($rec->lecan_link);
            $eval      = (string) ($rec->evaluated ?? '');

            if (in_array($s, [0, 1]) && $lecanNull)          $statusLabel = 'With Customer';
            elseif (in_array($s, [0, 1]) && !$lecanNull)     $statusLabel = 'With DTM';
            elseif ($s === 2 && $eval === 'no')               $statusLabel = 'Regional Billing';
            elseif ($s === 2 && $eval === 'approved')         $statusLabel = 'Regional Head';
            elseif ($s === 2 && $eval === 'yes')              $statusLabel = 'With Billing';
            elseif ($s === 5)                                 $statusLabel = 'Rejected';
            else                                              $statusLabel = 'Status ' . $s;

            $name    = trim(($rec->firstname ?? '') . ' ' . ($rec->other_name ?? ''));
            $address = trim(($rec->house_no ? $rec->house_no . ', ' : '') . ($rec->full_address ?? ''));

            $sheet->setCellValue("A{$dataRow}", $i + 1);
            $sheet->setCellValue("B{$dataRow}", $rec->tracking_id ?? '');
            $sheet->setCellValue("C{$dataRow}", $name ?: '—');
            $sheet->setCellValue("D{$dataRow}", $rec->map_id ?? '—');
            $sheet->setCellValue("E{$dataRow}", $rec->region ?? '—');
            $sheet->setCellValue("F{$dataRow}", $rec->business_hub ?? '—');
            $sheet->setCellValue("G{$dataRow}", $rec->service_center ?? '—');
            $sheet->setCellValue("H{$dataRow}", $address ?: '—');
            $sheet->setCellValue("I{$dataRow}", $statusLabel);
            $sheet->setCellValue("J{$dataRow}", $rec->dss ?? '—');
            $sheet->setCellValue("K{$dataRow}", $rec->dtm_comment ?? '—');
            $sheet->setCellValue("L{$dataRow}", $rec->billing_comment ?? '—');
            $sheet->setCellValue("M{$dataRow}", $rec->paid_for_meter ?? '—');

            $bgArgb = ($i % 2 === 0) ? 'FFF7F9FF' : 'FFFFFFFF';
            $sheet->getStyle("A{$dataRow}:{$lastCol}{$dataRow}")->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgArgb]],
                'font'      => ['size' => 9],
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE0E0E0']]],
            ]);

            $statusColor = match(true) {
                in_array($s, [0,1]) && $lecanNull  => ['bg' => 'FFE9ECEF', 'fg' => 'FF495057'],
                in_array($s, [0,1]) && !$lecanNull => ['bg' => 'FFFFF3CD', 'fg' => 'FF856404'],
                $s === 2 && $eval === 'no'         => ['bg' => 'FFD1ECF1', 'fg' => 'FF0C5460'],
                $s === 2 && $eval === 'approved'   => ['bg' => 'FFD4EDDA', 'fg' => 'FF155724'],
                $s === 2 && $eval === 'yes'        => ['bg' => 'FFE8F0FE', 'fg' => 'FF224ABE'],
                $s === 5                           => ['bg' => 'FFF8D7DA', 'fg' => 'FF721C24'],
                default                            => ['bg' => 'FFF0F0F0', 'fg' => 'FF6C757D'],
            };

            $sheet->getStyle("I{$dataRow}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $statusColor['bg']]],
                'font' => ['bold' => true, 'color' => ['argb' => $statusColor['fg']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getStyle("A{$dataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension($dataRow)->setRowHeight(16);
            $dataRow++;
        }

        $sheet->mergeCells("A{$dataRow}:{$lastCol}{$dataRow}");
        $sheet->setCellValue("A{$dataRow}", 'IBEDC Confidential — For Internal Use Only');
        $sheet->getStyle("A{$dataRow}")->applyFromArray([
            'font'      => ['italic' => true, 'size' => 8, 'color' => ['argb' => 'FF888888']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0F3FF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $widths = ['A'=>5,'B'=>16,'C'=>22,'D'=>18,'E'=>14,'F'=>22,'G'=>18,'H'=>30,'I'=>18,'J'=>14,'K'=>24,'L'=>24,'M'=>16];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
        $sheet->freezePane('A5');

        return $this->sendDownload($spreadsheet, 'IBEDC_Pending_Transactions_');
    }

    private function downloadReadyForAccount(string $userRegion)
    {
        $rows    = $this->buildPendingMeterQuery($userRegion)->get();
        $lastCol = 'L';

        $headers = [
            'A' => '#', 'B' => 'Tracking ID', 'C' => 'Customer Name', 'D' => 'MAP ID',
            'E' => 'Region', 'F' => 'Business Hub', 'G' => 'Service Center', 'H' => 'Address',
            'I' => 'Status', 'J' => 'DSS', 'K' => 'DTM Comment', 'L' => 'Billing Comment',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ready For Account');

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'IBEDC — Ibadan Electricity Distribution Company');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Calibri'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A3C6E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(38);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'Ready for Account — Customer Pending Meter (Paid: Yes / Old)');
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4B2D8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(22);

        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3',
            'Criteria: status = 2 | evaluated = yes | map_id IS NOT NULL | account_no IS NULL | paid_for_meter IN (Yes, Old)'
            . '    |    Total: ' . $rows->count()
            . '    |    Generated: ' . now()->format('d M Y, H:i'));
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF4B2D8A']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEDE7F6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(18);

        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}4", $label);
        }
        $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4B2D8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF6F42C1']]],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(20);

        $dataRow = 5;
        foreach ($rows as $i => $rec) {
            $name    = trim(($rec->firstname ?? '') . ' ' . ($rec->other_name ?? ''));
            $address = trim(($rec->house_no ? $rec->house_no . ', ' : '') . ($rec->full_address ?? ''));

            $sheet->setCellValue("A{$dataRow}", $i + 1);
            $sheet->setCellValue("B{$dataRow}", $rec->tracking_id ?? '');
            $sheet->setCellValue("C{$dataRow}", $name ?: '—');
            $sheet->setCellValue("D{$dataRow}", $rec->map_id ?? '—');
            $sheet->setCellValue("E{$dataRow}", $rec->region ?? '—');
            $sheet->setCellValue("F{$dataRow}", $rec->business_hub ?? '—');
            $sheet->setCellValue("G{$dataRow}", $rec->service_center ?? '—');
            $sheet->setCellValue("H{$dataRow}", $address ?: '—');
            $sheet->setCellValue("I{$dataRow}", 'Ready for Account');
            $sheet->setCellValue("J{$dataRow}", $rec->dss ?? '—');
            $sheet->setCellValue("K{$dataRow}", $rec->dtm_comment ?? '—');
            $sheet->setCellValue("L{$dataRow}", $rec->billing_comment ?? '—');

            $bgArgb = $i % 2 === 0 ? 'FFF5F0FF' : 'FFFFFFFF';
            $sheet->getStyle("A{$dataRow}:{$lastCol}{$dataRow}")->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgArgb]],
                'font'      => ['size' => 9],
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE0D6F5']]],
            ]);
            $sheet->getStyle("I{$dataRow}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEDE7F6']],
                'font' => ['bold' => true, 'color' => ['argb' => 'FF4B2D8A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getStyle("A{$dataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension($dataRow)->setRowHeight(16);
            $dataRow++;
        }

        $sheet->mergeCells("A{$dataRow}:{$lastCol}{$dataRow}");
        $sheet->setCellValue("A{$dataRow}", 'IBEDC Confidential — For Internal Use Only');
        $sheet->getStyle("A{$dataRow}")->applyFromArray([
            'font'      => ['italic' => true, 'size' => 8, 'color' => ['argb' => 'FF888888']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0EEF8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $widths = ['A'=>5,'B'=>16,'C'=>22,'D'=>18,'E'=>14,'F'=>22,'G'=>18,'H'=>30,'I'=>18,'J'=>14,'K'=>24,'L'=>24];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
        $sheet->freezePane('A5');

        return $this->sendDownload($spreadsheet, 'IBEDC_Ready_For_Account_');
    }

    private function buildExportQuery(string $search, string $dateFrom, string $dateTo, string $userRegion)
    {
        $query = DB::table('upload_houses')
            ->leftJoin('continue_account_creations as cac', 'upload_houses.customer_id', '=', 'cac.customer_id')
            ->leftJoin('account_creations as ac', 'cac.customer_id', '=', 'ac.id')
            ->where('upload_houses.status', '!=', 4)
            ->whereNull('upload_houses.account_no')
            ->whereNull('upload_houses.deleted_at');

        if (!empty($userRegion)) {
            $query->where('upload_houses.region', $userRegion);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('upload_houses.map_id', 'like', '%' . $search . '%')
                  ->orWhere('upload_houses.tracking_id', 'like', '%' . $search . '%');
            });
        }

        if (!empty($dateFrom)) {
            $query->where('upload_houses.created_at', '>=', $dateFrom . ' 00:00:00');
        }

        if (!empty($dateTo)) {
            $query->where('upload_houses.created_at', '<=', $dateTo . ' 23:59:59');
        }

        return $query->select([
            'upload_houses.tracking_id', 'upload_houses.house_no',
            'upload_houses.service_center', 'upload_houses.full_address',
            'upload_houses.region', 'upload_houses.business_hub', 'upload_houses.status',
            'upload_houses.map_id', 'upload_houses.dss', 'upload_houses.lecan_link',
            'upload_houses.evaluated', 'upload_houses.dtm_comment',
            'upload_houses.billing_comment', 'upload_houses.paid_for_meter', 'upload_houses.created_at',
            'ac.firstname', 'ac.other_name',
        ])->orderBy('upload_houses.region')->orderBy('upload_houses.business_hub');
    }

    private function buildPendingMeterQuery(string $userRegion)
    {
        $query = DB::table('upload_houses')
            ->leftJoin('continue_account_creations as cac', 'upload_houses.customer_id', '=', 'cac.customer_id')
            ->leftJoin('account_creations as ac', 'cac.customer_id', '=', 'ac.id')
            ->where('upload_houses.status', 2)
            ->where('upload_houses.evaluated', 'yes')
            ->whereNotNull('upload_houses.map_id')
            ->where('upload_houses.map_id', '!=', '')
            ->whereNull('upload_houses.account_no')
            ->whereIn('upload_houses.paid_for_meter', ['Yes', 'Old'])
            ->whereNull('upload_houses.deleted_at');

        if (!empty($userRegion)) {
            $query->where('upload_houses.region', $userRegion);
        }

        return $query->select([
            'upload_houses.tracking_id', 'upload_houses.house_no',
            'upload_houses.service_center', 'upload_houses.full_address',
            'upload_houses.region', 'upload_houses.business_hub', 'upload_houses.status',
            'upload_houses.map_id', 'upload_houses.dss', 'upload_houses.lecan_link',
            'upload_houses.evaluated', 'upload_houses.dtm_comment',
            'upload_houses.billing_comment', 'upload_houses.created_at',
            'ac.firstname', 'ac.other_name',
        ])->orderBy('upload_houses.region')->orderBy('upload_houses.business_hub');
    }

    private function sendDownload(Spreadsheet $spreadsheet, string $prefix)
    {
        $filename = $prefix . now()->format('Ymd_His') . '.xlsx';
        $tmpPath  = tempnam(sys_get_temp_dir(), 'ibedc_') . '.xlsx';

        (new Xlsx($spreadsheet))->save($tmpPath);

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
