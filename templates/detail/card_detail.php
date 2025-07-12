<?php
require '../../vendor/autoload.php'; // Adjusted path to autoload.php
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

if (isset($_GET['id']) && isset($_GET['file'])) {
    // Sanitize input
    $id = htmlspecialchars($_GET['id']);
    $uploadedFileName = basename($_GET['file']); // Prevent directory traversal
    $uploadDir = '../../uploads/excel/'; // Correct path to the upload directory

    // Construct the full path to the uploaded file
    $fullPath = $uploadDir . $uploadedFileName;

    // Check if the file exists
    if (!file_exists($fullPath)) {
        die("File does not exist: " . htmlspecialchars($fullPath)); // Provide the path for clarity
    }

    // Load the Excel file
    try {
        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Find the index of the image column
        $imageColumnIndex = null;
        $headerRow = $rows[0];
        foreach ($headerRow as $index => $header) {
            if (
                stripos($header, 'រូបភាព') !== false ||
                stripos($header, 'រូបថត') !== false ||
                stripos($header, 'image') !== false ||
                stripos($header, 'images') !== false
            ) {
                $imageColumnIndex = $index; // Save the index of the image column
                break;
            }
        }

        // Find the employee by ID
        $employee = null;
        foreach ($rows as $row) {
            if (isset($row[1]) && $row[1] == $id) { // Assuming ID is in the 2nd column
                $employee = $row;
                break;
            }
        }

        if ($employee) {
            // Extract employee details
            $name_kh = htmlspecialchars($employee[2]);
            $name_en = htmlspecialchars($employee[3]);
            $position = htmlspecialchars($employee[4]);
            $star_date = htmlspecialchars($employee[5]);
            $expired_date = htmlspecialchars($employee[6]);

            // Initialize image base64 string
            $imageBase64 = '';
            $drawingCollection = $sheet->getDrawingCollection();

            // Check if the image column index is valid
            if ($imageColumnIndex !== null) {
                foreach ($drawingCollection as $draw) {
                    if ($draw instanceof Drawing) {
                        // Check if the drawing corresponds to the current row and the image column
                        $rowIndex = array_search($employee, $rows); // Get the row index based on employee data
                        if ($draw->getCoordinates() == \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($imageColumnIndex + 1) . ($rowIndex + 1)) {
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

            // Render the employee detail page
            echo "<!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Employee Detail</title>
                <link rel='stylesheet' href='../../css/style.css'>
                <link rel='preconnect' href='https://fonts.googleapis.com'>
                <link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
                <link href='https://fonts.googleapis.com/css2?family=Battambang:wght@100;300;400;700;900&family=Moul&family=Varela+Round&display=swap' rel='stylesheet'>
            </head>
            <body>
                <form id='printForm'>
                    <input type='button' value='Print Card' onclick='printAndHide()'>  
                </form>
                <div class='card-holder'> 
                    <div class='container'>         
                        <div class='upload-container' style='position: relative; display: inline-block;'>
                            <img id='imagePreview' class='image-preview' alt='Image Preview' style='display: " . ($imageBase64 ? 'block' : 'none') . "; ' src='$imageBase64'>
                            <img id='image_simle' src='../../assets/images/image_style.jpg' alt='simple image' style='display: " . ($imageBase64 ? 'none' : 'block') . ";'>
                            <input type='file' id='imageUpload' style='display: none;' accept='image/*'>
                        </div>

                        <div class='card-detail'>
                            <h2 class='name_kh'>" . $name_kh . "</h2> 
                            <div class='name_en'>" . $name_en . "</div> 
                            <div class='position'>" . $position . "</div>
                        </div>
                        <div class='details'>
                            <div class='content'>
                                <p>ID No</p>
                                <p> : " . $id . "</p>
                            </div>
                            <div class='content'>
                                <p>Starting Date</p>
                                <p> : " . $star_date . "</p>
                            </div>
                            <div class='content'>
                                <p>Expired Date</p>
                                <p> : " . $expired_date . "</p>
                            </div>
                        </div>
                    </div>
                </div>
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
                    document.querySelector('.upload-container').addEventListener('click', function () {
                    document.querySelector('#imageUpload').click();});
                    document.querySelector('#imageUpload').addEventListener('change', function (event) {
                        const file = event.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function (e) {
                                document.querySelector('#imagePreview').src = e.target.result;
                                document.querySelector('#imagePreview').style.display = 'block';
                                document.querySelector('#image_simle').style.display = 'none';
                            };
                            reader.readAsDataURL(file);
                        }
                    });  
                </script>
            </body>
            </html>";
        } else {
            echo "Employee not found.";
        }
    } catch (Exception $e) {
        echo "Error loading the Excel file: " . htmlspecialchars($e->getMessage());
    }
} else {
    echo "No employee ID specified.";
}
