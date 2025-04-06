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
    $fine_id = $_GET['id'];
    $query = "SELECT * FROM fines WHERE fine_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $fine_id);
    $stmt->execute();
    $fine = $stmt->get_result()->fetch_assoc();
    if (!$fine) {
        echo '<script>alert("Fine details not found."); window.location.href = "manage_fines.php";</script>';
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['fine_id'], $_POST['fine_amount'], $_POST['due_date'])) {
        $fine_id = $_POST['fine_id'];
        $fine_amount = $_POST['fine_amount'];
        $due_date = $_POST['due_date'];
        if($fine_amount == 0.00){
            $query = "DELETE FROM fines WHERE fine_amount = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("d", $fine_amount);
        }else{
            $query = "UPDATE fines SET fine_amount = ?, due_date = ? WHERE fine_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("dsi", $fine_amount, $due_date, $fine_id);
        }
        $stmt->execute();
        header("Location: manage_fines.php");
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
    <title>Edit Fine - Library App</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
        <main class="ml-64 p-6">
            <header class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Edit Fine</h1>
            </header>
            <section>
                <form action="edit_fine.php" method="POST" class="space-y-6">
                    <input type="hidden" name="fine_id" value="<?php echo $fine['fine_id']; ?>">
                    <div class="space-y-2">
                        <label for="fine_amount" class="block text-sm font-medium text-gray-700">Fine Amount</label>
                        <input type="number" id="fine_amount" name="fine_amount" value="<?php echo $fine['fine_amount']; ?>" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="space-y-2">
                        <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                        <input type="date" id="due_date" name="due_date" value="<?php echo $fine['due_date']; ?>" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Update Fine</button>
                </form>
            </section>
        </main>
    </div>
</body>

</html>
