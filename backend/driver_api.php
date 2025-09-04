<?php
// API endpoint for driver operations
session_start();
require_once('../connection/connection.php');

header('Content-Type: application/json');

// Check if user is logged in and is a driver
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'driver') {
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
    case 'PUT':
        handlePutRequest($conn, $user_id);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

function handleGetRequest($conn, $user_id) {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'status':
            getDriverStatus($conn, $user_id);
            break;
        case 'stats':
            getDriverStats($conn, $user_id);
            break;
        case 'jeepney_info':
            getJeepneyInfo($conn, $user_id);
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
        case 'update_status':
            updateDriverStatus($conn, $user_id, $data['status']);
            break;
        case 'update_passenger_count':
            updatePassengerCount($conn, $user_id, $data['count']);
            break;
        case 'update_location':
            updateLocation($conn, $user_id, $data['latitude'], $data['longitude'], $data['speed'] ?? 0);
            break;
        case 'start_trip':
            startTrip($conn, $user_id, $data);
            break;
        case 'end_trip':
            endTrip($conn, $user_id, $data['trip_id']);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function getDriverStatus($conn, $user_id) {
    $query = "SELECT j.*, u.firstname, u.lastname FROM jeepney_tbl j 
              JOIN user_tbl u ON j.driver_id = u.id 
              WHERE j.driver_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($jeepney = mysqli_fetch_assoc($result)) {
        echo json_encode(['success' => true, 'data' => $jeepney]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Jeepney not found']);
    }
    
    mysqli_stmt_close($stmt);
}

function getDriverStats($conn, $user_id) {
    // Get today's stats
    $today = date('Y-m-d');
    
    // Get trips today
    $trips_query = "SELECT COUNT(*) as trips_today, SUM(total_passengers) as passengers_today, 
                   SUM(revenue) as revenue_today
                   FROM trip_tbl 
                   WHERE driver_id = ? AND DATE(start_time) = ? AND status = 'completed'";
    $stmt = mysqli_prepare($conn, $trips_query);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $today);
    mysqli_stmt_execute($stmt);
    $stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    // Get hours online today
    $hours_query = "SELECT 
                    COALESCE(SUM(TIMESTAMPDIFF(MINUTE, start_time, 
                        COALESCE(end_time, NOW()))), 0) / 60 as hours_online
                    FROM trip_tbl 
                    WHERE driver_id = ? AND DATE(start_time) = ?";
    $stmt = mysqli_prepare($conn, $hours_query);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $today);
    mysqli_stmt_execute($stmt);
    $hours_result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    $stats['hours_online'] = round($hours_result['hours_online'] ?? 0, 1);
    $stats['trips_today'] = $stats['trips_today'] ?? 0;
    $stats['passengers_today'] = $stats['passengers_today'] ?? 0;
    $stats['revenue_today'] = $stats['revenue_today'] ?? 0;
    
    echo json_encode(['success' => true, 'data' => $stats]);
}

function updateDriverStatus($conn, $user_id, $status) {
    $allowed_statuses = ['online', 'offline', 'maintenance'];
    if (!in_array($status, $allowed_statuses)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid status']);
        return;
    }
    
    $query = "UPDATE jeepney_tbl SET status = ?, updated_at = NOW() WHERE driver_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $status, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Log the status change
        logSystemActivity($conn, $user_id, "Status changed to $status");
        echo json_encode(['success' => true, 'message' => "Status updated to $status"]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update status']);
    }
    
    mysqli_stmt_close($stmt);
}

function updatePassengerCount($conn, $user_id, $count) {
    if ($count < 0 || $count > 20) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid passenger count']);
        return;
    }
    
    // Get jeepney info
    $jeepney_query = "SELECT jeepney_id, capacity FROM jeepney_tbl WHERE driver_id = ?";
    $stmt = mysqli_prepare($conn, $jeepney_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $jeepney = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    if (!$jeepney) {
        http_response_code(404);
        echo json_encode(['error' => 'Jeepney not found']);
        return;
    }
    
    $available_seats = $jeepney['capacity'] - $count;
    
    // Insert passenger count record
    $insert_query = "INSERT INTO passenger_count_tbl (jeepney_id, passenger_count, available_seats) 
                     VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "sii", $jeepney['jeepney_id'], $count, $available_seats);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Passenger count updated',
            'data' => [
                'passenger_count' => $count,
                'available_seats' => $available_seats
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update passenger count']);
    }
    
    mysqli_stmt_close($stmt);
}

function updateLocation($conn, $user_id, $latitude, $longitude, $speed) {
    // Get jeepney ID
    $jeepney_query = "SELECT jeepney_id FROM jeepney_tbl WHERE driver_id = ?";
    $stmt = mysqli_prepare($conn, $jeepney_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $jeepney = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    if (!$jeepney) {
        http_response_code(404);
        echo json_encode(['error' => 'Jeepney not found']);
        return;
    }
    
    // Insert location record
    $insert_query = "INSERT INTO location_tbl (jeepney_id, latitude, longitude, speed) 
                     VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "sddd", $jeepney['jeepney_id'], $latitude, $longitude, $speed);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Location updated']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update location']);
    }
    
    mysqli_stmt_close($stmt);
}

function logSystemActivity($conn, $user_id, $action, $details = '') {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $query = "INSERT INTO system_log_tbl (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $action, $details, $ip_address);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>