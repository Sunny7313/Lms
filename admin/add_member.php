<?php
session_start();
include '../config.php';
include '../includes/message_popup.php';
include '../calculate_fines.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['pin_number'], $_POST['username'], $_POST['email'], $_POST['password'], $_POST['phone_number'], $_POST['branch'], $_POST['gender'], $_POST['address'], $_POST['role'])) {
        $pin_number = $_POST['pin_number'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $phone_number = $_POST['phone_number'];
        $branch = $_POST['branch'];
        $section = isset($_POST['section']) ? $_POST['section'] : null;
        $year = isset($_POST['year']) ? $_POST['year'] : null;
        $gender = $_POST['gender'];
        $address = $_POST['address'];
        $role = $_POST['role'];

        // Check for duplicate pin_number
        $check_query = "SELECT * FROM member_list WHERE pin_number = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $pin_number);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['message'] = "Pin number already exists.";
            $_SESSION['message_type'] = "error";
        } else {
            $query = "INSERT INTO member_list (pin_number, username, email, password, phone_number, branch, section, year, gender, address, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssssssss", $pin_number, $username, $email, $password, $phone_number, $branch, $section, $year, $gender, $address, $role);
            $stmt->execute();

            $_SESSION['message'] = "Member added successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: manage_members.php");
            exit();
        }
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
    <title>Add Member - Library App</title>
    <link rel="stylesheet" href="../src/css/styles.css">
    <style>
        select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--border);
            border-radius: 0.25rem;
            outline: none;
            font-size: 1rem;
            background-color: white;
            appearance: none;
            background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 4 5"><path fill="none" stroke="currentColor" stroke-width=".5" d="M2 0L0 2h4zm0 5L0 3h4z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 0.65rem auto;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        function toggleFields() {
            const role = document.getElementById('role').value;
            const yearField = document.getElementById('year-field');
            const sectionField = document.getElementById('section-field');
            const yearInput = document.getElementById('year');
            const sectionInput = document.getElementById('section');

            if (role === 'faculty' || role === 'supervisor' || role === 'admin') {
                yearField.style.display = 'none';
                sectionField.style.display = 'none';
                yearInput.removeAttribute('required');
                sectionInput.removeAttribute('required');
            } else {
                yearField.style.display = 'block';
                sectionField.style.display = 'block';
                yearInput.setAttribute('required', 'required');
                sectionInput.setAttribute('required', 'required');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('role').addEventListener('change', toggleFields);
            toggleFields(); // Initial call to set the correct state on page load
        });
    </script>
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
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                    <span class="font-medium">Manage Borrow</span>
                </a>
                <a href="manage_fines.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                    <span class="font-medium">Manage Fines</span>
                </a>
            </nav>
        </aside>
        <main class="ml-64 min-h-screen">
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Add Member</h1>
                </div>
            </header>
            <div class="p-8">
                <section class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <form action="add_member.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="pin_number" class="block text-sm font-medium text-gray-700">Pin Number</label>
                            <input type="text" id="pin_number" name="pin_number" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                            <input type="text" id="username" name="username" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="username" class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" id="password" name="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" id="email" name="email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="text" id="phone_number" name="phone_number" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="branch" class="block text-sm font-medium text-gray-700">Branch</label>
                            <input type="text" id="branch" name="branch" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div id="section-field" class="mb-4">
                            <label for="section" class="block text-sm font-medium text-gray-700">Section</label>
                            <input type="text" id="section" name="section" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div id="year-field" class="mb-4">
                            <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
                            <input type="number" id="year" name="year" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                            <input type="text" id="gender" name="gender" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <input type="text" id="address" name="address" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                            <select id="role" name="role" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="admin">Admin</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="member">Member</option>
                                <option value="faculty">Faculty</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition-colors">Add Member</button>
                    </form>
                </section>
            </div>
        </main>
    </div>
</body>

</html>