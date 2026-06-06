<?php
declare(strict_types=1);

class SimplePdf
{
    private const PAGE_WIDTH = 842;
    private const PAGE_HEIGHT = 595;
    private const MARGIN = 36;

    private array $pages = [];
    private int $page = 0;
    private float $y = 545;

    public function addLine(string $text = ''): void
    {
        if ($this->y < self::MARGIN) {
            $this->addPage();
        }

        $this->text(self::MARGIN, $this->y, $text, 10);
        $this->y -= 15;
    }

    public function addTitle(string $text): void
    {
        if ($this->y < 70) {
            $this->addPage();
        }

        $this->text(self::MARGIN, $this->y, $text, 16, true);
        $this->y -= 24;
    }

    public function addSubtitle(string $text): void
    {
        if ($this->y < 60) {
            $this->addPage();
        }

        $this->text(self::MARGIN, $this->y, $text, 10, true);
        $this->y -= 16;
    }

    public function addTable(array $headers, array $rows): void
    {
        $widths = [32, 225, 82, 90, 105, 120, 90];
        $rowHeight = 19;
        $fontSize = 8;

        $this->addTableHeader($headers, $widths, $rowHeight, $fontSize);

        foreach ($rows as $row) {
            if ($this->y < self::MARGIN + $rowHeight) {
                $this->addPage();
                $this->addTableHeader($headers, $widths, $rowHeight, $fontSize);
            }

            $x = self::MARGIN;
            foreach ($row as $index => $value) {
                $width = $widths[$index];
                $this->rect($x, $this->y - $rowHeight + 4, $width, $rowHeight, false, [215, 222, 230]);
                $this->text($x + 4, $this->y - 9, $this->fit((string)$value, $width - 8, $fontSize), $fontSize);
                $x += $width;
            }
            $this->y -= $rowHeight;
        }
    }

    public function output(string $filename): void
    {
        if (!$this->pages) {
            $this->addPage();
        }

        $objects = [];
        $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[] = '';
        $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>";

        $pageIds = [];
        foreach ($this->pages as $content) {
            $pageObjectId = count($objects) + 1;
            $contentObjectId = $pageObjectId + 1;
            $pageIds[] = $pageObjectId . ' 0 R';
            $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 " . self::PAGE_WIDTH . ' ' . self::PAGE_HEIGHT . "] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents " . $contentObjectId . " 0 R >>";
            $objects[] = "<< /Length " . strlen($content) . " >>\nstream\n$content\nendstream";
        }
        $objects[1] = "<< /Type /Pages /Kids [" . implode(' ', $pageIds) . '] /Count ' . count($pageIds) . ' >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1) . " 0 obj\n$object\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string)$offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }
        $pdf .= "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";

        file_put_contents($filename, $pdf);
    }

    private function escape(string $text): string
    {
        $text = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text) ?: $text;
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private function addPage(): void
    {
        $this->pages[] = '';
        $this->page = count($this->pages) - 1;
        $this->y = self::PAGE_HEIGHT - 50;
    }

    private function addTableHeader(array $headers, array $widths, int $rowHeight, int $fontSize): void
    {
        if ($this->y < self::MARGIN + $rowHeight) {
            $this->addPage();
        }

        $x = self::MARGIN;
        foreach ($headers as $index => $header) {
            $width = $widths[$index];
            $this->rect($x, $this->y - $rowHeight + 4, $width, $rowHeight, true, [31, 41, 55]);
            $this->text($x + 4, $this->y - 9, $this->fit((string)$header, $width - 8, $fontSize), $fontSize, true, [255, 255, 255]);
            $x += $width;
        }
        $this->y -= $rowHeight;
    }

    private function text(float $x, float $y, string $text, int $size = 10, bool $bold = false, array $color = [0, 0, 0]): void
    {
        if (!$this->pages) {
            $this->addPage();
        }

        [$r, $g, $b] = array_map(fn($value) => round($value / 255, 3), $color);
        $font = $bold ? 'F2' : 'F1';
        $this->pages[$this->page] .= "$r $g $b rg\nBT\n/$font $size Tf\n1 0 0 1 " . round($x, 2) . ' ' . round($y, 2) . " Tm\n(" . $this->escape($text) . ") Tj\nET\n";
    }

    private function rect(float $x, float $y, float $width, float $height, bool $fill = false, array $color = [0, 0, 0]): void
    {
        if (!$this->pages) {
            $this->addPage();
        }

        [$r, $g, $b] = array_map(fn($value) => round($value / 255, 3), $color);
        $operator = $fill ? 'f' : 'S';
        $colorMode = $fill ? 'rg' : 'RG';
        $this->pages[$this->page] .= "$r $g $b $colorMode\n" . round($x, 2) . ' ' . round($y, 2) . ' ' . round($width, 2) . ' ' . round($height, 2) . " re $operator\n";
    }

    private function fit(string $text, float $width, int $fontSize): string
    {
        $maxChars = max(4, (int)floor($width / ($fontSize * .48)));
        if (mb_strlen($text, 'UTF-8') <= $maxChars) {
            return $text;
        }

        return mb_substr($text, 0, $maxChars - 3, 'UTF-8') . '...';
    }
}
