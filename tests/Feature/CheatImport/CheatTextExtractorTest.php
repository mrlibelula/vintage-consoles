<?php

use App\Services\CheatImport\CheatTextExtractor;
use Illuminate\Http\UploadedFile;

function cheatExtractorTempFile(string $contents, string $extension): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'cheat_test_') . '.' . $extension;
    file_put_contents($path, $contents);

    return new UploadedFile($path, "source.{$extension}", null, null, true);
}

it('normalizes pasted text by trimming whitespace', function () {
    $extractor = new CheatTextExtractor();

    expect($extractor->normalize("  \n  Up, Up, Down, Down  \n\n  "))->toBe('Up, Up, Down, Down');
});

it('returns an empty string for blank input', function () {
    $extractor = new CheatTextExtractor();

    expect($extractor->normalize("   \n\t  "))->toBe('');
});

it('caps extracted text at the max length', function () {
    $extractor = new CheatTextExtractor();
    $huge = str_repeat('a', 90_000);

    expect(strlen($extractor->normalize($huge)))->toBe(80_000);
});

it('extracts .txt uploads as-is', function () {
    $file = cheatExtractorTempFile("Cheat: Up Up Down Down\n", 'txt');

    expect((new CheatTextExtractor())->extractFromUpload($file))->toBe('Cheat: Up Up Down Down');
});

it('extracts .md uploads as-is', function () {
    $file = cheatExtractorTempFile("# Cheats\n- Code A", 'md');

    expect((new CheatTextExtractor())->extractFromUpload($file))->toBe("# Cheats\n- Code A");
});

it('rejects legacy .doc uploads with a clear message', function () {
    $file = cheatExtractorTempFile('binary junk', 'doc');

    expect(fn () => (new CheatTextExtractor())->extractFromUpload($file))
        ->toThrow(RuntimeException::class, 'Legacy .doc files are not supported');
});

it('rejects unsupported file extensions', function () {
    $file = cheatExtractorTempFile('junk', 'exe');

    expect(fn () => (new CheatTextExtractor())->extractFromUpload($file))
        ->toThrow(RuntimeException::class, 'Unsupported file type');
});

it('extracts text from a .docx archive', function () {
    $docxPath = tempnam(sys_get_temp_dir(), 'cheat_test_') . '.docx';

    $zip = new ZipArchive();
    $zip->open($docxPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('word/document.xml', '<w:document><w:body>'
        . '<w:p><w:r><w:t>Cheat Codes</w:t></w:r></w:p>'
        . '<w:p><w:r><w:t>Up, Up, Down, Down</w:t></w:r></w:p>'
        . '</w:body></w:document>');
    $zip->close();

    $file = new UploadedFile($docxPath, 'source.docx', null, null, true);

    $text = (new CheatTextExtractor())->extractFromUpload($file);

    expect($text)->toContain('Cheat Codes')
        ->and($text)->toContain('Up, Up, Down, Down');
});
