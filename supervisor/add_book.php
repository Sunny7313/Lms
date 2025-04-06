<?php
session_start();
include '../config.php';
include 'search_functions.php';
include '../includes/message_popup.php';
include '../calculate_fines.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'supervisor') {
    $_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

$admin_username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['book_id'], $_POST['book_name'], $_POST['author_name'], $_POST['published_date'], $_POST['branch'], $_POST['category'], $_POST['stock'], $_POST['rack'], $_FILES['book_cover'], $_POST['des'])) {
        $book_id = $_POST['book_id'];
        $book_name = $_POST['book_name'];
        $author_name = $_POST['author_name'];
        $published_date = $_POST['published_date'];
        $branch = $_POST['branch'];
        $category = $_POST['category'];
        $stock = $_POST['stock'];
        $rack = $_POST['rack'];
        $book_cover = $_FILES['book_cover']['name'];
        $des = $_POST['des'];

        if ($book_cover) {
            $target_dir = "../photos/";
            $target_file = $target_dir . basename($book_cover);
            move_uploaded_file($_FILES['book_cover']['tmp_name'], $target_file);
        }

        $query = "INSERT INTO books (book_id, book_name, author_name, published_date, branch, category, stock, rack, book_cover, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssssss", $book_id, $book_name, $author_name, $published_date, $branch, $category, $stock, $rack, $book_cover, $des);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "Book added successfully.";
            $_SESSION['message_type'] = 'success';
            $stmt->close();
        } else {
            $_SESSION['message'] = "Failed to add the book.";
            $_SESSION['message_type'] = 'error';
            $stmt->close();
        }
        header("Location: manage_books.php");
        exit();
    } else {
        echo '<script>alert("Please fill in all required fields.");</script>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Book - Library App</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        input[type=file]::file-selector-button {
            margin-right: 20px;
            border: none;
            background: #084cdf;
            padding: 10px 20px;
            border-radius: 10px;
            color: #fff;
            cursor: pointer;
            transition: background .2s ease-in-out;
        }

        input[type=file]::file-selector-button:hover {
            background: #0d45a5;
        }
    </style>
</head>

<body class="bg-gray-50">
    <?php displayMessagePopup(); ?>
    <div class="min-h-screen">
        <aside id="sidebar" class="fixed left-0 top-0 w-64 h-full bg-white border-r border-gray-200 z-30 hidden">
            <div class="flex items-center gap-3 p-6 border-b border-gray-100">
                <img src="../photos/college.png" alt="College Logo" class="w-8 h-8">
                <span class="text-xl font-semibold text-gray-900">Library Supervisor</span>
            </div>
            <nav class="p-4 space-y-2">
                <a href="index.php" class="flex items-center gap-3 p-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        <polyline points="9 22 9 12 15 12 15 22" />
                    </svg>
                    Dashboard
                </a>
                <a href="manage_books.php" class="flex items-center gap-3 p-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 4h9a2 2 0 0 1 2 2v14a2 2 0 0 0-2-2H2z"></path>
                        <path d="M22 4h-9a2 2 0 0 0-2 2v14a2 2 0 0 1 2-2h9z"></path>
                        <line x1="12" y1="6" x2="12" y2="20"></line>
                    </svg>
                    Manage Books
                </a>
                <a href="manage_book_requests.php" class="flex items-center gap-3 p-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="14" height="16" rx="2" ry="2"></rect>
                        <line x1="7" y1="4" x2="7" y2="20"></line>
                        <path d="M17 10h4v6a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2v-6z"></path>
                        <line x1="17" y1="10" x2="21" y2="14"></line>
                        <line x1="21" y1="10" x2="17" y2="14"></line>
                    </svg>

                    Manage Book Requests
                </a>
                <a href="manage_borrow.php" class="flex items-center gap-3 p-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect>
                        <path d="M7 4v5l2.5-1.5L12 9V4"></path>
                        <path d="M16 13l-4 4-4-4"></path>
                        <line x1="12" y1="17" x2="12" y2="7"></line>
                    </svg>
                    Manage Borrow
                </a>
                <a href="manage_fines.php" class="flex items-center gap-3 p-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 2h12l2 2v16l-2 2H4l-2-2V4l2-2z"></path>
                        <line x1="6" y1="8" x2="14" y2="8"></line>
                        <line x1="6" y1="12" x2="10" y2="12"></line>
                        <circle cx="18" cy="16" r="3"></circle>
                        <line x1="18" y1="15" x2="18" y2="17"></line>
                        <path d="M17 16h2"></path>
                    </svg>
                    Manage Fines
                </a>
            </nav>
        </aside>
        <main id="main-content" class="min-h-screen">
            <header class="bg-white border-b border-gray-200 px-8 py-4 flex justify-between items-center">
                <button id="toggle-sidebar" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <h1 class="text-2xl font-bold text-gray-900">Add Book</h1>
            </header>
            <div class="p-8">
                <section class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <form action="add_book.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="book_id" class="block text-sm font-medium text-gray-700">Book ID</label>
                            <input type="text" id="book_id" name="book_id" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="book_name" class="block text-sm font-medium text-gray-700">Book Name</label>
                            <input type="text" id="book_name" name="book_name" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="author_name" class="block text-sm font-medium text-gray-700">Author Name</label>
                            <input type="text" id="author_name" name="author_name" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="published_date" class="block text-sm font-medium text-gray-700">Published Date</label>
                            <input type="date" id="published_date" name="published_date" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="branch" class="block text-sm font-medium text-gray-700">Branch</label>
                            <input type="text" id="branch" name="branch" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                            <input type="text" id="category" name="category" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
                            <input type="number" id="stock" name="stock" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="rack" class="block text-sm font-medium text-gray-700">Rack</label>
                            <input type="text" id="rack" name="rack" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="des" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="des" name="des" required cols="50" rows="7" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="book_cover" class="block text-sm font-medium text-gray-700">Book Cover</label>
                            <input type="file" id="book_cover" name="book_cover" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition-colors">Add Book</button>
                    </form>
                </section>
            </div>
        </main>
    </div>
    <script>
        document.getElementById('toggle-sidebar').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            sidebar.classList.toggle('hidden');
            mainContent.classList.toggle('ml-64');
        });
    </script>
</body>

</html>