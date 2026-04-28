<?php
// Create a minimal DOCX to test MIME detection
$docx = sys_get_temp_dir() . '/detect_test.docx';
$zip = new ZipArchive();
$zip->open($docx, ZipArchive::CREATE);
$zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
$zip->addFromString('_rels/.rels', '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>');
$zip->addFromString('word/document.xml', '<?xml version="1.0"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>Test</w:t></w:r></w:p></w:body></w:document>');
$zip->close();

$finfo = new finfo(FILEINFO_MIME_TYPE);
$detected = $finfo->file($docx);
echo "DOCX file MIME type detected by PHP: $detected\n";
echo "Expected for Laravel mimes:docx validation: application/vnd.openxmlformats-officedocument.wordprocessingml.document\n";
echo "Match: " . ($detected === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ? 'YES' : 'NO') . "\n";

if ($detected === 'application/zip') {
    echo "\nPROBLEM: PHP detects DOCX as application/zip!\n";
    echo "Laravel's mimes:docx validation will FAIL because it checks MIME type.\n";
    echo "Fix: Use mimetypes:application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/pdf\n";
    echo "Or change mimes:docx to mimetypes:\n";
}

unlink($docx);
