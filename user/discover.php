<?php
session_start();
$_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];
include '../config.php';
include '../includes/message_popup.php';
//include '../includes/request_book.php';
include '../calculate_fines.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] !== 'member' && $_SESSION['role'] !== 'faculty')) {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['username'];
$email = $_SESSION['email'];

function fetchUserProfile($conn, $email)
{
    $query = "SELECT * FROM member_list WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function generateBookCard($row, $showRequestButton = true)
{
    $bookCover = '../photos/' . htmlspecialchars($row['book_cover']);
    $bookId = htmlspecialchars($row['book_id']);
    $requestButton = $showRequestButton ? "<form method='POST' action='request_book.php' class='inline-block'>
                        <input type='hidden' name='book_id' value='{$bookId}'>
                        <button type='submit' class='bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition-colors'>Request This Book</button>
                    </form>" : "";
    return "
    <div class='book-card'>
        <img src='{$bookCover}' alt='Book cover' class='book-cover'>
        <div class='book-info'>
            <h3>" . htmlspecialchars($row['book_name']) . "</h3>
            <p>" . htmlspecialchars($row['author_name']) . " • " . htmlspecialchars($row['published_date']) . "</p>
            <div class='stock'>
                <span>Stock: " . htmlspecialchars($row['stock']) . "</span>
            </div>
            <div class='flex space-x-2'>
                {$requestButton}
                <a href='detail.php?book_id={$bookId}#book-details' class='btn-secondary'>View Details</a>
            </div>
        </div>
    </div>";
}

$userProfile = fetchUserProfile($conn, $email);

$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$sortByOrder = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'book_name-ASC';
$sortByOrderParts = explode('-', $sortByOrder);
$sortBy = $sortByOrderParts[0] ?? 'book_name';
$order = strtoupper($sortByOrderParts[1] ?? 'ASC');

$branch = isset($_GET['branch']) ? $_GET['branch'] : '';
$stockStatus = isset($_GET['stock_status']) ? $_GET['stock_status'] : '';

$validSortBy = ['book_name', 'author_name', 'published_date', 'branch'];
$validOrder = ['ASC', 'DESC'];

if (!in_array($sortBy, $validSortBy)) {
    $sortBy = 'book_name';
}

if (!in_array($order, $validOrder)) {
    $order = 'ASC'; // Default fallback if invalid order
}

$query = "SELECT * FROM books WHERE (book_name LIKE ? OR author_name LIKE ? OR branch LIKE ? OR published_date LIKE ?)";
$params = ["ssss", '%' . $searchQuery . '%', '%' . $searchQuery . '%', '%' . $searchQuery . '%', '%' . $searchQuery . '%'];

if ($branch) {
    $query .= " AND branch = ?";
    $params[0] .= "s";
    $params[] = $branch;
}

if ($stockStatus) {
    if ($stockStatus === 'in_stock') {
        $query .= " AND stock > 0";
    } elseif ($stockStatus === 'out_of_stock') {
        $query .= " AND stock = 0";
    }
}

$query .= " ORDER BY $sortBy $order";

$stmt = $conn->prepare($query);
$stmt->bind_param(...$params);
$stmt->execute();
$books = $stmt->get_result();
$stmt->close();

$branchesQuery = "SELECT DISTINCT branch FROM books";
$branchesResult = $conn->query($branchesQuery);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discover Books - Library App</title>
    <link rel="stylesheet" href="../src/css/globals.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php displayMessagePopup(); ?>
    <div class="min-h-screen">
        <aside id="sidebar" class="sidebar">
            <div class="flex items-center gap-3 p-6 border-b border-gray-100">
                <img src="../photos/college.png" alt="College Logo" class="w-15 h-8">
                <span class="text-xl font-semibold text-gray-900">Library </span>
            </div>
            <nav class="nav flex flex-col p-4">
                <a href="index.php" class="nav-link flex items-center p-2 text-gray-700 hover:bg-gray-100 rounded-md">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" class="mr-2">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        <polyline points="9 22 9 12 15 12 15 22" />
                    </svg>
                    Home
                </a>
                <a href="discover.php" class="nav-link flex items-center p-2 text-gray-700 hover:bg-gray-100 rounded-md active">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" class="mr-2">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    Discover Books
                </a>
                <a href="book_requests_status.php" class="nav-link flex items-center p-2 text-gray-700 hover:bg-gray-100 rounded-md">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" class="mr-2">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                        <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
                        <path d="M12 11h4" />
                        <path d="M12 16h4" />
                        <path d="M8 11h.01" />
                        <path d="M8 16h.01" />
                    </svg>
                    Book Requests
                </a>
                <a href="borrow.php" class="nav-link flex items-center p-2 text-gray-700 hover:bg-gray-100 rounded-md">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" class="mr-2">
                        <path d="m7 11 2-2-2-2" />
                        <path d="M11 13h4" />
                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                    </svg>
                    Borrowing Data
                </a>
                <a href="fines.php" class="nav-link flex items-center p-2 text-gray-700 hover:bg-gray-100 rounded-md">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" class="mr-2">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8" />
                        <path d="M12 18V6" />
                    </svg>
                    Fines
                </a>
            </nav>
        </aside>

        <main id="main-content" class="main p-6">
            <header class="bg-white border-b border-gray-200 px-8 py-4 flex justify-between items-center">
                <button id="toggle-sidebar" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <div class="relative w-96">
                    <form method="GET" action="discover.php" class="relative">
                        <input
                            type="text"
                            name="search"
                            placeholder="Search by title, author, branch, etc."
                            value="<?= htmlspecialchars($searchQuery) ?>"
                            class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </form>
                </div>
                <div class="flex items-center gap-4">
                    <button class="p-2 hover:bg-gray-100 rounded-lg relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                    <div class="relative">
                        <button
                            onclick="document.getElementById('profile-menu').classList.toggle('hidden')"
                            class="flex items-center gap-2 hover:bg-gray-100 rounded-lg p-1">
                            <img src="../photos/bssdata.png" alt="User avatar" class="w-8 h-8 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div id="profile-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1" style="z-index: 1;">
                            <a href="#" onclick="showUserProfile()" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                View Profile
                            </a>
                            <a href="#" onclick="showChangePassword()" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                Change Password
                            </a>
                            <a href="logout.php" class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-gray-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            <div class="profile-menu hidden" id="profile-menu">
                <a href="#userprofile" onclick="showUserProfile()" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">View Profile</a>
                <a href="#changepassword" onclick="showChangePassword()" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Change Password</a>
                <a href="logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
            </div>
            <?php displayMessagePopup(); ?>
            <section class="bg-white p-6 rounded-md shadow-md">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Available Books</h2>
                    <div class="dropdown">
                        <button class="dropbtn" onclick="toggleDropdown()">Sort & Filter</button>
                        <div class="dropdown-content hidden" id="dropdown-content">
                            <form method="GET" action="discover.php">
                                <input type="hidden" name="search" value="<?= htmlspecialchars($searchQuery) ?>">
                                <label for="branch">Branch:</label>
                                <select name="branch" id="branch" class="sort-dropdown" onchange="this.form.submit()">
                                    <option value="">All Branches</option>
                                    <?php while ($branchRow = $branchesResult->fetch_assoc()): ?>
                                        <option value="<?= $branchRow['branch'] ?>" <?= $branch == $branchRow['branch'] ? 'selected' : '' ?>><?= $branchRow['branch'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                                <label for="stock_status">Stock Status:</label>
                                <select name="stock_status" id="stock_status" class="sort-dropdown" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <option value="in_stock" <?= $stockStatus == 'in_stock' ? 'selected' : '' ?>>In Stock</option>
                                    <option value="out_of_stock" <?= $stockStatus == 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                                </select>
                                <label for="sort_by">Sort By:</label>
                                <select name="sort_by" id="sort_by" class="sort-dropdown" onchange="this.form.submit()">
                                    <option value="book_name-ASC" <?= $sortByOrder == 'book_name-ASC' ? 'selected' : '' ?>>Title (A-Z)</option>
                                    <option value="book_name-DESC" <?= $sortByOrder == 'book_name-DESC' ? 'selected' : '' ?>>Title (Z-A)</option>
                                    <option value="author_name-ASC" <?= $sortByOrder == 'author_name-ASC' ? 'selected' : '' ?>>Author (A-Z)</option>
                                    <option value="author_name-DESC" <?= $sortByOrder == 'author_name-DESC' ? 'selected' : '' ?>>Author (Z-A)</option>
                                    <option value="published_date-ASC" <?= $sortByOrder == 'published_date-ASC' ? 'selected' : '' ?>>Published Date (Oldest)</option>
                                    <option value="published_date-DESC" <?= $sortByOrder == 'published_date-DESC' ? 'selected' : '' ?>>Published Date (Newest)</option>
                                    <option value="branch-ASC" <?= $sortByOrder == 'branch-ASC' ? 'selected' : '' ?>>Branch (A-Z)</option>
                                    <option value="branch-DESC" <?= $sortByOrder == 'branch-DESC' ? 'selected' : '' ?>>Branch (Z-A)</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="book-grid">
                    <?php while ($row = $books->fetch_assoc()): ?>
                        <?= generateBookCard($row, true) ?>
                    <?php endwhile; ?>
                </div>
            </section>
            <form id="requestBookForm" method="POST" action="request_book.php" style="display: none;">
                <input type="hidden" name="book_id" id="book_id">
                <input type="hidden" name="previous_page" value="<?= $_SESSION['previous_page'] ?>">
            </form>
            <script>
                function requestBook(bookId) {
                    document.getElementById('book_id').value = bookId;
                    document.getElementById('requestBookForm').submit();
                }
            </script>
        </main>
    </div>
    <div id="userprofile" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Admin Profile</h2>
                <button onclick="closeUserProfile()" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="space-y-4">
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Username</span>
                    <span class="font-medium text-gray-900"><?= htmlspecialchars($userProfile['username']) ?></span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Email</span>
                    <span class="font-medium text-gray-900"><?= htmlspecialchars($userProfile['email']) ?></span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Phone Number</span>
                    <span class="font-medium text-gray-900"><?= htmlspecialchars($userProfile['phone_number']) ?></span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Branch</span>
                    <span class="font-medium text-gray-900"><?= htmlspecialchars($userProfile['branch']) ?></span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Section</span>
                    <span class="font-medium text-gray-900"><?= htmlspecialchars($userProfile['section']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="changepassword" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Change Password</h2>
                <button onclick="closeChangePassword()" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form action="change_password.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                    <input type="password" name="current_password" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input type="password" name="new_password" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input type="password" name="confirm_password" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition-colors">
                    Update Password
                </button>
            </form>
        </div>
    </div>

    <script src="../src/js/script.js"></script>
</body>

</html>
</html>