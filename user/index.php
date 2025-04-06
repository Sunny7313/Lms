<?php
session_start();
$_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];
include '../config.php';
include '../includes/message_popup.php';
include '../calculate_fines.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] !== 'member' && $_SESSION['role'] !== 'faculty')) {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['username'];
$email = $_SESSION['email'];
$branch = $_SESSION['branch'];

function fetchUserProfile($conn, $email)
{
    $query = "SELECT * FROM member_list WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

$userProfile = fetchUserProfile($conn, $email);

function fetchBooks($conn, $searchQuery = '', $branch = null, $bookId = null, $orderBy = null, $limit = null)
{
    $query = "SELECT * FROM books WHERE (book_name LIKE ? OR author_name LIKE ? OR category LIKE ?)";
    $params = ["sss", '%' . $searchQuery . '%', '%' . $searchQuery . '%', '%' . $searchQuery . '%'];

    if ($branch) {
        $query .= " AND branch = ?";
        $params[0] .= "s";
        $params[] = $branch;
    }
    if ($bookId) {
        $query .= " AND book_id = ?";
        $params[0] .= "s";
        $params[] = $bookId;
    }
    if ($orderBy) {
        $query .= " ORDER BY " . $orderBy;
    }
    if ($limit) {
        $query .= " LIMIT ?";
        $params[0] .= "i";
        $params[] = $limit;
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param(...$params);
    $stmt->execute();
    return $stmt->get_result();
}

function fetchBookById($conn, $bookId)
{
    $query = "SELECT * FROM books WHERE book_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $bookId);
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
            <br>
            {$requestButton}
            <a href='detail.php?book_id={$bookId}' class='btn-secondary'>View Details</a>
        </div>
    </div>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
    $userProfile = fetchUserProfile($conn, $email);

    if ($bookId && $userProfile && !empty($userProfile['pin_number'])) {
        // Check if the book_id exists in the books table
        $check_book_sql = "SELECT * FROM books WHERE book_id = ?";
        $check_book_stmt = $conn->prepare($check_book_sql);
        $check_book_stmt->bind_param("s", $bookId);
        $check_book_stmt->execute();
        $check_book_result = $check_book_stmt->get_result();

        if ($check_book_result->num_rows > 0) {
            $query = "SELECT * FROM book_requests WHERE book_id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("is", $bookId, $userProfile['pin_number']);
            $stmt->execute();
            $existingRequest = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($existingRequest) {
                $_SESSION['message'] = "You have already requested this book.";
            } else {
                $query = "INSERT INTO book_requests (book_id, user_id, status) VALUES (?, ?, 'pending')";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("is", $bookId, $userProfile['pin_number']);
                $stmt->execute();
                $stmt->close();

                $_SESSION['message'] = "Book request submitted successfully.";
            }
        } else {
            echo '<script>alert("Invalid book ID.");</script>';
        }
    } else {
        $_SESSION['message'] = "Invalid book request.";
    }

    header("Location: index.php");
    exit();
}

$searchQuery = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$result = fetchBooks($conn, $searchQuery);
$bookId = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;
$bookDetails = $bookId ? fetchBookById($conn, $bookId) : null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library App</title>
    <link rel="stylesheet" href="../src/css/globals.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .popup-message {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
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
                <a href="index.php" class="nav-link flex items-center p-2 text-gray-700 hover:bg-gray-100 rounded-md active">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" class="mr-2">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        <polyline points="9 22 9 12 15 12 15 22" />
                    </svg>
                    Home
                </a>
                <a href="discover.php" class="nav-link flex items-center p-2 text-gray-700 hover:bg-gray-100 rounded-md">
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
                    <form method="GET" action="index.php" class="relative">
                        <input
                            type="text"
                            name="search"
                            placeholder="Search by title, author, category, etc."
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
                        <div id="profile-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1">
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
            <?php displayMessagePopup(); ?>
            <section class="hero">
                <div class="hero-content">
                    <h1>Welcome, <?= htmlspecialchars($user) ?>!</h1>
                    <p>Discover our curated selection of books just for you.</p>
                    <button class="btn-primary">Explore Latest</button>
                </div>
                <!-- <img src="../photos/hero-image.jpg" alt="Library illustration" class="hero-image"> -->
            </section>

            <?php if ($bookDetails): ?>
                <section class="book-details" id="book-details">
                    <h2 class="text-xl font-semibold">Book Details</h2>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <td colspan="2">
                                        <form method="POST" action="request_book.php">
                                            <input type="hidden" name="book_id" value="<?= $bookId ?>">
                                            <button type="submit" class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition-colors">Request This Book</button>
                                        </form>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Book Cover</th>
                                    <td><img src="photos/<?= $bookDetails['book_cover'] ?>" alt="Book cover"
                                            class="book-cover"></td>
                                </tr>
                                <tr>
                                    <th>Book ID</th>
                                    <td><?= $bookDetails['book_id'] ?></td>
                                </tr>
                                <tr>
                                    <th>Title</th>
                                    <td id="title"><?= $bookDetails['book_name'] ?></td>
                                </tr>
                                <tr>
                                    <th>Author</th>
                                    <td><?= $bookDetails['author_name'] ?></td>
                                </tr>
                                <tr>
                                    <th>Year</th>
                                    <td><?= $bookDetails['published_date'] ?></td>
                                </tr>
                                <tr>
                                    <th>Category</th>
                                    <td><?= $bookDetails['category'] ?></td>
                                </tr>
                                <tr>
                                    <th>Stock</th>
                                    <td><?= $bookDetails['stock'] ?></td>
                                </tr>
                                <tr>
                                    <td><a href="index.php" class="btn btn-sm btn-danger col-sm-7 float-right"><i
                                                class="fa fa-sign-out-alt"> </i></a></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </section>
            <?php elseif ($searchQuery): ?>
                <?php if ($result->num_rows > 0): ?>
                    <section class="recommended">
                        <h2 class="text-xl font-semibold">Book Details</h2>
                        <div class="book-grid book-grid-scrollable">
                            <?php
                            while ($row = $result->fetch_assoc()) {
                                echo generateBookCard($row, true);
                            }
                            ?>
                        </div>
                    </section>
                <?php else: ?>
                    <p>Book not found.</p>
                <?php endif; ?>
            <?php else: ?>
                <section class="recommended">
                    <h2 class="text-xl font-semibold">Recommended For You</h2>
                    <div class="book-grid book-grid-scrollable">
                        <?php
                        $result = fetchBooks($conn, '', $branch, '', 'branch ASC', 5);
                        while ($row = $result->fetch_assoc()) {
                            echo generateBookCard($row);
                        }
                        ?>
                    </div>
                </section>

                <section class="new-arrivals">
                    <h2 class="text-xl font-semibold">New Arrivals</h2>
                    <div class="book-grid">
                        <?php
                        $result = fetchBooks($conn, '', '', '', 'published_date DESC', 5);
                        while ($row = $result->fetch_assoc()) {
                            echo generateBookCard($row);
                        }
                        ?>
                    </div>
                </section>


            <?php endif; ?>
            <script>
                function requestBook(bookId) {
                    const form = document.createElement("form");
                    form.method = "POST";
                    form.action = "index.php";
                    const input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "book_id";
                    input.value = bookId;
                    form.appendChild(input);
                    const previousPageInput = document.createElement("input");
                    previousPageInput.type = "hidden";
                    previousPageInput.name = "previous_page";
                    previousPageInput.value = "<?= $_SESSION['previous_page'] ?>";
                    form.appendChild(previousPageInput);
                    document.body.appendChild(form);
                    form.submit();
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
    <script>
        function requestBook(bookId) {
            const form = document.createElement("form");
            form.method = "POST";
            form.action = "index.php";
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = "book_id";
            input.value = bookId;
            form.appendChild(input);
            const previousPageInput = document.createElement("input");
            previousPageInput.type = "hidden";
            previousPageInput.name = "previous_page";
            previousPageInput.value = "<?= $_SESSION['previous_page'] ?>";
            form.appendChild(previousPageInput);
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>

</html>