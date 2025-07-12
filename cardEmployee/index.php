<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Card Generator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        label {
            margin-bottom: 10px;
            display: block;
            font-weight: bold;
        }

        input[type="file"] {
            margin-bottom: 20px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
        }

        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        button:hover {
            background-color: #218838;
        }

        @media (max-width: 400px) {
            form {
                width: 90%;
            }
        }
    </style>
</head>

<body>
    <form id="fileForm" action="create_cards.php" method="post" enctype="multipart/form-data">
        <label for="file">Choose Excel File:</label>
        <input type="file" name="file" id="file" accept=".xls, .xlsx" required>

        <div style="display: flex; gap: 10px;">
            <!-- Air Bus button -->
            <button type="submit" name="submit" value="Air Bus Card" onclick="setAction('create_cards.php')">Air Bus</button>

            <!-- Buva Sea button -->
            <button type="submit" name="submit" value="Buva Sea Card" onclick="setAction('buvaSea.php')">Buva Sea</button>
        </div>


    </form>

    <script>
        function setAction(action) {
            document.getElementById('fileForm').action = action;
        }
    </script>
</body>

</html>