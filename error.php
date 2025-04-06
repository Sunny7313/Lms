<?php
session_start();
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Library App</title>
    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(to bottom right, #EBF4FF, #E0E7FF);
            padding: 20px;
        }

        .error-container {
            text-align: center;
            width: 100%;
            max-width: 42rem;
            background-color: white;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            transition: transform 0.3s ease;
        }

        .error-container:hover {
            transform: scale(1.05);
        }

        .error-icon {
            width: 16rem;
            height: 16rem;
            margin-bottom: 2rem;
        }

        h1 {
            font-size: 2.25rem;
            font-weight: bold;
            color: #1F2937;
            margin-bottom: 0.5rem;
        }

        p {
            font-size: 1.5rem;
            color: #4B5563;
            margin-bottom: 2rem;
        }

        .home-link {
            display: inline-block;
            background-color:rgb(237, 7, 7);
            color: white;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .home-link:hover,
        .home-link:focus {
            background-color: #2563EB;
        }

        .home-link:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
        }

        @media (max-width: 640px) {
            .error-icon {
                width: 12rem;
                height: 12rem;
            }

            h1 {
                font-size: 1.875rem;
            }

            p {
                font-size: 1.25rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .error-container {
                transition: none;
            }

            .error-container:hover {
                transform: none;
            }
        }
    </style>
</head>

<body>
    <div class="error-container">
        <svg class="error-icon" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="100" cy="100" r="90" fill="red" />
            <text x="100" y="220" font-size="120" font-weight="bold" fill="white" text-anchor="middle" transform="scale(1, -1) translate(0, -280)">i</text>
        </svg>
        <h1>Oops! An Error Occurred</h1>
        <p><?= htmlspecialchars($error_message) ?></p>
        <a href="index.php" class="home-link">Go to Home</a>
    </div>
</body>

</html>