<?php

namespace App\Services\CheatImport;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use Smalot\PdfParser\Parser as PdfParser;
use ZipArchive;

/**
 * Extracts plain text from admin-uploaded cheat source files. Pure-PHP only
 * (no external binaries) so this works on shared hosting like Namecheap.
 */
class CheatTextExtractor
{
    /** Cap extracted text before it goes to the AI call (request size + timeout safety). */
    private const MAX_CHARS = 80_000;

    public function extractFromUpload(UploadedFile $file): string
    {
        $ext  = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        if ($path === false || ! is_readable($path)) {
            throw new RuntimeException('Could not read the uploaded file.');
        }

        $text = match ($ext) {
            'txt', 'md' => (string) file_get_contents($path),
            'docx' => $this->extractDocx($path),
            'pdf' => $this->extractPdf($path),
            'doc' => throw new RuntimeException(
                'Legacy .doc files are not supported. Please paste the text, or convert it to .txt, .docx, or .pdf.'
            ),
            default => throw new RuntimeException(
                "Unsupported file type: .{$ext}. Use .txt, .md, .docx, or .pdf, or paste the text directly."
            ),
        };

        return $this->normalize($text);
    }

    public function normalize(string $text): string
    {
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        if (strlen($text) > self::MAX_CHARS) {
            $text = substr($text, 0, self::MAX_CHARS);
        }

        return $text;
    }

    private function extractDocx(string $path): string
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('Could not open the .docx file (invalid archive).');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            throw new RuntimeException('Could not read the .docx contents.');
        }

        // Preserve paragraph/tab breaks before stripping tags, or everything runs together.
        $xml = (string) preg_replace('/<\/w:p>/', "\n", $xml);
        $xml = (string) preg_replace('/<w:tab\/>/', "\t", $xml);
        $text = strip_tags($xml);

        return html_entity_decode($text, ENT_QUOTES | ENT_XML1);
    }

    private function extractPdf(string $path): string
    {
        try {
            $parser = new PdfParser();
            $pdf    = $parser->parseFile($path);

            return $pdf->getText();
        } catch (\Throwable $e) {
            throw new RuntimeException('Could not read the PDF contents: ' . $e->getMessage());
        }
    }
}
