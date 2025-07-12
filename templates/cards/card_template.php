<?php

function generateEmployeeCard($id, $name_kh, $name_en, $position, $star_date, $expired_date, $uploadedFileName, $imageBase64)
{
    // Generate unique ids for each card using the employee ID
    return '
        <div class="card-holder" id="card-holder-' . $id . '">
            <div class="container">
                <div class="upload-container">
                    <!-- The image element that shows the current image -->
                    <img src="' . ($imageBase64 ? $imageBase64 : '../assets/images/image_style.jpg') . '" class="image-preview" data-card-id="' . $id . '" alt="Employee Image">
                    <!-- Hidden file input element -->
                    <input type="file" class="image-input" data-card-id="' . $id . '" style="display: none;" accept="image/*">
                </div>
                <div class="card-detail">
                    <h2 class="name_kh">' . $name_kh . '</h2> 
                    <div class="name_en">' . $name_en . '</div> 
                    <div class="position">' . $position . '</div>
                </div>
                <div class="details">
                    <div class="content">
                        <p>ID No</p>
                        <p> : ' . $id . '</p>
                    </div>
                    <div class="content">
                        <p>Starting Date</p>
                        <p> : ' . $star_date . '</p>
                    </div>
                    <div class="content">
                        <p>Expired Date</p>
                        <p> : ' . $expired_date . '</p>
                    </div>
                </div>
                <div class="hover-buttons">
                    <!-- Change Image button triggers the file input -->
                    <button class="change-image" data-card-id="' . $id . '">Change Image</button>
                    <button class="show-price" onclick="window.location.href=\'templates/detail/card_detail.php?id=' . $id . '&file=' . urlencode($uploadedFileName) . '\';">Print</button>
                </div>
                
            </div>
        </div>
    ';
}
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listener for all "Change Image" buttons
        document.querySelectorAll('.change-image').forEach(function(button) {
            button.addEventListener('click', function() {
                const cardId = button.getAttribute('data-card-id'); // Get unique card ID
                const fileInput = document.querySelector('.image-input[data-card-id="' + cardId + '"]'); // Get corresponding file input
                fileInput.click(); // Trigger file input click
            });
        });

        // Add event listener for all file inputs
        document.querySelectorAll('.image-input').forEach(function(input) {
            input.addEventListener('change', function(event) {
                const cardId = input.getAttribute('data-card-id'); // Get unique card ID
                const file = event.target.files[0];
                if (file) {
                    console.log('File selected for card ID ' + cardId + ':', file); // Debugging: check if the file is selected
                    const reader = new FileReader();

                    reader.onloadend = function() {
                        console.log('File loaded successfully for card ID ' + cardId + ':', reader.result); // Debugging: check the result
                        // Update the image preview with the selected file
                        const imagePreview = document.querySelector('.image-preview[data-card-id="' + cardId + '"]');
                        imagePreview.src = reader.result;
                    };

                    reader.onerror = function() {
                        console.error('Error reading the file for card ID ' + cardId); // Handle errors in file reading
                    };

                    reader.readAsDataURL(file); // Read the image file as a data URL
                } else {
                    console.log('No file selected for card ID ' + cardId); // Debugging: check if no file was selected
                }
            });
        });
    });
</script>