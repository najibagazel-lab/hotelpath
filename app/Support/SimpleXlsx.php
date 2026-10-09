<?php

namespace App\Support;

use ZipArchive;

class SimpleXlsx
{
    public static function download(string $filename, array $headers, iterable $rows)
    {
        $path = tempnam(sys_get_temp_dir(), 'contractflow-xlsx-');
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $allRows = [array_values($headers)];
        foreach ($rows as $row) {
            $allRows[] = array_values($row);
        }

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Report" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF16877C"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellXfs count="2"><xf fontId="0" fillId="0" borderId="0"/><xf fontId="1" fillId="1" borderId="0" applyFont="1" applyFill="1"/></cellXfs></styleSheet>');
        $zip->addFromString('xl/worksheets/sheet1.xml', self::sheetXml($allRows));
        $zip->close();

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private static function sheetXml(array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols>';
        foreach (($rows[0] ?? []) as $index => $header) {
            $xml .= '<col min="'.($index + 1).'" max="'.($index + 1).'" width="22" customWidth="1"/>';
        }
        $xml .= '</cols><sheetData>';
        foreach ($rows as $rowIndex => $row) {
            $xml .= '<row r="'.($rowIndex + 1).'">';
            foreach ($row as $columnIndex => $value) {
                $reference = self::columnLetter($columnIndex + 1).($rowIndex + 1);
                $style = $rowIndex === 0 ? ' s="1"' : '';
                $text = htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $xml .= '<c r="'.$reference.'" t="inlineStr"'.$style.'><is><t>'.$text.'</t></is></c>';
            }
            $xml .= '</row>';
        }
        return $xml.'</sheetData><autoFilter ref="A1:'.self::columnLetter(max(1, count($rows[0] ?? []))).max(1, count($rows)).'"/></worksheet>';
    }

    private static function columnLetter(int $number): string
    {
        $letter = '';
        while ($number > 0) {
            $number--;
            $letter = chr(65 + ($number % 26)).$letter;
            $number = intdiv($number, 26);
        }
        return $letter;
    }
}
