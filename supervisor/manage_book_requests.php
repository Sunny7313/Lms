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

$email = $_SESSION['email'];
$userProfile = fetchUserProfile($conn, $email);

$supervisorProfile = fetchUserProfile($conn, $email);
$searchResults = [];
$searchQuery = '';

if (isset($_GET['search'])) {
    $searchQuery = htmlspecialchars(trim($_GET['search']));
    $result = retrieveBookRequests($conn, $searchQuery);
    if ($result === false) {
        echo "<p>Error executing search query: " . htmlspecialchars($conn->error) . "</p>";
        $searchResults = [];
    } else {
        $searchResults = $result;
    }
} else {
    $query = "SELECT * FROM book_requests LIMIT ?, ?";
    $limit = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $offset, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Book Requests - Library App</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
                <a href="manage_book_requests.php" class="flex items-center gap-3 p-2 text-gray-700 hover:bg-gray-100 rounded-lg active">
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
                <div class="relative w-96">
                    <form method="GET" action="manage_book_requests.php" class="relative">
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
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Manage Book Requests</h2>
                </div>
                <table class="min-w-full bg-white border border-gray-200 rounded-md">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">Request ID</th>
                            <th class="py-2 px-4 border-b">Book ID</th>
                            <th class="py-2 px-4 border-b">User ID</th>
                            <th class="py-2 px-4 border-b">Status</th>
                            <th class="py-2 px-4 border-b">Request Date</th>
                            <th class="py-2 px-4 border-b">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="py-2 px-4 border-b"><?php echo isset($row['request_id']) ? $row['request_id'] : 'N/A'; ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo $row['book_id']; ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo $row['user_id']; ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo $row['status']; ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo $row['request_date']; ?></td>
                                    <td class="py-2 px-4 border-b">
                                        <a href="accept.php?id=<?= $row['request_id'] ?>" class="text-green-500"><i class="fa fa-check"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <a href="reject.php?id=<?= $row['request_id'] ?>" class="text-red-500"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">No book requests found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
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
        function toggleProfileMenu() {
            const menu = document.getElementById('profile-menu');
            menu.classList.toggle('hidden');
        }

        function showUserProfile() {
            document.getElementById('userprofile').classList.remove('hidden');
            document.getElementById('profile-menu').classList.add('hidden');
        }

        function closeUserProfile() {
            document.getElementById('userprofile').classList.add('hidden');
        }

        function showChangePassword() {
            document.getElementById('changepassword').classList.remove('hidden');
            document.getElementById('profile-menu').classList.add('hidden');
        }

        function closeChangePassword() {
            document.getElementById('changepassword').classList.add('hidden');
        }

        document.getElementById('toggle-sidebar').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            sidebar.classList.toggle('hidden');
            mainContent.classList.toggle('ml-64');
        });
    </script>
</body>

</html>