<?php
session_start();
include '../config.php';
include '../includes/message_popup.php';
include '../calculate_fines.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] !== 'member' && $_SESSION['role'] !== 'faculty')) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];
$email = $_SESSION['email'];

function fetchUserProfile($conn, $email)
{
    $query = "SELECT * FROM member_list WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

$userProfile = fetchUserProfile($conn, $email);

if (!isset($_GET['borrow_id'])) {
    header('Location: borrow.php');
    exit();
}

$borrowId = $_GET['borrow_id'];

function fetchBorrowStatus($conn, $borrowId, $email)
{
    $query = "SELECT bl.borrow_id, bl.borrow_date, bl.return_date, bl.stock, bl.status, b.book_id, b.book_name, m.pin_number, m.username 
              FROM borrow_list bl 
              INNER JOIN books b ON bl.book_id = b.book_id 
              INNER JOIN member_list m ON bl.member_id = m.pin_number 
              WHERE bl.borrow_id = ? AND m.email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $borrowId, $email);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

$borrowStatus = fetchBorrowStatus($conn, $borrowId, $email);
$searchQuery = '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowing Status - Library App</title>
    <link rel="stylesheet" href="../src/css/globals.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-gray-50">
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
                <a href="borrow.php" class="nav-link flex items-center p-2 text-gray-700 hover:bg-gray-100 rounded-md active">
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
            <section class="bg-white p-6 rounded-md shadow-md">
                <h2 class="text-xl font-semibold mb-4">Borrowing Status</h2>
                <div class="table-responsive">
                    <table class="min-w-full bg-white border border-gray-200 rounded-md">
                        <tr>
                            <th class="px-4 py-2 border-b text-center">Borrow ID</th>
                            <td class="px-4 py-2 border-b text-center"><?= $borrowStatus['borrow_id'] ?></td>
                        </tr>
                        <tr>
                            <th class="px-4 py-2 border-b text-center">Book ID</th>
                            <td class="px-4 py-2 border-b text-center"><?= $borrowStatus['book_id'] ?></td>
                        </tr>
                        <tr>
                            <th class="px-4 py-2 border-b text-center">Book Title</th>
                            <td class="px-4 py-2 border-b text-center"><?= $borrowStatus['book_name'] ?></td>
                        </tr>
                        <tr>
                            <th class="px-4 py-2 border-b text-center">Borrow Date</th>
                            <td class="px-4 py-2 border-b text-center"><?= $borrowStatus['borrow_date'] ?></td>
                        </tr>
                        <tr>
                            <th class="px-4 py-2 border-b text-center">Return Date</th>
                            <td class="px-4 py-2 border-b text-center"><?= $borrowStatus['return_date'] ?></td>
                        </tr>
                        <tr>
                            <th class="px-4 py-2 border-b text-center">Member ID</th>
                            <td class="px-4 py-2 border-b text-center"><?= $borrowStatus['pin_number'] ?></td>
                        </tr>
                        <tr>
                            <th class="px-4 py-2 border-b text-center">Borrower Name</th>
                            <td class="px-4 py-2 border-b text-center"><?= $borrowStatus['username'] ?></td>
                        </tr>
                        <tr>
                            <th class="px-4 py-2 border-b text-center">Quantity</th>
                            <td class="px-4 py-2 border-b text-center"><?= $borrowStatus['stock'] ?></td>
                        </tr>
                        <tr>
                            <th class="px-4 py-2 border-b text-center">Status</th>
                            <td class="px-4 py-2 border-b text-center"><?= $borrowStatus['status'] ?></td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 border-b">
                                <a href="borrow.php" class="btn btn-sm btn-danger col-sm-7 float-right"><i
                                        class="fa fa-sign-out-alt"> </i></a>
                            </td>
                        </tr>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <div id="userprofile" class="modal fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="modal-content bg-white p-6 rounded-md shadow-md">
            <span class="close cursor-pointer" onclick="closeUserProfile()">&times;</span>
            <h2 class="text-xl font-semibold mb-4">User Profile</h2>
            <div class="form-group mb-4">
                <label class="block text-gray-700">Username</label>
                <p class="text-gray-900"><?= htmlspecialchars($userProfile['username']) ?></p>
            </div>
            <div class="form-group mb-4">
                <label class="block text-gray-700">Email</label>
                <p class="text-gray-900"><?= htmlspecialchars($userProfile['email']) ?></p>
            </div>
            <div class="form-group mb-4">
                <label class="block text-gray-700">Phone Number</label>
                <p class="text-gray-900"><?= htmlspecialchars($userProfile['phone_number']) ?></p>
            </div>
            <div class="form-group mb-4">
                <label class="block text-gray-700">Branch</label>
                <p class="text-gray-900"><?= htmlspecialchars($userProfile['branch']) ?></p>
            </div>
            <div class="form-group mb-4">
                <label class="block text-gray-700">Section</label>
                <p class="text-gray-900"><?= htmlspecialchars($userProfile['section']) ?></p>
            </div>
            <div class="form-group mb-4">
                <label class="block text-gray-700">Role</label>
                <p class="text-gray-900"><?= htmlspecialchars($userProfile['role']) ?></p>
            </div>
        </div>
    </div>
    <div id="changepassword" class="modal fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="modal-content bg-white p-6 rounded-md shadow-md">
            <span class="close cursor-pointer" onclick="closeChangePassword()">&times;</span>
            <h2 class="text-xl font-semibold mb-4">Change Password</h2>
            <form method="POST" action="change_password.php">
                <div class="form-group mb-4">
                    <label for="current_password" class="block text-gray-700">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required class="w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div class="form-group mb-4">
                    <label for="new_password" class="block text-gray-700">New Password</label>
                    <input type="password" id="new_password" name="new_password" required class="w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div class="form-group mb-4">
                    <label for="confirm_password" class="block text-gray-700">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required class="w-full p-2 border border-gray-300 rounded-md">
                </div>
                <button type="submit" class="btn btn-primary bg-blue-500 text-white px-4 py-2 rounded-md">Change Password</button>
            </form>
        </div>
    </div>
    <script src="../src/js/script.js"></script>
</body>

</html>