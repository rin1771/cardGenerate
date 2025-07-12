<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

// HTML Head
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Employee Detail</title>
    
    <!-- Preconnect to optimize font loading -->
    <link rel='preconnect' href='https://fonts.googleapis.com'>
    <link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
    
    <!-- Load Google Fonts -->
    <link href='https://fonts.googleapis.com/css2?family=Moul&family=Varela+Round&display=swap' rel='stylesheet'>
    
    <!-- Link to external CSS -->
    <link rel='stylesheet' href='../../css/style.css'>
</head>
<body>";

if (isset($_GET['id']) && isset($_GET['file'])) {
    // Sanitize input
    $id = htmlspecialchars($_GET['id']);
    $uploadedFileName = basename($_GET['file']);
    $uploadDir = '../../uploads/excel/';
    $fullPath = $uploadDir . $uploadedFileName;

    // Check if the file exists
    if (!file_exists($fullPath)) {
        die("File does not exist: " . htmlspecialchars($fullPath));
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
            if (stripos($header, 'រូបភាព') !== false || stripos($header, 'រូបថត') !== false || stripos($header, 'image') !== false || stripos($header, 'images') !== false) {
                $imageColumnIndex = $index;
                break;
            }
        }

        // Find the employee by ID
        $employee = null;
        foreach ($rows as $row) {
            if (isset($row[1]) && $row[1] == $id) {
                $employee = $row;
                break;
            }
        }

        if ($employee) {
            // Extract employee details and image
            $name_kh = htmlspecialchars($employee[2]);
            $name_en = htmlspecialchars($employee[3]);
            $position = htmlspecialchars($employee[4]);
            $star_date = date('d-M-y', strtotime(htmlspecialchars($employee[5])));
            $expired_date = date('d-M-y', strtotime(htmlspecialchars($employee[6])));
            $imageBase64 = '';

            // Check for images
            if ($imageColumnIndex !== null) {
                foreach ($sheet->getDrawingCollection() as $draw) {
                    if ($draw instanceof Drawing) {
                        $rowIndex = array_search($employee, $rows);
                        if ($draw->getCoordinates() === \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($imageColumnIndex + 1) . ($rowIndex + 1)) {
                            $imagePath = $draw->getPath();
                            $imageData = file_get_contents($imagePath);
                            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
                            $mimeType = 'image/' . $extension;
                            $imageBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                            break;
                        }
                    }
                }
            }

            // Render employee detail
            echo "<div class='card-holder'>
                    <div class='container'>
                        <div class='upload-container' style='position: relative; display: inline-block;'>
                            <img id='imagePreview' class='image-preview' alt='Image Preview' style='display: " . ($imageBase64 ? 'block' : 'none') . ";' src='$imageBase64'>
                            <img id='image_simle' src='../../assets/images/image_style.jpg' alt='simple image' style='display: " . ($imageBase64 ? 'none' : 'block') . ";'>
                            <input type='file' id='imageUpload' style='display: none;' accept='image/*'>
                        </div>
                        <div class='card-detail'>
                            <h2 class='name_kh'>$name_kh</h2>
                            <div class='name_en'>$name_en</div>
                            <div class='position'>$position</div>
                        </div>
                        <div class='details'>
                            <div class='content'>
                                <p>ID No</p>
                                <p>Starting Date</p>
                                <p>Expired Date</p>
                            </div>
                            <div class='content'>
                                <p><span>:</span> $id</p>
                                <p><span>:</span> $star_date</p>
                                <p><span>:</span> $expired_date</p>
                            </div>
                        </div>
                    </div>
                </div>";
        } else {
            echo "Employee not found.";
        }
    } catch (Exception $e) {
        echo "Error loading the Excel file: " . htmlspecialchars($e->getMessage());
    }
} else {
    echo "No employee ID specified.";
}

// JavaScript for printing and image upload
echo "<script>
    function printAndHide() {
        const form = document.getElementById('printForm');
        form.style.display = 'none';
        window.print();
        setTimeout(() => {
            form.style.display = 'block';
        }, 100);
    }
    document.querySelector('.upload-container').addEventListener('click', function () {
        document.querySelector('#imageUpload').click();
    });
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
