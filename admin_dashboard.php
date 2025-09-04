<?php
session_start();
require_once('connection/connection.php');

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Get statistics from database
$conn = con();

// Get total users count
$total_users_query = "SELECT COUNT(*) as total FROM user_tbl";
$total_users_result = mysqli_query($conn, $total_users_query);
$total_users = mysqli_fetch_assoc($total_users_result)['total'];

// Get drivers count
$drivers_query = "SELECT COUNT(*) as total FROM user_tbl WHERE usert_type = 'driver'";
$drivers_result = mysqli_query($conn, $drivers_query);
$total_drivers = mysqli_fetch_assoc($drivers_result)['total'];

// Get commuters count
$commuters_query = "SELECT COUNT(*) as total FROM user_tbl WHERE usert_type = 'user'";
$commuters_result = mysqli_query($conn, $commuters_query);
$total_commuters = mysqli_fetch_assoc($commuters_result)['total'];

// Get all drivers for management
$drivers_list_query = "SELECT * FROM user_tbl WHERE usert_type = 'driver' ORDER BY date_created DESC";
$drivers_list_result = mysqli_query($conn, $drivers_list_query);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <title>Jeepney Tracking — Admin Dashboard</title>
    <link rel="stylesheet" href="src/output.css" />
    <!-- Leaflet CSS for maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.tailwindcss.min.css">
    <style>
        #location-map { height: 400px; width: 100%; }
        .driver-online { background-color: #10b981; }
        .driver-offline { background-color: #ef4444; }
        .driver-maintenance { background-color: #f59e0b; }
    </style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-slate-200/60 dark:bg-slate-900/80 dark:border-slate-800/60 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex items-center gap-3">
                        <div class="grid h-8 w-8 place-items-center rounded-xl border border-slate-200 bg-gradient-to-b from-sky-200/40 to-transparent text-[10px] font-bold tracking-wide dark:border-slate-800 dark:from-sky-400/20">
                            JT</div>
                        <span class="font-bold text-lg">Jeepney Tracking</span>
                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded-lg text-sm font-medium dark:bg-red-900 dark:text-red-200">Admin</span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-slate-600 dark:text-slate-400">Welcome, <?php echo htmlspecialchars($username); ?></span>
                    <a href="backend/logout.php" class="text-sm bg-red-100 hover:bg-red-200 text-red-700 px-3 py-2 rounded-lg transition dark:bg-red-900/20 dark:hover:bg-red-900/40 dark:text-red-400">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Dashboard Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Admin Dashboard</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-2">Manage drivers, monitor system activity, and oversee jeepney operations.</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['register_success'])): ?>
        <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/30 rounded-xl p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <p class="text-green-800 dark:text-green-400"><?php echo htmlspecialchars($_SESSION['register_success']); unset($_SESSION['register_success']); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['register_error'])): ?>
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30 rounded-xl p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <p class="text-red-800 dark:text-red-400"><?php echo htmlspecialchars($_SESSION['register_error']); unset($_SESSION['register_error']); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- System Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl p-6 border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 dark:text-slate-400 text-sm">Total Users</p>
                        <p class="text-2xl font-bold"><?php echo $total_users; ?></p>
                    </div>
                    <div class="h-12 w-12 bg-blue-100 dark:bg-blue-900/20 rounded-xl flex items-center justify-center">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl p-6 border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 dark:text-slate-400 text-sm">Active Drivers</p>
                        <p class="text-2xl font-bold" id="active-drivers"><?php echo $total_drivers; ?></p>
                    </div>
                    <div class="h-12 w-12 bg-green-100 dark:bg-green-900/20 rounded-xl flex items-center justify-center">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl p-6 border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 dark:text-slate-400 text-sm">Commuters</p>
                        <p class="text-2xl font-bold"><?php echo $total_commuters; ?></p>
                    </div>
                    <div class="h-12 w-12 bg-purple-100 dark:bg-purple-900/20 rounded-xl flex items-center justify-center">
                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl p-6 border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 dark:text-slate-400 text-sm">Online Jeepneys</p>
                        <p class="text-2xl font-bold" id="online-jeepneys">8</p>
                    </div>
                    <div class="h-12 w-12 bg-orange-100 dark:bg-orange-900/20 rounded-xl flex items-center justify-center">
                        <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Driver Registration Form -->
            <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
                <div class="p-6 border-b border-slate-200/60 dark:border-slate-700/60">
                    <h2 class="text-xl font-semibold">Register New Driver</h2>
                    <p class="text-slate-600 dark:text-slate-400 text-sm mt-1">Add a new driver to the system</p>
                </div>
                <div class="p-6">
                    <form id="driver-registration-form" action="backend/admin_register_driver.php" method="POST" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="driver-firstname" class="block text-sm font-medium mb-1">First Name</label>
                                <input type="text" id="driver-firstname" name="firstname" required class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-sm dark:border-slate-600 dark:bg-slate-700">
                            </div>
                            <div>
                                <label for="driver-lastname" class="block text-sm font-medium mb-1">Last Name</label>
                                <input type="text" id="driver-lastname" name="lastname" required class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-sm dark:border-slate-600 dark:bg-slate-700">
                            </div>
                        </div>
                        <div>
                            <label for="driver-username" class="block text-sm font-medium mb-1">Username</label>
                            <input type="text" id="driver-username" name="username" required class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-sm dark:border-slate-600 dark:bg-slate-700">
                        </div>
                        <div>
                            <label for="driver-password" class="block text-sm font-medium mb-1">Password</label>
                            <input type="password" id="driver-password" name="password" required class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-sm dark:border-slate-600 dark:bg-slate-700">
                        </div>
                        <div>
                            <label for="driver-jeepney-id" class="block text-sm font-medium mb-1">Jeepney ID</label>
                            <input type="text" id="driver-jeepney-id" name="jeepney_id" placeholder="e.g., JP-001" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-sm dark:border-slate-600 dark:bg-slate-700">
                        </div>
                        <div>
                            <label for="driver-route" class="block text-sm font-medium mb-1">Assigned Route</label>
                            <select id="driver-route" name="route" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-sm dark:border-slate-600 dark:bg-slate-700">
                                <option value="">Select Route</option>
                                <option value="divisoria-mabolo">Divisoria → Mabolo</option>
                                <option value="lahug-colon">Lahug → Colon</option>
                                <option value="talamban-itpark">Talamban → IT Park</option>
                                <option value="ayala-sm">Ayala → SM Cebu</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                            Register Driver
                        </button>
                    </form>
                </div>
            </div>

            <!-- Real-time Location Monitoring -->
            <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
                <div class="p-6 border-b border-slate-200/60 dark:border-slate-700/60">
                    <h2 class="text-xl font-semibold">Real-time Location Monitoring</h2>
                    <p class="text-slate-600 dark:text-slate-400 text-sm mt-1">Track all active jeepneys in real-time</p>
                </div>
                <div class="p-6">
                    <div id="location-map" class="rounded-lg border border-slate-200 dark:border-slate-600"></div>
                </div>
            </div>
        </div>

        <!-- Driver Management Table -->
        <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
            <div class="p-6 border-b border-slate-200/60 dark:border-slate-700/60">
                <h2 class="text-xl font-semibold">Driver Management</h2>
                <p class="text-slate-600 dark:text-slate-400 text-sm mt-1">Manage registered drivers and their status</p>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table id="drivers-table" class="w-full text-sm">
                        <thead>
                            <tr class="text-left border-b border-slate-200/60 dark:border-slate-700/60">
                                <th class="pb-3 font-semibold">ID</th>
                                <th class="pb-3 font-semibold">Name</th>
                                <th class="pb-3 font-semibold">Username</th>
                                <th class="pb-3 font-semibold">Jeepney ID</th>
                                <th class="pb-3 font-semibold">Status</th>
                                <th class="pb-3 font-semibold">Registered</th>
                                <th class="pb-3 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/60 dark:divide-slate-700/60">
                            <?php while ($driver = mysqli_fetch_assoc($drivers_list_result)): ?>
                            <tr>
                                <td class="py-3 font-medium"><?php echo $driver['id']; ?></td>
                                <td class="py-3"><?php echo htmlspecialchars($driver['firstname'] . ' ' . $driver['lastname']); ?></td>
                                <td class="py-3"><?php echo htmlspecialchars($driver['username']); ?></td>
                                <td class="py-3">JP-<?php echo str_pad($driver['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td class="py-3">
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs dark:bg-green-900/20 dark:text-green-400">Active</span>
                                </td>
                                <td class="py-3"><?php echo date('M d, Y', $driver['date_created']); ?></td>
                                <td class="py-3">
                                    <div class="flex gap-2">
                                        <button class="text-blue-600 hover:text-blue-800 text-xs font-medium edit-driver" data-driver-id="<?php echo $driver['id']; ?>">Edit</button>
                                        <button class="text-red-600 hover:text-red-800 text-xs font-medium delete-driver" data-driver-id="<?php echo $driver['id']; ?>">Delete</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- System Data Management -->
        <div class="mt-8 bg-white/70 dark:bg-slate-800/70 rounded-xl border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
            <div class="p-6 border-b border-slate-200/60 dark:border-slate-700/60">
                <h2 class="text-xl font-semibold">System Data Management</h2>
                <p class="text-slate-600 dark:text-slate-400 text-sm mt-1">Export data, generate reports, and system maintenance tools</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <button class="p-4 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/10 dark:hover:bg-blue-900/20 rounded-lg text-left transition" id="export-users">
                        <div class="text-blue-600 dark:text-blue-400 mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="font-medium mb-1">Export User Data</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Download all user records</p>
                    </button>

                    <button class="p-4 bg-green-50 hover:bg-green-100 dark:bg-green-900/10 dark:hover:bg-green-900/20 rounded-lg text-left transition" id="generate-report">
                        <div class="text-green-600 dark:text-green-400 mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="font-medium mb-1">Generate Reports</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Create system usage reports</p>
                    </button>

                    <button class="p-4 bg-purple-50 hover:bg-purple-100 dark:bg-purple-900/10 dark:hover:bg-purple-900/20 rounded-lg text-left transition" id="backup-data">
                        <div class="text-purple-600 dark:text-purple-400 mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                            </svg>
                        </div>
                        <h3 class="font-medium mb-1">Backup Database</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Create system backup</p>
                    </button>

                    <button class="p-4 bg-orange-50 hover:bg-orange-100 dark:bg-orange-900/10 dark:hover:bg-orange-900/20 rounded-lg text-left transition" id="system-logs">
                        <div class="text-orange-600 dark:text-orange-400 mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="font-medium mb-1">System Logs</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400">View system activity logs</p>
                    </button>

                    <button class="p-4 bg-red-50 hover:bg-red-100 dark:bg-red-900/10 dark:hover:bg-red-900/20 rounded-lg text-left transition" id="maintenance-mode">
                        <div class="text-red-600 dark:text-red-400 mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <h3 class="font-medium mb-1">Maintenance Mode</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400">System maintenance tools</p>
                    </button>

                    <button class="p-4 bg-gray-50 hover:bg-gray-100 dark:bg-gray-900/10 dark:hover:bg-gray-900/20 rounded-lg text-left transition" id="settings">
                        <div class="text-gray-600 dark:text-gray-400 mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                            </svg>
                        </div>
                        <h3 class="font-medium mb-1">System Settings</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Configure system parameters</p>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.tailwindcss.min.js"></script>
    <script>
        // Initialize location monitoring map
        const locationMap = L.map('location-map').setView([10.3157, 123.8854], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(locationMap);

        // Sample active jeepney locations for monitoring
        const activeJeepneys = [
            { id: 'JP-001', lat: 10.3157, lng: 123.8854, driver: 'Juan Dela Cruz', status: 'online', passengers: 6 },
            { id: 'JP-002', lat: 10.3200, lng: 123.8900, driver: 'Maria Santos', status: 'online', passengers: 4 },
            { id: 'JP-003', lat: 10.3100, lng: 123.8800, driver: 'Pedro Rodriguez', status: 'online', passengers: 8 },
            { id: 'JP-004', lat: 10.3250, lng: 123.8750, driver: 'Ana Garcia', status: 'maintenance', passengers: 0 },
            { id: 'JP-005', lat: 10.3080, lng: 123.8920, driver: 'Carlos Lopez', status: 'online', passengers: 12 }
        ];

        // Add jeepney markers to location monitoring map
        activeJeepneys.forEach(jeepney => {
            const color = jeepney.status === 'online' ? 'green' : 
                         jeepney.status === 'maintenance' ? 'orange' : 'red';
            
            const marker = L.marker([jeepney.lat, jeepney.lng]).addTo(locationMap);
            marker.bindPopup(`
                <div class="text-sm">
                    <strong>${jeepney.id}</strong><br>
                    Driver: ${jeepney.driver}<br>
                    Status: <span class="capitalize">${jeepney.status}</span><br>
                    Passengers: ${jeepney.passengers}/14<br>
                    <button class="mt-2 px-2 py-1 bg-blue-500 text-white rounded text-xs" onclick="contactDriver('${jeepney.id}')">Contact Driver</button>
                </div>
            `);
        });

        // Initialize DataTable for drivers management
        $(document).ready(function() {
            $('#drivers-table').DataTable({
                responsive: true,
                pageLength: 10,
                searching: true,
                ordering: true,
                info: true,
                lengthChange: false
            });
        });

        // Driver registration form handling
        document.getElementById('driver-registration-form').addEventListener('submit', function(e) {
            // Let the form submit normally to the backend - no prevent default
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Registering...';
            
            // Re-enable the button after a delay in case of redirect
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }, 3000);
        });

        // Edit driver functionality
        document.querySelectorAll('.edit-driver').forEach(button => {
            button.addEventListener('click', function() {
                const driverId = this.dataset.driverId;
                
                Swal.fire({
                    title: 'Edit Driver',
                    html: `
                        <div class="text-left space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">First Name</label>
                                <input id="edit-firstname" type="text" class="w-full px-3 py-2 border rounded">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Last Name</label>
                                <input id="edit-lastname" type="text" class="w-full px-3 py-2 border rounded">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Status</label>
                                <select id="edit-status" class="w-full px-3 py-2 border rounded">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire('Updated!', 'Driver information has been updated.', 'success');
                    }
                });
            });
        });

        // Delete driver functionality
        document.querySelectorAll('.delete-driver').forEach(button => {
            button.addEventListener('click', function() {
                const driverId = this.dataset.driverId;
                
                Swal.fire({
                    title: 'Delete Driver?',
                    text: 'This action cannot be undone. The driver will be permanently removed from the system.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Yes, delete!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // In real app, send delete request to server
                        Swal.fire('Deleted!', 'Driver has been removed from the system.', 'success');
                    }
                });
            });
        });

        // System data management tools
        document.getElementById('export-users').addEventListener('click', function() {
            Swal.fire('Exporting Data...', 'Please wait while we prepare your export file.', 'info');
            // Simulate export process
            setTimeout(() => {
                Swal.fire('Export Ready!', 'Your user data export has been generated.', 'success');
            }, 2000);
        });

        document.getElementById('generate-report').addEventListener('click', function() {
            Swal.fire({
                title: 'Generate Report',
                html: `
                    <div class="text-left space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Report Type</label>
                            <select class="w-full px-3 py-2 border rounded">
                                <option>User Activity Report</option>
                                <option>Driver Performance Report</option>
                                <option>System Usage Report</option>
                                <option>Revenue Report</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Date Range</label>
                            <input type="date" class="w-full px-3 py-2 border rounded mb-2">
                            <input type="date" class="w-full px-3 py-2 border rounded">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Generate',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Generating...', 'Your report is being created.', 'info');
                    setTimeout(() => {
                        Swal.fire('Report Generated!', 'Your report is ready for download.', 'success');
                    }, 2000);
                }
            });
        });

        document.getElementById('backup-data').addEventListener('click', function() {
            Swal.fire({
                title: 'Create Backup?',
                text: 'This will create a complete backup of the system database.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Create Backup',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Creating Backup...', 'Please wait while we backup the database.', 'info');
                    setTimeout(() => {
                        Swal.fire('Backup Complete!', 'System backup has been created successfully.', 'success');
                    }, 3000);
                }
            });
        });

        // Function to contact driver (for popup buttons)
        window.contactDriver = function(jeepneyId) {
            Swal.fire({
                title: `Contact ${jeepneyId} Driver`,
                html: `
                    <div class="text-left space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Message Type</label>
                            <select class="w-full px-3 py-2 border rounded">
                                <option>Emergency Alert</option>
                                <option>Route Change</option>
                                <option>Maintenance Required</option>
                                <option>General Message</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Message</label>
                            <textarea class="w-full px-3 py-2 border rounded" rows="3" placeholder="Enter your message..."></textarea>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Send Message',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Message Sent!', 'Your message has been delivered to the driver.', 'success');
                }
            });
        };

        // Update statistics periodically
        function updateStatistics() {
            const onlineCount = activeJeepneys.filter(j => j.status === 'online').length;
            document.getElementById('online-jeepneys').textContent = onlineCount;
        }

        // Update every 30 seconds
        setInterval(updateStatistics, 30000);
        updateStatistics(); // Initial call
    </script>
</body>

</html>