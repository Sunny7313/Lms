<?php
session_start();
include '../config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$admin_username = $_SESSION['username'];
$email = $_SESSION['email'];

function fetchAdminProfile($conn, $email)
{
    $query = "SELECT * FROM member_list WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

$adminProfile = fetchAdminProfile($conn, $email);

function searchBooks($conn, $searchTerm)
{
    $query = "SELECT * FROM books WHERE book_name LIKE ? OR author_name LIKE ? OR category LIKE ? OR rack LIKE ?";
    $stmt = $conn->prepare($query);
    $searchTerm = "%$searchTerm%";
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    return $stmt->get_result();
}

function fetchStatistics($conn)
{
    $stats = [];
    $stats['members'] = $conn->query("SELECT COUNT(*) AS count FROM member_list")->fetch_assoc()['count'];
    $stats['books'] = $conn->query("SELECT COUNT(*) AS count FROM books")->fetch_assoc()['count'];
    $stats['fines'] = $conn->query("SELECT COUNT(*) AS count FROM fines")->fetch_assoc()['count'];
    $stats['borrowed'] = $conn->query("SELECT COUNT(*) AS count FROM borrow_list")->fetch_assoc()['count'];
    return $stats;
}

function fetchBookcategorys($conn)
{
    $query = "SELECT category, COUNT(*) AS count FROM books GROUP BY category";
    $result = $conn->query($query);
    $categorys = [];
    while ($row = $result->fetch_assoc()) {
        $categorys[] = $row;
    }
    return $categorys;
}

function fetchMemberRoles($conn)
{
    $query = "SELECT role, COUNT(*) AS count FROM member_list GROUP BY role";
    $result = $conn->query($query);
    $roles = [];
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row;
    }
    return $roles;
}

function fetchMemberBranches($conn)
{
    $query = "SELECT branch, COUNT(*) AS count FROM member_list GROUP BY branch";
    $result = $conn->query($query);
    $branches = [];
    while ($row = $result->fetch_assoc()) {
        $branches[] = $row;
    }
    return $branches;
}

function fetchMemberSections($conn)
{
    $query = "SELECT section, COUNT(*) AS count FROM member_list GROUP BY section";
    $result = $conn->query($query);
    $sections = [];
    while ($row = $result->fetch_assoc()) {
        $sections[] = $row;
    }
    return $sections;
}

function fetchBorrowedBooks($conn)
{
    $query = "SELECT status, COUNT(*) AS count FROM borrow_list GROUP BY status";
    $result = $conn->query($query);
    $borrowedBooks = [];
    while ($row = $result->fetch_assoc()) {
        $borrowedBooks[] = $row;
    }
    return $borrowedBooks;
}

function fetchFinesByAmount($conn)
{
    $query = "SELECT 
                CASE 
                    WHEN fine_amount < 5 THEN 'Less than ₹5'
                    WHEN fine_amount BETWEEN 5 AND 10 THEN '₹5 - ₹10'
                    WHEN fine_amount BETWEEN 10 AND 20 THEN '₹10 - ₹20'
                    ELSE 'More than ₹20'
                END AS amount_range,
                COUNT(*) AS count 
              FROM fines 
              GROUP BY amount_range";
    $result = $conn->query($query);
    $fines = [];
    while ($row = $result->fetch_assoc()) {
        $fines[] = $row;
    }
    return $fines;
}

$searchResults = [];
$searchQuery = '';
if (isset($_GET['search'])) {
    $searchQuery = $_GET['search'];
    $searchResults = searchBooks($conn, $searchQuery);
}

$statistics = fetchStatistics($conn);
$bookcategorys = fetchBookcategorys($conn);
$memberRoles = fetchMemberRoles($conn);
$memberBranches = fetchMemberBranches($conn);
$memberSections = fetchMemberSections($conn);
$borrowedBooks = fetchBorrowedBooks($conn);
$finesByAmount = fetchFinesByAmount($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Library App</title>
    <link rel="stylesheet" href="../src/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .statistics-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .chart-container {
            flex: 1 1 calc(50% - 20px);
            max-width: calc(50% - 20px);
        }
        .chart-heading {
            text-align: center;
            font-size: 1.2em;
            margin-bottom: 10px;
        }
        .divider {
            border-top: 2px solid #ccc;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="app">
        <aside class="sidebar">
            <div class="logo">
                <div class="logo-icon"><img src="../photos/college.png" width="120px" height="35px"></div>
                <span class="logo-text">Library</span>
            </div>
            <nav class="nav">
                <a href="index.php" class="nav-link active">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        <polyline points="9 22 9 12 15 12 15 22" />
                    </svg>
                    Dashboard
                </a>
                <a href="manage_books.php" class="nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 4h9a2 2 0 0 1 2 2v14a2 2 0 0 0-2-2H2z"></path>
                        <path d="M22 4h-9a2 2 0 0 0-2 2v14a2 2 0 0 1 2-2h9z"></path>
                        <line x1="12" y1="6" x2="12" y2="20"></line>
                    </svg>
                    Manage Books
                </a>
                <a href="manage_members.php" class="nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    Manage Members
                </a>
                <a href="manage_borrow.php" class="nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect>
                        <path d="M7 4v5l2.5-1.5L12 9V4"></path>
                        <path d="M16 13l-4 4-4-4"></path>
                        <line x1="12" y1="17" x2="12" y2="7"></line>
                    </svg>
                    Manage Borrow
                </a>
                <a href="manage_fines.php" class="nav-link">
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

        <main class="main">
        <header class="header">
                <div class="search-container">
                    <form method="GET" action="index.php">
                        <input type="text" class="search-input" placeholder="Search by title, author, category, etc."
                            id="search-input" name="search" value="<?= htmlspecialchars($searchQuery) ?>">
                        <button type="submit" class="search-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4.3-4.3" />
                            </svg>
                        </button>
                    </form>
                </div>
                <div class="header-actions">
                    <button class="notification-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                        </svg>
                    </button>
                    <div class="avatar" onclick="toggleProfileMenu()">
                        <img src="../photos/bssdata.png" alt="User avatar">
                    </div>
                </div>
            </header>
            <div class="profile-menu" id="profile-menu">
                <a href="#userprofile" onclick="showUserProfile()">View Profile</a>
                <a href="#changepassword" onclick="showChangePassword()">Change Password</a>
                <a href="logout.php">Logout</a>
            </div>
            <section class="dashboard">
                <h2>Welcome, <?= $adminProfile['username'] ?>!</h2>
                <p>Use the sidebar to manage books, members, borrow details, and fines.</p>
                    <div class="divider"></div>
                <div class="statistics-grid">
                    <div class="chart-container">
                        <div class="chart-heading">Members by Role</div>
                        <canvas id="membersChart" style="max-height: 150px;"></canvas>
                    </div>
                    <div class="chart-container">
                        <div class="chart-heading">Books by Category</div>
                        <canvas id="booksChart" style="max-height: 150px;"></canvas>
                    </div>
                    <div class="chart-container">
                        <div class="chart-heading">Fines by Amount Range</div>
                        <canvas id="finesChart" style="max-height: 150px;"></canvas>
                    </div>
                    <div class="chart-container">
                        <div class="chart-heading">Borrowed Books by Status</div>
                        <canvas id="borrowedChart" style="max-height: 150px;"></canvas>
                    </div>
                </div>
                <?php if (!empty($searchQuery)): ?>
                    <h3>Search Results:</h3>
                    <table class="table">
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Rack</th>
                        </tr>
                        <?php while ($book = $searchResults->fetch_assoc()): ?>
                            <tr>
                                <td><?= $book['book_name'] ?></td>
                                <td><?= $book['author_name'] ?></td>
                                <td><?= $book['category'] ?></td>
                                <td><?= $book['rack'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <div id="userprofile" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeUserProfile()">&times;</span>
            <h2>Admin Profile</h2>
            <table class="table">
                <tr>
                    <th>Username</th>
                    <td><?= $adminProfile['username'] ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?= $adminProfile['email'] ?></td>
                </tr>
                <tr>
                    <th>Phone Number</th>
                    <td><?= $adminProfile['phone_number'] ?></td>
                </tr>
                <tr>
                    <th>Branch</th>
                    <td><?= $adminProfile['branch'] ?></td>
                </tr>
                <tr>
                    <th>Section</th>
                    <td><?= $adminProfile['section'] ?></td>
                </tr>
            </table>
        </div>
    </div>
    <div id="changepassword" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeChangePassword()">&times;</span>
            <h2>Change Password</h2>
            <form method="POST" action="change_password.php">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
        </div>
    </div>

    <script src="../src/js/script.js"></script>
    <script>
        function toggleAdminProfile() {
            var modal = document.getElementById("admin-profile-modal");
            modal.style.display = (modal.style.display === "block") ? "none" : "block";
        }

        window.onclick = function (event) {
            var modal = document.getElementById("admin-profile-modal");
            if (event.target === modal) {
                modal.style.display = "none";
            }
        }

        const membersCtx = document.getElementById('membersChart').getContext('2d');
        const booksCtx = document.getElementById('booksChart').getContext('2d');
        const finesCtx = document.getElementById('finesChart').getContext('2d');
        const borrowedCtx = document.getElementById('borrowedChart').getContext('2d');

        const membersChart = new Chart(membersCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($memberRoles, 'role')) ?>,
                datasets: [{
                    label: 'Members by Role',
                    data: <?= json_encode(array_column($memberRoles, 'count')) ?>,
                    backgroundColor: ['rgba(75, 192, 192, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(255, 206, 86, 0.2)', 'rgba(153, 102, 255, 0.2)'],
                    borderColor: ['rgba(75, 192, 192, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)', 'rgba(153, 102, 255, 1)'],
                    borderWidth: 1
                }]
            }
        });

        const booksChart = new Chart(booksCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($bookcategorys, 'category')) ?>,
                datasets: [{
                    label: 'Books by Category',
                    data: <?= json_encode(array_column($bookcategorys, 'count')) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const finesChart = new Chart(finesCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($finesByAmount, 'amount_range')) ?>,
                datasets: [{
                    label: 'Fines by Amount Range',
                    data: <?= json_encode(array_column($finesByAmount, 'count')) ?>,
                    backgroundColor: ['rgba(255, 206, 86, 0.2)'],
                    borderColor: ['rgba(255, 206, 86, 1)'],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const borrowedChart = new Chart(borrowedCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($borrowedBooks, 'status')) ?>,
                datasets: [{
                    label: 'Borrowed Books by Status',
                    data: <?= json_encode(array_column($borrowedBooks, 'count')) ?>,
                    backgroundColor: ['rgba(153, 102, 255, 0.2)', 'rgba(255, 99, 132, 0.2)'],
                    borderColor: ['rgba(153, 102, 255, 1)', 'rgba(255, 99, 132, 1)'],
                    borderWidth: 1
                }]
            }
        });
    </script>
</body>

</html>
