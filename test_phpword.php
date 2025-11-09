<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\PhpWord;

echo "<pre>";
echo class_exists(TemplateProcessor::class) ? "OK TemplateProcessor\n" : "NO TemplateProcessor\n";
echo class_exists(PhpWord::class) ? "OK PhpWord\n" : "NO PhpWord\n";

try {
    $phpWord = new PhpWord();
    $s = $phpWord->addSection();
    $s->addText('Hola, PhpWord está funcionando ✅');
    $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $outfile = __DIR__ . '/test_ok.docx';
    $writer->save($outfile);
    echo "Generado: $outfile\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";
