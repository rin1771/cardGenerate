<?php
require 'vendor/autoload.php'; // Include PhpSpreadsheet library
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

include 'templates/cards/card_buva_sea.php';

if (isset($_POST['submit'])) {
    // Check if the file was uploaded without errors
    if (isset($_FILES['file'])) {
        // Check for upload errors
        switch ($_FILES['file']['error']) {
            case UPLOAD_ERR_OK:
                // Check if the file size exceeds the limit
                if ($_FILES['file']['size'] > 100 * 1024 * 1024) {
                    echo "File is too large.";
                    exit;
                }

                $fileTmpPath = $_FILES['file']['tmp_name'];
                $uploadDir = 'uploads/excel/';

                // Ensure the upload directory exists
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true); // Create the directory if it doesn't exist
                }

                $uploadFile = $uploadDir . basename($_FILES['file']['name']);

                // Move the uploaded file to the designated directory
                if (move_uploaded_file($fileTmpPath, $uploadFile)) {
                    try {
                        // Load the Excel file
                        $spreadsheet = IOFactory::load($uploadFile);
                        $sheet = $spreadsheet->getActiveSheet();
                        $rows = $sheet->toArray();

                        // Find the index of the image column
                        $imageColumnIndex = null;
                        $headerRow = $rows[0];
                        foreach ($headerRow as $index => $header) {
                            if (
                                stripos($header, 'រូបថត') !== false ||
                                stripos($header, 'រូបភាព') !== false ||
                                stripos($header, 'image') !== false ||
                                stripos($header, 'images') !== false
                            ) {
                                $imageColumnIndex = $index; // Save the index of the image column
                                break;
                            }
                        }

                        $uploadedFileName = basename($_FILES['file']['name']); // Get the uploaded file name
                        $cardsHTML = '';
                        foreach ($rows as $index => $row) {
                            if ($index == 0) continue; // Skip header row

                            // Adjust these indices based on your actual column layout
                            $id = isset($row[1]) ? $row[1] : 'N/A';
                            $name_kh = isset($row[2]) ? $row[2] : 'N/A';
                            $name_en = isset($row[3]) ? $row[3] : 'N/A';
                            $position = isset($row[4]) ? $row[4] : 'N/A';
                            $star_date = isset($row[5]) ? $row[5] : 'N/A';
                            $expired_date = isset($row[6]) ? $row[6] : 'N/A';

                            // Initialize image base64 string
                            $imageBase64 = '';
                            $drawingCollection = $sheet->getDrawingCollection();

                            // Check if the image column index is valid
                            if ($imageColumnIndex !== null) {
                                foreach ($drawingCollection as $draw) {
                                    if ($draw instanceof Drawing) {
                                        // Check if the drawing corresponds to the current row and the image column
                                        $rowIndex = $index + 1; // Excel rows are 1-based
                                        if ($draw->getCoordinates() == \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($imageColumnIndex + 1) . $rowIndex) {
                                            $imagePath = $draw->getPath();
                                            $imageData = file_get_contents($imagePath);
                                            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
                                            $mimeType = 'image/' . $extension; // Set the mime type
                                            $imageBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                                            break;
                                        }
                                    }
                                }
                            }

                            // Generate HTML for the employee card with the uploaded file name
                            $cardsHTML .= generateEmployeeCard($id, $name_kh, $name_en, $position, $star_date, $expired_date, $uploadedFileName, $imageBase64);
                        }

                        // Output the entire page with the employee cards
                        echo "<!DOCTYPE html>
                        <html lang='en'>
                        <head>
                            <meta charset='UTF-8'>
                            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                            <title>Employee Cards</title>
                            <link rel='stylesheet' href='css/style.css'>
                        </head>
                        <body>
                            <form id='printForm'>
                                <input type='button' value='Print Cards' onclick='printAndHide()'>  
                            </form>
                            <div class='employee-cards'>$cardsHTML</div>
                            <script>
                                function printAndHide() {
                                // Hide the form
                                const form = document.getElementById('printForm');
                                form.style.display = 'none';

                                // Trigger the print dialog
                                window.print();

                                // Show the form again after a short delay
                                setTimeout(() => {
                                    form.style.display = 'block';
                                }, 100); // Adjust delay as needed

                                
                            }
                            </script>
                        </body>
                        </html>";
                    } catch (Exception $e) {
                        echo "Error loading the Excel file: " . $e->getMessage();
                    }
                } else {
                    echo "Error moving the uploaded file.";
                }
                break;

            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                echo "File is too large.";
                break;
            case UPLOAD_ERR_PARTIAL:
                echo "File was only partially uploaded.";
                break;
            case UPLOAD_ERR_NO_FILE:
                echo "No file was uploaded.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                echo "No temporary directory.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                echo "Failed to write file to disk.";
                break;
            case UPLOAD_ERR_EXTENSION:
                echo "File upload stopped by extension.";
                break;
            default:
                echo "Unknown upload error.";
                break;
        }
    } else {
        echo "No file was uploaded.";
    }
}
