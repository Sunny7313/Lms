<?php
session_start();
include '../config.php';
include 'db_functions.php';
include 'search_functions.php';
 include 'functions.php';
include '../includes/message_popup.php';
include '../calculate_fines.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'supervisor') {
    $_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

$supervisor_username = $_SESSION['username'];
$email = $_SESSION['email'];

$userProfile = fetchUserProfile($conn, $email);

$searchResults = [];
$noResultsMessage = '';
$searchQuery = '';
if (isset($_GET['search'])) {
    $searchQuery = htmlspecialchars(trim($_GET['search'])); // Sanitize user input
    $searchResults = searchBooks($conn, $searchQuery);
    if ($searchResults->num_rows === 0) {
        $noResultsMessage = "No results found for '$searchQuery'.";
    }
}

$statistics = fetchStatistics($conn);
$bookCategories = fetchBookCategories($conn);
$memberRoles = fetchMemberRoles($conn);
$memberBranches = fetchMemberBranches($conn);
$memberSections = fetchMemberSections($conn);
$borrowedBooks = fetchBorrowedBooks($conn);
$finesByAmount = fetchFinesByAmount($conn);
$borrowRequestStatus = fetchBorrowRequestStatus($conn);

// Debugging: Log the data returned by the functions
error_log(print_r($bookCategories, true));
error_log(print_r($finesByAmount, true));
error_log(print_r($borrowedBooks, true));

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard - Library App</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="index.php" class="flex items-center gap-3 p-2 text-gray-700 hover:bg-gray-100 rounded-lg active">
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

        <main id="main-content" class="p-6">
            <header class="bg-white border-b border-gray-200 px-8 py-4 flex justify-between items-center">
                <button id="toggle-sidebar" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <div class="flex-1 flex justify-center">
                    <div class="relative w-96">
                        <form method="GET" action="index.php" class="relative">
                            <input type="text" class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Search by title, author, category, etc."
                                id="search-input" name="search" value="<?= htmlspecialchars($searchQuery) ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                        </form>
                    </div>
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
            <div class="p-8">
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">Welcome, <?= htmlspecialchars($supervisor_username) ?>!</h1>
                    <p class="text-gray-600 mt-1">Manage your library system efficiently.</p>
                </div>
                <?php if (empty($searchQuery)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-2 rounded-lg bg-green-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-500">Total Books</span>
                            </div>
                            <div class="flex items-end justify-between">
                                <h3 class="text-2xl font-bold text-gray-900"><?= number_format($statistics['books']) ?></h3>
                                <span class="text-sm text-green-500 font-medium">+2.1%</span>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-2 rounded-lg bg-red-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-500">Active Fines</span>
                            </div>
                            <div class="flex items-end justify-between">
                                <h3 class="text-2xl font-bold text-gray-900"><?= number_format($statistics['fines']) ?></h3>
                                <span class="text-sm text-red-500 font-medium">+0.0%</span>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-2 rounded-lg bg-purple-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-purple-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-500">Books Borrowed</span>
                            </div>
                            <div class="flex items-end justify-between">
                                <h3 class="text-2xl font-bold text-gray-900"><?= number_format($statistics['borrowed']) ?></h3>
                                <span class="text-sm text-green-500 font-medium">+1.2%</span>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-2 rounded-lg bg-red-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-500">Book Requests</span>
                            </div>
                            <div class="flex items-end justify-between">
                                <h3 class="text-2xl font-bold text-gray-900"><?= number_format($statistics['requests']) ?></h3>
                                <span class="text-sm text-red-500 font-medium">+0.0%</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Books by Category</h3>
                            <canvas id="booksChart" class="w-full" height="500"></canvas>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Fines Distribution</h3>
                            <canvas id="finesChart" class="w-full" height="500"></canvas>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Borrowed Books Status</h3>
                            <canvas id="borrowedChart" class="w-full" height="300"></canvas>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Borrow Request Status</h3>
                            <canvas id="borrowRequestChart" class="w-full" height="300"></canvas>
                        </div>

                    </div>
                <?php endif; ?>

                <?php if (!empty($searchQuery)): ?>
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Search Results</h3>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rack</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while ($book = $searchResults->fetch_assoc()): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($book['book_name']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($book['author_name']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($book['category']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($book['rack']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
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

    <script>
        // Profile Menu Toggle
        function toggleProfileMenu() {
            const menu = document.getElementById('profile-menu');
            menu.classList.toggle('hidden');
        }

        // User Profile Modal
        function showUserProfile() {
            document.getElementById('userprofile').classList.remove('hidden');
            document.getElementById('profile-menu').classList.add('hidden');
        }

        function closeUserProfile() {
            document.getElementById('userprofile').classList.add('hidden');
        }

        // Change Password Modal
        function showChangePassword() {
            document.getElementById('changepassword').classList.remove('hidden');
            document.getElementById('profile-menu').classList.add('hidden');
        }

        function closeChangePassword() {
            document.getElementById('changepassword').classList.add('hidden');
        }

        // Sidebar Toggle
        document.getElementById('toggle-sidebar').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            sidebar.classList.toggle('hidden');
            if (sidebar.classList.contains('hidden')) {
                mainContent.classList.remove('ml-64');
            } else {
                mainContent.classList.add('ml-64');
            }
        });

        // Charts
        const membersData = <?= json_encode(array_column($memberRoles, 'count')) ?>;
        const membersLabels = <?= json_encode(array_column($memberRoles, 'role')) ?>;

        const categoriesData = <?= json_encode(array_column($bookCategories, 'count')) ?>;
        const categoriesLabels = <?= json_encode(array_column($bookCategories, 'category')) ?>;

        const finesData = <?= json_encode(array_column($finesByAmount, 'count')) ?>;
        const finesLabels = <?= json_encode(array_column($finesByAmount, 'amount_range')) ?>;

        const borrowedData = <?= json_encode(array_column($borrowedBooks, 'count')) ?>;
        const borrowedLabels = <?= json_encode(array_column($borrowedBooks, 'status')) ?>;
        const borrowRequestData = <?= json_encode(array_column($borrowRequestStatus, 'count')) ?>;
        const borrowRequestLabels = <?= json_encode(array_column($borrowRequestStatus, 'status')) ?>;

        // Books Chart
        new Chart(document.getElementById('booksChart'), {
            type: 'bar',
            data: {
                labels: categoriesLabels,
                datasets: [{
                    label: 'Books',
                    data: categoriesData,
                    backgroundColor: '#60A5FA',
                    borderColor: '#2563EB',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Fines Chart
        new Chart(document.getElementById('finesChart'), {
            type: 'line',
            data: {
                labels: finesLabels,
                datasets: [{
                    label: 'Fines',
                    data: finesData,
                    fill: true,
                    backgroundColor: 'rgba(248, 113, 113, 0.2)',
                    borderColor: '#EF4444',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        new Chart(document.getElementById('borrowedChart'), {
            type: 'doughnut',
            data: {
                labels: [...borrowedLabels, ...borrowRequestLabels],
                datasets: [{
                    data: [...borrowedData, ...borrowRequestData],
                    backgroundColor: ['#34D399', '#F87171', '#60A5FA', '#A78BFA'],
                    borderColor: '#FFFFFF',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        new Chart(document.getElementById('borrowRequestChart'), {
            type: 'doughnut',
            data: {
                labels: borrowRequestLabels,
                datasets: [{
                    data: borrowRequestData,
                    backgroundColor: ['#34D399', '#F87171', '#60A5FA', '#A78BFA'],
                    borderColor: '#FFFFFF',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>

</html>