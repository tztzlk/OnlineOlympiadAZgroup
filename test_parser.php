<?php
require 'vendor/autoload.php';

use App\Support\DocumentQuestionParser;
use Illuminate\Http\UploadedFile;

// Create a minimal valid DOCX in temp
$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_docx_' . time() . '.docx';

$zip = new ZipArchive();
$zip->open($tmp, ZipArchive::CREATE);
$zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
$zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');
$zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>1. Первый вопрос?</w:t></w:r></w:p><w:p><w:r><w:t>A. Ответ А</w:t></w:r></w:p><w:p><w:r><w:t>*B. Ответ Б</w:t></w:r></w:p></w:body></w:document>');
$zip->addFromString('word/_rels/document.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>');
$zip->close();

echo "Temp DOCX: $tmp\n";
echo "File exists: " . (file_exists($tmp) ? 'YES' : 'NO') . "\n";

// Boot Laravel app for Storage facade
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Try PhpWord
try {
    $phpWord = PhpOffice\PhpWord\IOFactory::load($tmp, 'Word2007');
    echo "PhpWord load: OK\n";
    $sections = $phpWord->getSections();
    echo "Sections: " . count($sections) . "\n";
    foreach ($sections as $section) {
        echo "Elements in section: " . count($section->getElements()) . "\n";
    }
} catch (Throwable $e) {
    echo "PhpWord ERROR: " . get_class($e) . ": " . $e->getMessage() . "\n";
}

// Try the actual parser
try {
    $uploadedFile = new UploadedFile($tmp, 'test.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);
    $parser = new DocumentQuestionParser();
    $result = $parser->parseDocx($uploadedFile);
    echo "Parser OK! Questions: " . count($result['questions']) . "\n";
    echo "Warnings: " . count($result['warnings']) . "\n";
    print_r($result);
} catch (Throwable $e) {
    echo "Parser ERROR: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

unlink($tmp);
