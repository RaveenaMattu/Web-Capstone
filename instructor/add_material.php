<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
require_once('../database.php');
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory as WordIO;
use PhpOffice\PhpPresentation\IOFactory as PPTIO;
use Dompdf\Dompdf;
use FPDF\FPDF;


// Ensure PDO throws exceptions
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check user role
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'Instructor') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../instructor_manage_course.php');
    exit();
}

// Get form data
$courseID = intval($_POST['courseID'] ?? 0);
$title = trim($_POST['title'] ?? '');

if (empty($title) || !isset($_FILES['materialFile'])) {
    $_SESSION['error'] = "Please provide both title and file.";
    header("Location: instructor_manage_course.php?courseID=$courseID");
    exit();
}

$file = $_FILES['materialFile'];
$uploadDir = '../uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$originalName = pathinfo($file['name'], PATHINFO_FILENAME);
$fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

$finalFileName = $originalName . '.' . $fileExt;  // keep real extension for images
$finalFilePath = $uploadDir . $finalFileName;

try {
    switch ($fileExt) {
        // ---------- Images ----------
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            if (!move_uploaded_file($file['tmp_name'], $finalFilePath)) {
                throw new Exception("Failed to upload image file.");
            }
            break;

        // ---------- Word ----------
        case 'doc':
        case 'docx':
            $pdfFileName = $originalName . '.pdf';
            $pdfFilePath = $uploadDir . $pdfFileName;

            $phpWord = WordIO::load($file['tmp_name']);
            $tempHtml = $uploadDir . $originalName . '.html';
            $htmlWriter = WordIO::createWriter($phpWord, 'HTML');
            $htmlWriter->save($tempHtml);

            $dompdf = new Dompdf();
            $dompdf->loadHtml(file_get_contents($tempHtml));
            $dompdf->render();
            file_put_contents($pdfFilePath, $dompdf->output());

            unlink($tempHtml);

            $finalFileName = $pdfFileName;
            $finalFilePath = $pdfFilePath;
            break;

        // ---------- PowerPoint ----------
      case 'ppt':
      case 'pptx':
        $pptFileName = $originalName . '.' . $fileExt;
        $pptFilePath = $uploadDir . $pptFileName;

        if (!move_uploaded_file($file['tmp_name'], $pptFilePath)) {
            throw new Exception("Failed to upload PPTX file.");
        }

        // Optional: Convert to PDF using PHPPresentation + Dompdf
        
        
/*
        $ppt = PPTIO::load($pptFilePath);
        $tempHtml = $uploadDir . $originalName . '.html';
        $writer = PPTIO::createWriter($ppt, 'HTML');
        $writer->save($tempHtml);

        $dompdf = new Dompdf();
        $dompdf->loadHtml(file_get_contents($tempHtml));
        $dompdf->render();
        $pdfFileName = $originalName . '.pdf';
        $pdfFilePath = $uploadDir . $pdfFileName;
        file_put_contents($pdfFilePath, $dompdf->output());
*/
        unlink($tempHtml);
        
        $finalFileName = $pptFileName;
        $finalFilePath = $pptFilePath;
        break;


        // ---------- TXT ----------
        case 'txt':
            $pdfFileName = $originalName . '.pdf';
            $pdfFilePath = $uploadDir . $pdfFileName;

            $txtContent = file_get_contents($file['tmp_name']);
            $fpdf = new FPDF();
            $fpdf->AddPage();
            $fpdf->SetFont('Arial', '', 12);
            $lines = explode("\n", $txtContent);
            foreach ($lines as $line) {
                $fpdf->MultiCell(0, 6, $line);
            }
            $fpdf->Output('F', $pdfFilePath);

            $finalFileName = $pdfFileName;
            $finalFilePath = $pdfFilePath;
            break;

        // ---------- PDF ----------
        case 'pdf':
            if (!move_uploaded_file($file['tmp_name'], $finalFilePath)) {
                throw new Exception("Failed to upload PDF file.");
            }
            break;

        default:
            throw new Exception("Unsupported file type: .$fileExt");
    }

    // Insert into database
    $stmt = $db->prepare("INSERT INTO course_materials (courseID, title, file_path, uploaded_at) 
                          VALUES (:courseID, :title, :file_path, NOW())");
    $stmt->bindValue(':courseID', $courseID, PDO::PARAM_INT);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':file_path', '/web-capstone/uploads/' . $finalFileName, PDO::PARAM_STR);
    $stmt->execute();
    $stmt->closeCursor();

    $_SESSION['success'] = "Material uploaded successfully!";
    }

 catch (Exception $e) {
    $_SESSION['error'] = "Error processing file: " . $e->getMessage();
    error_log("File processing error: " . $e->getMessage());
}

header("Location: instructor_manage_course.php?courseID=$courseID");
exit();
?>