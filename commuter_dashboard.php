<?php
session_start();
require_once('connection/connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <title>Jeepney Tracking — Commuter Dashboard</title>
    <link rel="stylesheet" href="src/output.css" />
    <!-- Leaflet CSS for interactive maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        #map { height: 400px; width: 100%; }
        .speed-display { font-family: 'Courier New', monospace; }
        .seat-available { background-color: #10b981; }
        .seat-occupied { background-color: #ef4444; }
        .seat-unavailable { background-color: #6b7280; }
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
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-lg text-sm font-medium dark:bg-blue-900 dark:text-blue-200">Commuter</span>
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
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Commuter Dashboard</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-2">Track jeepney locations, check seat availability, and monitor real-time information.</p>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl p-6 border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 dark:text-slate-400 text-sm">Active Jeepneys</p>
                        <p class="text-2xl font-bold" id="active-jeepneys">12</p>
                    </div>
                    <div class="h-12 w-12 bg-green-100 dark:bg-green-900/20 rounded-xl flex items-center justify-center">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl p-6 border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 dark:text-slate-400 text-sm">Available Seats</p>
                        <p class="text-2xl font-bold" id="available-seats">24</p>
                    </div>
                    <div class="h-12 w-12 bg-blue-100 dark:bg-blue-900/20 rounded-xl flex items-center justify-center">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl p-6 border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 dark:text-slate-400 text-sm">Average Speed</p>
                        <p class="text-2xl font-bold speed-display"><span id="avg-speed">25</span> km/h</p>
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Interactive Map -->
            <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200/60 dark:border-slate-700/60">
                    <h2 class="text-xl font-semibold">Live Jeepney Locations</h2>
                    <p class="text-slate-600 dark:text-slate-400 text-sm mt-1">Real-time tracking of active jeepneys</p>
                </div>
                <div class="p-6">
                    <div id="map" class="rounded-lg border border-slate-200 dark:border-slate-600"></div>
                </div>
            </div>

            <!-- Jeepney List & Seat Availability -->
            <div class="space-y-6">
                <!-- Current Speed Display -->
                <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl p-6 border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
                    <h3 class="text-lg font-semibold mb-4">Speed Monitor</h3>
                    <div class="text-center">
                        <div class="text-4xl font-bold speed-display mb-2">
                            <span id="current-speed">32</span> <span class="text-2xl">km/h</span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400">Selected Jeepney Speed</p>
                    </div>
                </div>

                <!-- Seat Availability -->
                <div class="bg-white/70 dark:bg-slate-800/70 rounded-xl p-6 border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
                    <h3 class="text-lg font-semibold mb-4">Seat Availability</h3>
                    
                    <!-- Jeepney Selection -->
                    <div class="mb-4">
                        <label for="jeepney-select" class="block text-sm font-medium mb-2">Select Jeepney:</label>
                        <select id="jeepney-select" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700">
                            <option value="jp001">JP-001 (Route: Divisoria-Mabolo)</option>
                            <option value="jp002">JP-002 (Route: Lahug-Colon)</option>
                            <option value="jp003">JP-003 (Route: Talamban-IT Park)</option>
                        </select>
                    </div>

                    <!-- Seat Map -->
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-4">
                        <p class="text-sm text-slate-600 dark:text-slate-400 mb-3">Seat Layout (JP-001):</p>
                        <div class="grid grid-cols-2 gap-2 max-w-48 mx-auto" id="seat-layout">
                            <!-- Left side seats -->
                            <div class="space-y-2">
                                <div class="seat-available h-8 w-8 rounded border-2 border-white flex items-center justify-center text-xs font-bold text-white cursor-pointer" data-seat="1">1</div>
                                <div class="seat-occupied h-8 w-8 rounded border-2 border-white flex items-center justify-center text-xs font-bold text-white" data-seat="3">3</div>
                                <div class="seat-available h-8 w-8 rounded border-2 border-white flex items-center justify-center text-xs font-bold text-white cursor-pointer" data-seat="5">5</div>
                                <div class="seat-available h-8 w-8 rounded border-2 border-white flex items-center justify-center text-xs font-bold text-white cursor-pointer" data-seat="7">7</div>
                            </div>
                            <!-- Right side seats -->
                            <div class="space-y-2">
                                <div class="seat-available h-8 w-8 rounded border-2 border-white flex items-center justify-center text-xs font-bold text-white cursor-pointer" data-seat="2">2</div>
                                <div class="seat-available h-8 w-8 rounded border-2 border-white flex items-center justify-center text-xs font-bold text-white cursor-pointer" data-seat="4">4</div>
                                <div class="seat-occupied h-8 w-8 rounded border-2 border-white flex items-center justify-center text-xs font-bold text-white" data-seat="6">6</div>
                                <div class="seat-available h-8 w-8 rounded border-2 border-white flex items-center justify-center text-xs font-bold text-white cursor-pointer" data-seat="8">8</div>
                            </div>
                        </div>
                        
                        <!-- Legend -->
                        <div class="flex justify-center gap-4 mt-4 text-xs">
                            <div class="flex items-center gap-1">
                                <div class="seat-available h-3 w-3 rounded"></div>
                                <span>Available</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <div class="seat-occupied h-3 w-3 rounded"></div>
                                <span>Occupied</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Jeepneys List -->
        <div class="mt-8 bg-white/70 dark:bg-slate-800/70 rounded-xl border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm">
            <div class="p-6 border-b border-slate-200/60 dark:border-slate-700/60">
                <h2 class="text-xl font-semibold">Active Jeepneys</h2>
                <p class="text-slate-600 dark:text-slate-400 text-sm mt-1">Live status of all operating jeepneys</p>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left border-b border-slate-200/60 dark:border-slate-700/60">
                            <tr>
                                <th class="pb-3 font-semibold">Jeepney ID</th>
                                <th class="pb-3 font-semibold">Route</th>
                                <th class="pb-3 font-semibold">Current Speed</th>
                                <th class="pb-3 font-semibold">Available Seats</th>
                                <th class="pb-3 font-semibold">Status</th>
                                <th class="pb-3 font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody id="jeepneys-table" class="divide-y divide-slate-200/60 dark:divide-slate-700/60">
                            <tr>
                                <td class="py-3 font-medium">JP-001</td>
                                <td class="py-3">Divisoria → Mabolo</td>
                                <td class="py-3 speed-display">28 km/h</td>
                                <td class="py-3"><span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs dark:bg-green-900/20 dark:text-green-400">6 seats</span></td>
                                <td class="py-3"><span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs dark:bg-blue-900/20 dark:text-blue-400">Active</span></td>
                                <td class="py-3">
                                    <button class="text-blue-600 hover:text-blue-800 text-xs font-medium track-jeepney" data-jeepney="jp001">Track</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3 font-medium">JP-002</td>
                                <td class="py-3">Lahug → Colon</td>
                                <td class="py-3 speed-display">22 km/h</td>
                                <td class="py-3"><span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs dark:bg-green-900/20 dark:text-green-400">4 seats</span></td>
                                <td class="py-3"><span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs dark:bg-blue-900/20 dark:text-blue-400">Active</span></td>
                                <td class="py-3">
                                    <button class="text-blue-600 hover:text-blue-800 text-xs font-medium track-jeepney" data-jeepney="jp002">Track</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3 font-medium">JP-003</td>
                                <td class="py-3">Talamban → IT Park</td>
                                <td class="py-3 speed-display">35 km/h</td>
                                <td class="py-3"><span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs dark:bg-red-900/20 dark:text-red-400">0 seats</span></td>
                                <td class="py-3"><span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs dark:bg-blue-900/20 dark:text-blue-400">Active</span></td>
                                <td class="py-3">
                                    <button class="text-blue-600 hover:text-blue-800 text-xs font-medium track-jeepney" data-jeepney="jp003">Track</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize map
        const map = L.map('map').setView([10.3157, 123.8854], 13); // Cebu City coordinates

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Sample jeepney locations
        const jeepneyData = {
            'jp001': { lat: 10.3157, lng: 123.8854, speed: 28, seats: 6, route: 'Divisoria → Mabolo' },
            'jp002': { lat: 10.3200, lng: 123.8900, speed: 22, seats: 4, route: 'Lahug → Colon' },
            'jp003': { lat: 10.3100, lng: 123.8800, speed: 35, seats: 0, route: 'Talamban → IT Park' }
        };

        const markers = {};

        // Add jeepney markers to map
        Object.keys(jeepneyData).forEach(jeepneyId => {
            const data = jeepneyData[jeepneyId];
            const marker = L.marker([data.lat, data.lng]).addTo(map);
            marker.bindPopup(`
                <strong>${jeepneyId.toUpperCase()}</strong><br>
                Route: ${data.route}<br>
                Speed: ${data.speed} km/h<br>
                Available Seats: ${data.seats}
            `);
            markers[jeepneyId] = marker;
        });

        // Handle jeepney tracking
        document.querySelectorAll('.track-jeepney').forEach(button => {
            button.addEventListener('click', function() {
                const jeepneyId = this.dataset.jeepney;
                const data = jeepneyData[jeepneyId];
                
                if (data) {
                    map.setView([data.lat, data.lng], 16);
                    markers[jeepneyId].openPopup();
                    
                    // Update current speed display
                    document.getElementById('current-speed').textContent = data.speed;
                }
            });
        });

        // Handle jeepney selection for seat availability
        document.getElementById('jeepney-select').addEventListener('change', function() {
            const selectedId = this.value;
            // In a real implementation, this would fetch seat data from the server
            updateSeatLayout(selectedId);
        });

        function updateSeatLayout(jeepneyId) {
            // Simulate different seat layouts for different jeepneys
            const seatLayouts = {
                'jp001': [true, true, false, true, true, false, true, true],
                'jp002': [true, false, true, true, false, true, false, true],
                'jp003': [false, false, false, false, false, false, false, false]
            };
            
            const layout = seatLayouts[jeepneyId] || seatLayouts['jp001'];
            const seatElements = document.querySelectorAll('[data-seat]');
            
            seatElements.forEach((seat, index) => {
                seat.className = seat.className.replace(/seat-(available|occupied)/, 
                    layout[index] ? 'seat-available' : 'seat-occupied');
            });
        }

        // Simulate real-time updates
        function updateRealTimeData() {
            // Update active jeepneys count
            const activeCount = Object.keys(jeepneyData).length;
            document.getElementById('active-jeepneys').textContent = activeCount;
            
            // Update total available seats
            const totalSeats = Object.values(jeepneyData).reduce((sum, data) => sum + data.seats, 0);
            document.getElementById('available-seats').textContent = totalSeats;
            
            // Update average speed
            const avgSpeed = Math.round(Object.values(jeepneyData).reduce((sum, data) => sum + data.speed, 0) / activeCount);
            document.getElementById('avg-speed').textContent = avgSpeed;
        }

        // Update data every 5 seconds
        setInterval(updateRealTimeData, 5000);
        updateRealTimeData(); // Initial call

        // Handle seat selection (optional feature)
        document.querySelectorAll('[data-seat]').forEach(seat => {
            seat.addEventListener('click', function() {
                if (this.classList.contains('seat-available')) {
                    Swal.fire({
                        title: 'Reserve Seat?',
                        text: `Do you want to reserve seat ${this.dataset.seat}?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, reserve it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire('Reserved!', 'Seat has been reserved for you.', 'success');
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>