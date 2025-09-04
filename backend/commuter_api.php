<?php
// API endpoint for commuter operations
session_start();
require_once('../connection/connection.php');

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$conn = con();
$user_id = $_SESSION['user_id'];

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        handleGetRequest($conn, $user_id);
        break;
    case 'POST':
        handlePostRequest($conn, $user_id);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

function handleGetRequest($conn, $user_id) {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'active_jeepneys':
            getActiveJeepneys($conn);
            break;
        case 'jeepney_details':
            getJeepneyDetails($conn, $_GET['jeepney_id'] ?? '');
            break;
        case 'seat_availability':
            getSeatAvailability($conn, $_GET['jeepney_id'] ?? '');
            break;
        case 'routes':
            getRoutes($conn);
            break;
        case 'traffic_alerts':
            getTrafficAlerts($conn);
            break;
        case 'dashboard_stats':
            getDashboardStats($conn);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function handlePostRequest($conn, $user_id) {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    
    switch ($action) {
        case 'reserve_seat':
            reserveSeat($conn, $user_id, $data['jeepney_id'], $data['seat_number']);
            break;
        case 'cancel_reservation':
            cancelReservation($conn, $user_id, $data['reservation_id']);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function getActiveJeepneys($conn) {
    $query = "SELECT * FROM active_jeepneys_view ORDER BY jeepney_id";
    $result = mysqli_query($conn, $query);
    
    $jeepneys = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $jeepneys[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $jeepneys]);
}

function getJeepneyDetails($conn, $jeepney_id) {
    if (empty($jeepney_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Jeepney ID required']);
        return;
    }
    
    $query = "SELECT * FROM active_jeepneys_view WHERE jeepney_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $jeepney_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($jeepney = mysqli_fetch_assoc($result)) {
        echo json_encode(['success' => true, 'data' => $jeepney]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Jeepney not found or offline']);
    }
    
    mysqli_stmt_close($stmt);
}

function getSeatAvailability($conn, $jeepney_id) {
    if (empty($jeepney_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Jeepney ID required']);
        return;
    }
    
    // Get current passenger count
    $query = "SELECT passenger_count, available_seats FROM passenger_count_tbl 
              WHERE jeepney_id = ? 
              ORDER BY timestamp DESC LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $jeepney_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $seat_data = mysqli_fetch_assoc($result);
    if (!$seat_data) {
        // Default values if no data found
        $seat_data = ['passenger_count' => 0, 'available_seats' => 14];
    }
    
    // Get reserved seats
    $reserved_query = "SELECT seat_number FROM seat_reservation_tbl 
                      WHERE jeepney_id = ? AND status = 'active' AND expiry_time > NOW()";
    $stmt2 = mysqli_prepare($conn, $reserved_query);
    mysqli_stmt_bind_param($stmt2, "s", $jeepney_id);
    mysqli_stmt_execute($stmt2);
    $reserved_result = mysqli_stmt_get_result($stmt2);
    
    $reserved_seats = [];
    while ($row = mysqli_fetch_assoc($reserved_result)) {
        $reserved_seats[] = (int)$row['seat_number'];
    }
    
    mysqli_stmt_close($stmt);
    mysqli_stmt_close($stmt2);
    
    echo json_encode([
        'success' => true, 
        'data' => [
            'passenger_count' => (int)$seat_data['passenger_count'],
            'available_seats' => (int)$seat_data['available_seats'],
            'reserved_seats' => $reserved_seats
        ]
    ]);
}

function getRoutes($conn) {
    $query = "SELECT * FROM route_tbl WHERE is_active = TRUE ORDER BY route_name";
    $result = mysqli_query($conn, $query);
    
    $routes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $routes[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $routes]);
}

function getTrafficAlerts($conn) {
    $query = "SELECT * FROM traffic_alert_tbl 
              WHERE is_active = TRUE AND (end_time IS NULL OR end_time > NOW())
              ORDER BY severity DESC, start_time DESC";
    $result = mysqli_query($conn, $query);
    
    $alerts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $alerts[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $alerts]);
}

function getDashboardStats($conn) {
    // Get active jeepneys count
    $active_query = "SELECT COUNT(*) as count FROM jeepney_tbl WHERE status = 'online'";
    $active_result = mysqli_query($conn, $active_query);
    $active_count = mysqli_fetch_assoc($active_result)['count'];
    
    // Get total available seats
    $seats_query = "SELECT SUM(pc.available_seats) as total_seats 
                   FROM passenger_count_tbl pc
                   INNER JOIN (
                       SELECT jeepney_id, MAX(timestamp) as max_time
                       FROM passenger_count_tbl 
                       GROUP BY jeepney_id
                   ) latest ON pc.jeepney_id = latest.jeepney_id AND pc.timestamp = latest.max_time
                   INNER JOIN jeepney_tbl j ON pc.jeepney_id = j.jeepney_id
                   WHERE j.status = 'online'";
    $seats_result = mysqli_query($conn, $seats_query);
    $total_seats = mysqli_fetch_assoc($seats_result)['total_seats'] ?? 0;
    
    // Get average speed
    $speed_query = "SELECT AVG(l.speed) as avg_speed 
                   FROM location_tbl l
                   INNER JOIN (
                       SELECT jeepney_id, MAX(timestamp) as max_time
                       FROM location_tbl 
                       WHERE timestamp >= NOW() - INTERVAL 5 MINUTE
                       GROUP BY jeepney_id
                   ) latest ON l.jeepney_id = latest.jeepney_id AND l.timestamp = latest.max_time
                   INNER JOIN jeepney_tbl j ON l.jeepney_id = j.jeepney_id
                   WHERE j.status = 'online'";
    $speed_result = mysqli_query($conn, $speed_query);
    $avg_speed = round(mysqli_fetch_assoc($speed_result)['avg_speed'] ?? 0, 1);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'active_jeepneys' => (int)$active_count,
            'available_seats' => (int)$total_seats,
            'average_speed' => $avg_speed
        ]
    ]);
}

function reserveSeat($conn, $user_id, $jeepney_id, $seat_number) {
    if (empty($jeepney_id) || empty($seat_number)) {
        http_response_code(400);
        echo json_encode(['error' => 'Jeepney ID and seat number required']);
        return;
    }
    
    // Check if seat is already reserved
    $check_query = "SELECT id FROM seat_reservation_tbl 
                   WHERE jeepney_id = ? AND seat_number = ? AND status = 'active' AND expiry_time > NOW()";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "si", $jeepney_id, $seat_number);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        http_response_code(409);
        echo json_encode(['error' => 'Seat already reserved']);
        mysqli_stmt_close($stmt);
        return;
    }
    mysqli_stmt_close($stmt);
    
    // Create reservation (expires in 15 minutes)
    $expiry_time = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    $insert_query = "INSERT INTO seat_reservation_tbl (jeepney_id, user_id, seat_number, expiry_time) 
                     VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "siis", $jeepney_id, $user_id, $seat_number, $expiry_time);
    
    if (mysqli_stmt_execute($stmt)) {
        $reservation_id = mysqli_insert_id($conn);
        echo json_encode([
            'success' => true, 
            'message' => 'Seat reserved successfully',
            'data' => [
                'reservation_id' => $reservation_id,
                'expiry_time' => $expiry_time
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to reserve seat']);
    }
    
    mysqli_stmt_close($stmt);
}

function cancelReservation($conn, $user_id, $reservation_id) {
    $query = "UPDATE seat_reservation_tbl 
              SET status = 'cancelled' 
              WHERE id = ? AND user_id = ? AND status = 'active'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $reservation_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        if ($affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Reservation cancelled']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Reservation not found or already cancelled']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to cancel reservation']);
    }
    
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>