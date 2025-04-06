<?php
session_start();
include '../config.php';
include '../includes/message_popup.php';
include '../calculate_fines.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$admin_username = $_SESSION['username'];

if (isset($_GET['id'])) {
    $book_id = $_GET['id'];
    $query = "SELECT * FROM books WHERE book_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $book_id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    if (!$book) {
        $_SESSION['message'] = "Book details not found.";
        $_SESSION['message_type'] = "error";
        header("Location: manage_books.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['book_id'], $_POST['book_name'], $_POST['author_name'], $_POST['published_date'], $_POST['category'], $_POST['stock'], $_POST['rack'],$_POST['des'])) {
        $book_id = $_POST['book_id'];
        $book_name = $_POST['book_name'];
        $author_name = $_POST['author_name'];
        $published_date = $_POST['published_date'];
        $category = $_POST['category'];
        $stock = $_POST['stock'];
        $rack = $_POST['rack'];
        $book_cover = $_FILES['book_cover']['name'];
        $des = $_POST['des'];

        if ($book_cover) {
            $target_dir = "../photos/";
            $target_file = $target_dir . basename($book_cover);
            move_uploaded_file($_FILES['book_cover']['tmp_name'], $target_file);
        } else {
            $book_cover = $book['book_cover'];
        }

        $query = "UPDATE books SET book_name = ?, author_name = ?, published_date = ?, category = ?, stock = ?, rack = ?,description=?, book_cover = ? WHERE book_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssissss", $book_name, $author_name, $published_date, $category, $stock, $rack,$des, $book_cover, $book_id);
        $stmt->execute();

        $_SESSION['message'] = "Book updated successfully!";
        $_SESSION['message_type'] = "success";
        header("Location: manage_books.php");
        exit();
    } else {
        $_SESSION['message'] = "Please fill in all required fields.";
        $_SESSION['message_type'] = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book - Library App</title>
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
        <aside class="fixed left-0 top-0 w-64 h-full bg-white border-r border-gray-200 z-30">
            <div class="flex items-center gap-3 p-6 border-b border-gray-100">
                <img src="../photos/college.png" alt="College Logo" class="w-8 h-8">
                <span class="text-xl font-semibold text-gray-900">Library Admin</span>
            </div>
            <nav class="p-4 space-y-2">
                <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-indigo-50 text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    <span class="font-medium">Dashboard</span>
                </a>
                <a href="manage_books.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                    <span class="font-medium">Manage Books</span>
                </a>
                <a href="manage_members.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span class="font-medium">Manage Members</span>
                </a>
                <a href="manage_borrow.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect>
                        <path d="M7 4v5l2.5-1.5L12 9V4"></path>
                        <path d="M16 13l-4 4-4-4"></path>
                        <line x1="12" y1="17" x2="12" y2="7"></line>
                    </svg>
                    <span class="font-medium">Manage Borrow</span>
                </a>
                <a href="manage_fines.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 2h12l2 2v16l-2 2H4l-2-2V4l2-2z"></path>
                        <line x1="6" y1="8" x2="14" y2="8"></line>
                        <line x1="6" y1="12" x2="10" y2="12"></line>
                        <circle cx="18" cy="16" r="3"></circle>
                        <line x1="18" y1="15" x2="18" y2="17"></line>
                        <path d="M17 16h2"></path>
                    </svg>
                    <span class="font-medium">Manage Fines</span>
                </a>
            </nav>
        </aside>
        <main class="ml-64 min-h-screen">
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Edit Book</h1>
                </div>
            </header>
            <div class="p-8">
                <section class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <form action="edit_book.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                        <div class="mb-4">
                            <label for="book_name" class="block text-sm font-medium text-gray-700">Book Name</label>
                            <input type="text" id="book_name" name="book_name" value="<?php echo $book['book_name']; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="author_name" class="block text-sm font-medium text-gray-700">Author Name</label>
                            <input type="text" id="author_name" name="author_name" value="<?php echo $book['author_name']; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="published_date" class="block text-sm font-medium text-gray-700">Published Date</label>
                            <input type="date" id="published_date" name="published_date" value="<?php echo $book['published_date']; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                            <input type="text" id="category" name="category" value="<?php echo $book['category']; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
                            <input type="number" id="stock" name="stock" value="<?php echo $book['stock']; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="rack" class="block text-sm font-medium text-gray-700">Rack</label>
                            <input type="text" id="rack" name="rack" value="<?php echo $book['rack']; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="des" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="des" name="des" required cols="50" rows="7" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?php echo $book['description']; ?></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="book_cover" class="block text-sm font-medium text-gray-700">Book Cover</label>
                            <input type="file" id="book_cover" name="book_cover" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <img src="../photos/<?php echo $book['book_cover']; ?>" alt="Book Cover" class="mt-2" style="max-width: 100px;">
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition-colors">Update Book</button>
                    </form>
                </section>
            </div>
        </main>
    </div>
</body>

</html>