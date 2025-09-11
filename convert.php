<?php
// Input PPTX file
$input = __DIR__ . "/sample.pptx";  

// Output PDF file
$output = __DIR__ . "/sample.pdf";  

// LibreOffice binary path
$soffice = "/opt/homebrew/bin/soffice";

// Build command
$command = escapeshellcmd("$soffice --headless --convert-to pdf --outdir " . escapeshellarg(dirname($output)) . " " . escapeshellarg($input));

// Run command and capture output/errors
exec($command . " 2>&1", $outputLines, $resultCode);

// Debug output
echo "Command: $command\n";
echo "Exit Code: $resultCode\n";
echo "Output:\n" . implode("\n", $outputLines) . "\n";
?>