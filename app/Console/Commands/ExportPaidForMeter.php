<?php

namespace App\Console\Commands;

use App\Mail\PaidForMeterReportMail;
use App\Models\NAC\UploadHouses;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportPaidForMeter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-paid-for-meter
                            {--no-mail : Only generate the Excel file, do not send the email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export status 2 upload houses that paid for meter but have no account number to Excel (summary, With Billing, With Regional Billing, With Regional Head) and email the business hub summary';

    private const RECIPIENTS = [
        'victor.ogiogio@ibedc.com',
        'Eyinade.Wintope@ibedc.com',
        'basirat.opoola@ibedc.com',
        'grace.odejayi@ibedc.com',
        'emmanuel.adeoye@ibedc.com',
        'Waliu.Alamu@ibedc.com',
        'omoniyi.ajao@ibedc.com',
        'Oluyemisi.Adegbola@ibedc.com',
        'Olanike.Olabode@ibedc.com',
        'Olubunmi.Aina@ibedc.com',
        'Azeez.Aderibigbe@ibedc.com',
        'janet.dairo@ibedc.com',
        'Olanike.adebayo@ibedc.com',
        'surajudeen.olagunju@ibedc.com',
         'Daniel.marcus@ibedc.com',
        'Opeoluwa.adewole@ibedc.com',
        'tejumade.adediran@ibedc.com',
        'Olutoye.ogunleye@ibedc.com'

    ];

    // Summary key => [evaluated value, sheet name]. All sheets are status = 2.
    private const STAGES = [
        'with_billing'          => ['evaluated' => 'yes',      'sheet' => 'With Billing'],
        'with_regional_billing' => ['evaluated' => 'no',       'sheet' => 'With Regional Billing'],
        'with_regional_head'    => ['evaluated' => 'approved', 'sheet' => 'With Regional Head'],
    ];

    private const DETAIL_HEADERS = [
        'A' => '#', 'B' => 'Tracking ID', 'C' => 'Account No', 'D' => 'Customer Name', 'E' => 'MAP ID',
        'F' => 'Region', 'G' => 'Business Hub', 'H' => 'Service Center', 'I' => 'Address',
        'J' => 'Status', 'K' => 'Evaluated', 'L' => 'DSS', 'M' => 'DTM Comment',
        'N' => 'Billing Comment', 'O' => 'Date Created',
    ];

    private const DETAIL_WIDTHS = [
        'A' => 6, 'B' => 18, 'C' => 18, 'D' => 26, 'E' => 18, 'F' => 14, 'G' => 20, 'H' => 20,
        'I' => 40, 'J' => 18, 'K' => 12, 'L' => 18, 'M' => 30, 'N' => 30, 'O' => 18,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '1024M');

        $this->info('🚀 STARTING PAID FOR METER EXPORT ***********************');

        $rows = $this->baseQuery()->get()->each(function ($r) {
            // Region/hub casing is inconsistent in the data (e.g. "Ogun West" vs "Ogun west").
            $r->region       = $this->normalizeName($r->region);
            $r->business_hub = $this->normalizeName($r->business_hub);
        });

        $this->info('📊 Total Records Found: ' . $rows->count());

        if ($rows->isEmpty()) {
            $this->info('✅ Nothing to export. Exiting.');

            return Command::SUCCESS;
        }

        [$summary, $totals] = $this->buildSummary($rows);

        $spreadsheet = new Spreadsheet();

        $this->writeSummarySheet($spreadsheet->getActiveSheet(), $summary, $totals);

        $sheetCounts = [];
        foreach (self::STAGES as $stage) {
            $stageRows = $rows->filter(fn ($r) => $this->evaluated($r) === $stage['evaluated'])->values();
            $sheetCounts[] = "{$stage['sheet']}: {$stageRows->count()}";

            $this->writeDetailSheet(
                $spreadsheet->createSheet(),
                $stage['sheet'],
                "Paid For Meter — {$stage['sheet']} (Status = 2, Evaluated = {$stage['evaluated']}), Account No. not assigned",
                $stageRows
            );
        }

        $spreadsheet->setActiveSheetIndex(0);

        $dir = storage_path('app/reports/paid_for_meter');
        File::ensureDirectoryExists($dir);

        $filePath = $dir . '/IBEDC_Paid_For_Meter_' . now()->format('Ymd_His') . '.xlsx';
        (new Xlsx($spreadsheet))->save($filePath);

        $spreadsheet->disconnectWorksheets();

        $this->info("💾 Excel saved: {$filePath}");
        $this->info('   Summary hubs: ' . count($summary) . ' | ' . implode(' | ', $sheetCounts));

        if ($this->option('no-mail')) {
            $this->info('📭 --no-mail set, skipping email.');

            return Command::SUCCESS;
        }

        try {
            Mail::mailer('alerts')->to(self::RECIPIENTS)
                ->send(new PaidForMeterReportMail($summary, $totals, $filePath));

            $this->info('📧 Report emailed to: ' . implode(', ', self::RECIPIENTS));
        } catch (\Throwable $e) {
            $this->error('❌ Failed to send report email: ' . $e->getMessage());

            return Command::FAILURE;
        }

        $this->info('✅ PAID FOR METER EXPORT COMPLETE');

        return Command::SUCCESS;
    }

    private function baseQuery()
    {
        return UploadHouses::query()
            ->leftJoin('continue_account_creations as cac', 'upload_houses.customer_id', '=', 'cac.customer_id')
            ->leftJoin('account_creations as ac', 'cac.customer_id', '=', 'ac.id')
            ->where('upload_houses.paid_for_meter', 'Yes')
            ->whereNull('upload_houses.account_no')
            ->where('upload_houses.status', 2)
            ->whereIn('upload_houses.evaluated', array_column(self::STAGES, 'evaluated'))
            ->select([
                'upload_houses.tracking_id', 'upload_houses.account_no', 'upload_houses.house_no', 'upload_houses.full_address',
                'upload_houses.region', 'upload_houses.business_hub', 'upload_houses.service_center',
                'upload_houses.status', 'upload_houses.evaluated', 'upload_houses.lecan_link',
                'upload_houses.map_id', 'upload_houses.dss', 'upload_houses.dtm_comment',
                'upload_houses.billing_comment', 'upload_houses.created_at',
                'ac.firstname', 'ac.other_name',
            ])
            ->orderBy('upload_houses.region')
            ->orderBy('upload_houses.business_hub');
    }

    /**
     * Group records by region + business hub.
     */
    private function buildSummary(Collection $rows): array
    {
        $summary = $rows
            ->groupBy(fn ($r) => ($r->region ?? '') . '|' . ($r->business_hub ?? ''))
            ->map(function (Collection $group) {
                $first = $group->first();

                $row = [
                    'region'       => (string) ($first->region ?? ''),
                    'business_hub' => (string) ($first->business_hub ?? ''),
                    'total'        => $group->count(),
                ];

                foreach (self::STAGES as $key => $stage) {
                    $row[$key] = $group->filter(fn ($r) => $this->evaluated($r) === $stage['evaluated'])->count();
                }

                return $row;
            })
            ->sortBy([['region', 'asc'], ['business_hub', 'asc']])
            ->values()
            ->all();

        $totals = [];
        foreach (array_merge(['total'], array_keys(self::STAGES)) as $key) {
            $totals[$key] = array_sum(array_column($summary, $key));
        }

        return [$summary, $totals];
    }

    private function writeSummarySheet(Worksheet $sheet, array $summary, array $totals): void
    {
        $sheet->setTitle('Summary');
        $lastCol = 'G';

        $this->writeTitleRows($sheet, $lastCol, 'Paid For Meter Summary by Business Hub — Status = 2, Account No. not assigned', $totals['total']);

        $headers = [
            'A' => '#', 'B' => 'Region', 'C' => 'Business Hub', 'D' => 'Total',
            'E' => 'With Billing', 'F' => 'With Regional Billing', 'G' => 'With Regional Head',
        ];
        $this->writeHeaderRow($sheet, $headers, $lastCol);

        $row = 5;
        foreach ($summary as $i => $s) {
            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", $s['region'] ?: '—');
            $sheet->setCellValue("C{$row}", $s['business_hub'] ?: '—');
            $sheet->setCellValue("D{$row}", $s['total']);
            $sheet->setCellValue("E{$row}", $s['with_billing']);
            $sheet->setCellValue("F{$row}", $s['with_regional_billing']);
            $sheet->setCellValue("G{$row}", $s['with_regional_head']);

            $this->styleDataRow($sheet, $row, $lastCol, $i);
            $row++;
        }

        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL');
        $sheet->setCellValue("D{$row}", $totals['total']);
        $sheet->setCellValue("E{$row}", $totals['with_billing']);
        $sheet->setCellValue("F{$row}", $totals['with_regional_billing']);
        $sheet->setCellValue("G{$row}", $totals['with_regional_head']);
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'font'    => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF1A3C6E']],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8F0FE']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF1A3C6E']]],
        ]);

        $sheet->getStyle("A5:A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D5:G{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("D5:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        foreach (['A' => 6, 'B' => 18, 'C' => 26, 'D' => 14, 'E' => 16, 'F' => 22, 'G' => 22] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $sheet->freezePane('A5');
    }

    private function writeDetailSheet(Worksheet $sheet, string $title, string $subtitle, Collection $rows): void
    {
        $sheet->setTitle($title);
        $lastCol = 'O';

        $this->writeTitleRows($sheet, $lastCol, $subtitle, $rows->count());
        $this->writeHeaderRow($sheet, self::DETAIL_HEADERS, $lastCol);

        $row = 5;
        foreach ($rows as $i => $rec) {
            $name    = trim(($rec->firstname ?? '') . ' ' . ($rec->other_name ?? ''));
            $address = trim(($rec->house_no ? $rec->house_no . ', ' : '') . ($rec->full_address ?? ''));

            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValueExplicit("B{$row}", (string) ($rec->tracking_id ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$row}", (string) ($rec->account_no ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("D{$row}", $name ?: '—');
            $sheet->setCellValueExplicit("E{$row}", (string) ($rec->map_id ?? '—'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("F{$row}", $rec->region ?? '—');
            $sheet->setCellValue("G{$row}", $rec->business_hub ?? '—');
            $sheet->setCellValue("H{$row}", $rec->service_center ?? '—');
            $sheet->setCellValue("I{$row}", $address ?: '—');
            $sheet->setCellValue("J{$row}", $this->statusLabel($rec));
            $sheet->setCellValue("K{$row}", $rec->evaluated ?? '—');
            $sheet->setCellValue("L{$row}", $rec->dss ?? '—');
            $sheet->setCellValue("M{$row}", $rec->dtm_comment ?? '');
            $sheet->setCellValue("N{$row}", $rec->billing_comment ?? '');
            $sheet->setCellValue("O{$row}", $rec->created_at ? \Carbon\Carbon::parse($rec->created_at)->format('d M Y, H:i') : '');

            $this->styleDataRow($sheet, $row, $lastCol, $i);
            $row++;
        }

        if ($rows->isNotEmpty()) {
            $sheet->getStyle("A5:A" . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setAutoFilter("A4:{$lastCol}" . ($row - 1));
        }

        foreach (self::DETAIL_WIDTHS as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $sheet->freezePane('A5');
    }

    private function writeTitleRows(Worksheet $sheet, string $lastCol, string $subtitle, int $total): void
    {
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'IBEDC — Ibadan Electricity Distribution Company');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Calibri'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A3C6E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(38);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', $subtitle);
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E5FA3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(22);

        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', 'Generated: ' . now()->format('d M Y, H:i') . '    |    Total Records: ' . number_format($total));
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF2E5FA3']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8F0FE']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(18);
    }

    private function writeHeaderRow(Worksheet $sheet, array $headers, string $lastCol): void
    {
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
    }

    private function styleDataRow(Worksheet $sheet, int $row, string $lastCol, int $index): void
    {
        $style = [
            'font'      => ['size' => 10],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
        ];

        if ($index % 2 === 1) {
            $style['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF5F8FC']];
        }

        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($style);
    }

    private function normalizeName(?string $value): string
    {
        $value = preg_replace('/\s+/', ' ', trim((string) $value));

        return ucwords(strtolower($value), " -");
    }

    private function evaluated($rec): string
    {
        return strtolower(trim((string) ($rec->evaluated ?? '')));
    }

    private function statusLabel($rec): string
    {
        $s         = (int) $rec->status;
        $lecanNull = empty($rec->lecan_link);
        $eval      = (string) ($rec->evaluated ?? '');

        return match (true) {
            in_array($s, [0, 1]) && $lecanNull  => 'With Customer',
            in_array($s, [0, 1]) && !$lecanNull => 'With DTM',
            $s === 2 && $eval === 'no'          => 'Regional Billing',
            $s === 2 && $eval === 'approved'    => 'Regional Head',
            $s === 2 && $eval === 'yes'         => 'With Billing',
            $s === 4                            => 'Completed',
            $s === 5                            => 'Rejected',
            default                             => 'Status ' . $s,
        };
    }
}
