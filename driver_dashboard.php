<?php
session_start();
require_once('connection/connection.php');

// Check if user is logged in and is a driver
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'driver') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Get driver info from database
$conn = con();
$query = "SELECT * FROM user_tbl WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$driver = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <title>Jeepney Tracking — Driver Dashboard</title>
    <link rel="stylesheet" href="src/output.css" />
    <!-- Leaflet CSS for maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        #traffic-map { height: 300px; width: 100%; }
        .status-online { background-color: #10b981; }
        .status-offline { background-color: #ef4444; }
        .status-maintenance { background-color: #f59e0b; }
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
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-lg text-sm font-medium dark:bg-green-900 dark:text-green-200">Driver</span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <div id="status-indicator" class="h-3 w-3 rounded-full status-offline"></div>
                        <span class="text-sm text-slate-600 dark:text-slate-400" id="status-text">Offline</span>
                    </div>
                    <span class="text-sm text-slate-600 dark:text-slate-400">Welcome, <?php echo htmlspecialchars($username); ?></span>
                    <a href="backend/logout.php" class="text-sm bg-red-100 hover:bg-red-200 text-red-700 px-3 py-2 rounded-lg transition dark:bg-red-900/20 dark:hover:bg-red-900/40 dark:text-red-400">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Dashboard Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Driver Dashboard</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-2">Manage your jeepney operations, passenger count, and monitor traffic conditions.</p>
        </div>

        <!-- Status Controls -->
        <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl p-6 border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm mb-8">
            <h2 class="text-xl font-semibold mb-4">Jeepney Status Control</h2>
            <div class="flex flex-wrap gap-4 items-center">
                <button id="go-online" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition font-medium">Go Online</button>
                <button id="go-offline" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition font-medium">Go Offline</button>
                <button id="maintenance-mode" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition font-medium">Maintenance</button>
                <div class="flex items-center gap-2 ml-4">
                    <label for="jeepney-id" class="text-sm font-medium">Jeepney ID:</label>
                    <input type="text" id="jeepney-id" value="JP-001" class="px-3 py-1 border border-slate-200 rounded-lg bg-white text-sm dark:border-slate-600 dark:bg-slate-700" readonly>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Passenger Count Management -->
            <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
                <div class="p-6 border-b border-slate-200/60 dark:border-slate-700/60">
                    <h2 class="text-xl font-semibold">Passenger Count Management</h2>
                    <p class="text-slate-600 dark:text-slate-400 text-sm mt-1">Update current passenger count and seat availability</p>
                </div>
                <div class="p-6">
                    <!-- Current Count Display -->
                    <div class="text-center mb-6">
                        <div class="text-4xl font-bold mb-2" id="current-passenger-count">6</div>
                        <p class="text-slate-600 dark:text-slate-400">Current Passengers</p>
                        <div class="text-sm text-slate-500 mt-1">
                            Capacity: <span class="font-medium">14 passengers</span>
                        </div>
                    </div>

                    <!-- Quick Count Controls -->
                    <div class="flex justify-center gap-4 mb-6">
                        <button id="add-passenger" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Passenger
                        </button>
                        <button id="remove-passenger" class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                            Remove Passenger
                        </button>
                    </div>

                    <!-- Manual Count Update -->
                    <div class="border-t border-slate-200/60 dark:border-slate-700/60 pt-4">
                        <label for="manual-count" class="block text-sm font-medium mb-2">Set Exact Count:</label>
                        <div class="flex gap-2">
                            <input type="number" id="manual-count" min="0" max="14" class="flex-1 px-3 py-2 border border-slate-200 rounded-lg bg-white text-sm dark:border-slate-600 dark:bg-slate-700" placeholder="Enter passenger count">
                            <button id="update-count" class="px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg text-sm font-medium transition">Update</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Real-time Traffic Updates -->
            <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
                <div class="p-6 border-b border-slate-200/60 dark:border-slate-700/60">
                    <h2 class="text-xl font-semibold">Real-time Traffic Updates</h2>
                    <p class="text-slate-600 dark:text-slate-400 text-sm mt-1">Current traffic conditions on your route</p>
                </div>
                <div class="p-6">
                    <div id="traffic-map" class="rounded-lg border border-slate-200 dark:border-slate-600 mb-4"></div>
                    
                    <!-- Traffic Alerts -->
                    <div class="space-y-3">
                        <div class="flex items-start gap-3 p-3 bg-red-50 dark:bg-red-900/10 rounded-lg border border-red-200 dark:border-red-800/30">
                            <div class="h-2 w-2 rounded-full bg-red-500 mt-2 flex-shrink-0"></div>
                            <div>
                                <p class="text-sm font-medium text-red-800 dark:text-red-400">Heavy Traffic Alert</p>
                                <p class="text-xs text-red-600 dark:text-red-300">Colon Street - 15 min delay expected</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3 p-3 bg-yellow-50 dark:bg-yellow-900/10 rounded-lg border border-yellow-200 dark:border-yellow-800/30">
                            <div class="h-2 w-2 rounded-full bg-yellow-500 mt-2 flex-shrink-0"></div>
                            <div>
                                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-400">Construction Zone</p>
                                <p class="text-xs text-yellow-600 dark:text-yellow-300">Mabolo area - Use alternate route</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3 p-3 bg-green-50 dark:bg-green-900/10 rounded-lg border border-green-200 dark:border-green-800/30">
                            <div class="h-2 w-2 rounded-full bg-green-500 mt-2 flex-shrink-0"></div>
                            <div>
                                <p class="text-sm font-medium text-green-800 dark:text-green-400">Clear Traffic</p>
                                <p class="text-xs text-green-600 dark:text-green-300">Lahug Boulevard - Normal flow</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl p-6 border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 dark:text-slate-400 text-sm">Today's Trips</p>
                        <p class="text-2xl font-bold" id="todays-trips">8</p>
                    </div>
                    <div class="h-12 w-12 bg-blue-100 dark:bg-blue-900/20 rounded-xl flex items-center justify-center">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl p-6 border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 dark:text-slate-400 text-sm">Total Passengers</p>
                        <p class="text-2xl font-bold" id="total-passengers">142</p>
                    </div>
                    <div class="h-12 w-12 bg-green-100 dark:bg-green-900/20 rounded-xl flex items-center justify-center">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl p-6 border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 dark:text-slate-400 text-sm">Hours Online</p>
                        <p class="text-2xl font-bold" id="hours-online">6.5</p>
                    </div>
                    <div class="h-12 w-12 bg-purple-100 dark:bg-purple-900/20 rounded-xl flex items-center justify-center">
                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl p-6 border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 dark:text-slate-400 text-sm">Revenue Today</p>
                        <p class="text-2xl font-bold">₱<span id="revenue-today">852</span></p>
                    </div>
                    <div class="h-12 w-12 bg-yellow-100 dark:bg-yellow-900/20 rounded-xl flex items-center justify-center">
                        <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl p-6 border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
            <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <button class="p-4 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/10 dark:hover:bg-blue-900/20 rounded-lg text-left transition">
                    <div class="text-blue-600 dark:text-blue-400 mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-medium mb-1">Trip History</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400">View past trips</p>
                </button>

                <button class="p-4 bg-green-50 hover:bg-green-100 dark:bg-green-900/10 dark:hover:bg-green-900/20 rounded-lg text-left transition">
                    <div class="text-green-600 dark:text-green-400 mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-medium mb-1">Report Issue</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Report technical problems</p>
                </button>

                <button class="p-4 bg-purple-50 hover:bg-purple-100 dark:bg-purple-900/10 dark:hover:bg-purple-900/20 rounded-lg text-left transition">
                    <div class="text-purple-600 dark:text-purple-400 mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-medium mb-1">Settings</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400">App preferences</p>
                </button>

                <button class="p-4 bg-orange-50 hover:bg-orange-100 dark:bg-orange-900/10 dark:hover:bg-orange-900/20 rounded-lg text-left transition">
                    <div class="text-orange-600 dark:text-orange-400 mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-medium mb-1">Help & Support</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Get assistance</p>
                </button>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize traffic map
        const trafficMap = L.map('traffic-map').setView([10.3157, 123.8854], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(trafficMap);

        // Add traffic markers
        const trafficData = [
            { lat: 10.3157, lng: 123.8854, status: 'heavy', description: 'Heavy traffic - Colon Street' },
            { lat: 10.3200, lng: 123.8900, status: 'moderate', description: 'Moderate traffic - Mabolo area' },
            { lat: 10.3100, lng: 123.8800, status: 'clear', description: 'Clear traffic - Lahug Boulevard' }
        ];

        trafficData.forEach(traffic => {
            const color = traffic.status === 'heavy' ? 'red' : traffic.status === 'moderate' ? 'yellow' : 'green';
            L.circleMarker([traffic.lat, traffic.lng], {
                color: color,
                fillColor: color,
                fillOpacity: 0.6,
                radius: 8
            }).addTo(trafficMap).bindPopup(traffic.description);
        });

        // Driver status management
        let isOnline = false;
        let currentPassengers = 6;

        const statusIndicator = document.getElementById('status-indicator');
        const statusText = document.getElementById('status-text');
        const passengerCountDisplay = document.getElementById('current-passenger-count');

        document.getElementById('go-online').addEventListener('click', function() {
            isOnline = true;
            statusIndicator.className = 'h-3 w-3 rounded-full status-online';
            statusText.textContent = 'Online';
            
            Swal.fire({
                title: 'Going Online',
                text: 'Your jeepney is now active and visible to passengers.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        });

        document.getElementById('go-offline').addEventListener('click', function() {
            isOnline = false;
            statusIndicator.className = 'h-3 w-3 rounded-full status-offline';
            statusText.textContent = 'Offline';
            
            Swal.fire({
                title: 'Going Offline',
                text: 'Your jeepney is no longer visible to passengers.',
                icon: 'info',
                timer: 2000,
                showConfirmButton: false
            });
        });

        document.getElementById('maintenance-mode').addEventListener('click', function() {
            isOnline = false;
            statusIndicator.className = 'h-3 w-3 rounded-full status-maintenance';
            statusText.textContent = 'Maintenance';
            
            Swal.fire({
                title: 'Maintenance Mode',
                text: 'Your jeepney is in maintenance mode.',
                icon: 'warning',
                timer: 2000,
                showConfirmButton: false
            });
        });

        // Passenger count management
        document.getElementById('add-passenger').addEventListener('click', function() {
            if (currentPassengers < 14) {
                currentPassengers++;
                updatePassengerCount();
            } else {
                Swal.fire('Jeepney Full!', 'Maximum capacity reached.', 'warning');
            }
        });

        document.getElementById('remove-passenger').addEventListener('click', function() {
            if (currentPassengers > 0) {
                currentPassengers--;
                updatePassengerCount();
            }
        });

        document.getElementById('update-count').addEventListener('click', function() {
            const manualCount = parseInt(document.getElementById('manual-count').value);
            if (manualCount >= 0 && manualCount <= 14) {
                currentPassengers = manualCount;
                updatePassengerCount();
                document.getElementById('manual-count').value = '';
                
                Swal.fire({
                    title: 'Count Updated',
                    text: `Passenger count set to ${manualCount}`,
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Invalid Count', 'Please enter a number between 0 and 14.', 'error');
            }
        });

        function updatePassengerCount() {
            passengerCountDisplay.textContent = currentPassengers;
            
            // Update available seats display (14 - current passengers)
            const availableSeats = 14 - currentPassengers;
            
            // In a real application, this would send data to server
            console.log(`Passenger count updated: ${currentPassengers}, Available seats: ${availableSeats}`);
        }

        // Simulate real-time statistics updates
        function updateStatistics() {
            // This would normally fetch from server
            const stats = {
                trips: Math.floor(Math.random() * 3) + 8,
                passengers: Math.floor(Math.random() * 20) + 140,
                hours: (Math.random() * 2 + 6).toFixed(1),
                revenue: Math.floor(Math.random() * 100) + 850
            };

            document.getElementById('todays-trips').textContent = stats.trips;
            document.getElementById('total-passengers').textContent = stats.passengers;
            document.getElementById('hours-online').textContent = stats.hours;
            document.getElementById('revenue-today').textContent = stats.revenue;
        }

        // Update statistics every 30 seconds
        setInterval(updateStatistics, 30000);
        updateStatistics(); // Initial call

        // Simulate location updates (in real app, this would use GPS)
        if ("geolocation" in navigator) {
            navigator.geolocation.watchPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                // In real app, send location to server
                console.log(`Location updated: ${lat}, ${lng}`);
            });
        }
    </script>
</body>

</html>