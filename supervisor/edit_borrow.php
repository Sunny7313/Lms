<?php
session_start();
include '../config.php';
include '../includes/message_popup.php';
include '../calculate_fines.php';

 if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'supervisor') {
    $_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

$pin = $_SESSION['pin_number'];

if (isset($_GET['id'])) {
    $borrow_id = $_GET['id'];
    $query = "SELECT bl.borrow_id, bl.borrow_date, bl.return_date, b.book_name, m.username, bl.book_id, bl.status
              FROM borrow_list bl 
              INNER JOIN books b ON bl.book_id = b.book_id 
              INNER JOIN member_list m ON bl.member_id = m.pin_number WHERE bl.borrow_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $borrow_id);

    $stmt->execute();
    $borrow = $stmt->get_result()->fetch_assoc();
    if (!$borrow) {
        echo '<script>alert("Borrow details not found."); window.location.href = "manage_borrow.php";</script>';
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['borrow_id'], $_POST['borrow_date'], $_POST['return_date'], $_POST['book_id'], $_POST['status'])) {
        $borrow_id = $_POST['borrow_id'];
        $borrow_date = $_POST['borrow_date'];
        $return_date = $_POST['return_date'];
        $book_id = $_POST['book_id'];
        $status = $_POST['status'];

        // Validate inputs
        if (empty($borrow_date) || empty($return_date) || empty($book_id) || empty($status)) {
            echo '<script>alert("Please fill in all required fields.");</script>';
            exit();
        }

        $query = "UPDATE borrow_list SET borrow_date = ?, return_date = ?, book_id = ?, status = ? WHERE borrow_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $borrow_date, $return_date, $book_id, $status, $borrow_id);

        if ($stmt->execute()) {
            header("Location: manage_borrow.php");
            exit();
        } else {
            echo '<script>alert("Error updating borrow. Please try again. Error: ' . $stmt->error . '");</script>';
        }
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
    <title>Edit Borrow - Library App</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
                <h1 class="text-2xl font-bold text-gray-900">Edit Borrow</h1>
            </header>
            <div class="p-8">
                <section class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <form action="edit_borrow.php" method="POST">
                        <input type="hidden" name="borrow_id" value="<?php echo $borrow['borrow_id']; ?>">
                        <div class="mb-4">
                            <label for="borrower_name" class="block text-sm font-medium text-gray-700">Borrower Name</label>
                            <input type="text" id="borrower_name" name="borrower_name" value="<?php echo $borrow['username']; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="borrow_date" class="block text-sm font-medium text-gray-700">Borrow Date</label>
                            <input type="date" id="borrow_date" name="borrow_date" value="<?php echo $borrow['borrow_date']; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="return_date" class="block text-sm font-medium text-gray-700">Return Date</label>
                            <input type="date" id="return_date" name="return_date" value="<?php echo $borrow['return_date']; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="status" name="status" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="Borrowed" <?= $borrow['status'] == 'Borrowed' ? 'selected' : '' ?>>Borrowed</option>
                                <option value="Returned" <?= $borrow['status'] == 'Returned' ? 'selected' : '' ?>>Returned</option>
                            </select>
                        </div>
                        <input type="hidden" name="book_id" value="<?php echo $borrow['book_id']; ?>">
                        <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition-colors">Update Borrow</button>
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