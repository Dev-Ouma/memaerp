<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

final class DataExportService
{
    /**
     * Generate standard RFC 4180 CSV with UTF-8 BOM.
     */
    public function exportCsv(string $filename, array $headers, array $rows): StreamedResponse
    {
        $headersList = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // UTF-8 BOM for Microsoft Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, $headers);

            // Data rows
            foreach ($rows as $row) {
                $sanitized = array_map(function ($val) {
                    if (is_null($val)) {
                        return '';
                    }
                    if (is_bool($val)) {
                        return $val ? 'Yes' : 'No';
                    }
                    if (is_array($val)) {
                        return implode(', ', $val);
                    }
                    return (string) $val;
                }, $row);

                fputcsv($handle, $sanitized);
            }

            fclose($handle);
        }, 200, $headersList);
    }

    /**
     * Generate authentic OpenXML (.xlsx) Excel spreadsheet.
     */
    public function exportXlsx(string $filename, string $sheetTitle, array $headers, array $rows): StreamedResponse
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_export_');
        $zip = new ZipArchive();
        $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // 1. [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // 2. _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        // 3. xl/_rels/workbook.xml.rels
        $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);

        // 4. xl/styles.xml (System Brand Theme: Dark Teal #0A3E50 and Orange #E67E22)
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="3">
        <font>
            <sz val="10"/>
            <color theme="1"/>
            <name val="Segoe UI"/>
        </font>
        <font>
            <b/>
            <sz val="11"/>
            <color rgb="FFFFFFFF"/>
            <name val="Segoe UI"/>
        </font>
        <font>
            <b/>
            <sz val="12"/>
            <color rgb="FF0A3E50"/>
            <name val="Segoe UI"/>
        </font>
    </fonts>
    <fills count="4">
        <fill><patternFill patternType="none"/></fill>
        <fill><patternFill patternType="gray125"/></fill>
        <fill>
            <patternFill patternType="solid">
                <fgColor rgb="FF0A3E50"/>
            </patternFill>
        </fill>
        <fill>
            <patternFill patternType="solid">
                <fgColor rgb="FFF8FAFC"/>
            </patternFill>
        </fill>
    </fills>
    <borders count="2">
        <border><left/><right/><top/><bottom/><diagonal/></border>
        <border>
            <left style="thin"><color rgb="FFE2E8F0"/></left>
            <right style="thin"><color rgb="FFE2E8F0"/></right>
            <top style="thin"><color rgb="FFE2E8F0"/></top>
            <bottom style="thin"><color rgb="FFE2E8F0"/></bottom>
        </border>
    </borders>
    <cellStyleXfs count="1">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
    </cellStyleXfs>
    <cellXfs count="4">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
            <alignment horizontal="center" vertical="center" wrapText="1"/>
        </xf>
        <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1">
            <alignment vertical="center"/>
        </xf>
        <xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
            <alignment vertical="center"/>
        </xf>
    </cellXfs>
</styleSheet>';
        $zip->addFromString('xl/styles.xml', $styles);

        // 5. xl/workbook.xml
        $cleanSheetName = htmlspecialchars(substr(preg_replace('/[^A-Za-z0-9 _-]/', '', $sheetTitle) ?: 'Data', 0, 31), ENT_XML1);
        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="'.$cleanSheetName.'" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>';
        $zip->addFromString('xl/workbook.xml', $workbook);

        // 6. xl/worksheets/sheet1.xml
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <sheetViews>
        <sheetView tabSelected="1" workbookViewId="0">
            <pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>
        </sheetView>
    </sheetViews>
    <sheetFormatPr defaultRowHeight="20"/>
    <sheetData>';

        // Row 1: Header Row
        $sheetXml .= '<row r="1" ht="26" customHeight="1">';
        foreach ($headers as $colIdx => $header) {
            $colLetter = $this->getColumnLetter($colIdx + 1);
            $cellValue = htmlspecialchars((string) $header, ENT_XML1);
            $sheetXml .= '<c r="'.$colLetter.'1" t="inlineStr" s="1"><is><t>'.$cellValue.'</t></is></c>';
        }
        $sheetXml .= '</row>';

        // Data Rows
        $rowNum = 2;
        foreach ($rows as $row) {
            $styleId = ($rowNum % 2 === 0) ? '2' : '3';
            $sheetXml .= '<row r="'.$rowNum.'" ht="21" customHeight="1">';
            $colIdx = 0;
            foreach ($row as $val) {
                $colLetter = $this->getColumnLetter($colIdx + 1);
                $cellRef = $colLetter.$rowNum;

                if (is_numeric($val) && !str_starts_with((string)$val, '0') && strlen((string)$val) < 12) {
                    $sheetXml .= '<c r="'.$cellRef.'" s="'.$styleId.'"><v>'.$val.'</v></c>';
                } else {
                    $strVal = is_null($val) ? '' : (is_bool($val) ? ($val ? 'Yes' : 'No') : (is_array($val) ? implode(', ', $val) : (string)$val));
                    $cleanVal = htmlspecialchars($strVal, ENT_XML1);
                    $sheetXml .= '<c r="'.$cellRef.'" t="inlineStr" s="'.$styleId.'"><is><t>'.$cleanVal.'</t></is></c>';
                }
                $colIdx++;
            }
            $sheetXml .= '</row>';
            $rowNum++;
        }

        $sheetXml .= '</sheetData></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        $headersList = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => filesize($tempFile),
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($tempFile): void {
            readfile($tempFile);
            @unlink($tempFile);
        }, 200, $headersList);
    }

    /**
     * Convert 1-based index to Excel Column Letter (1 -> A, 27 -> AA).
     */
    private function getColumnLetter(int $colIndex): string
    {
        $letter = '';
        while ($colIndex > 0) {
            $remainder = ($colIndex - 1) % 26;
            $letter = chr(65 + $remainder) . $letter;
            $colIndex = (int) (($colIndex - $remainder) / 26);
        }
        return $letter;
    }
}
