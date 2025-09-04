<?php
// API endpoint for admin operations
session_start();
require_once('../connection/connection.php');

header('Content-Type: application/json');

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
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
    case 'DELETE':
        handleDeleteRequest($conn, $user_id);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

function handleGetRequest($conn, $user_id) {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'system_stats':
            getSystemStats($conn);
            break;
        case 'drivers':
            getDrivers($conn);
            break;
        case 'all_jeepneys':
            getAllJeepneys($conn);
            break;
        case 'system_logs':
            getSystemLogs($conn, $_GET['limit'] ?? 50);
            break;
        case 'traffic_alerts':
            getTrafficAlerts($conn);
            break;
        case 'user_activity':
            getUserActivity($conn);
            break;
        case 'export_users':
            exportUsers($conn);
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
        case 'register_driver':
            registerDriver($conn, $user_id, $data);
            break;
        case 'create_traffic_alert':
            createTrafficAlert($conn, $user_id, $data);
            break;
        case 'send_message':
            sendMessageToDriver($conn, $user_id, $data);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function handlePutRequest($conn, $user_id) {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    
    switch ($action) {
        case 'update_driver':
            updateDriver($conn, $user_id, $data);
            break;
        case 'update_jeepney':
            updateJeepney($conn, $user_id, $data);
            break;
        case 'update_user_status':
            updateUserStatus($conn, $user_id, $data);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function handleDeleteRequest($conn, $user_id) {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    
    switch ($action) {
        case 'delete_driver':
            deleteDriver($conn, $user_id, $data['driver_id']);
            break;
        case 'delete_user':
            deleteUser($conn, $user_id, $data['user_id']);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function getSystemStats($conn) {
    // Get user counts by type
    $users_query = "SELECT usert_type, COUNT(*) as count FROM user_tbl GROUP BY usert_type";
    $users_result = mysqli_query($conn, $users_query);
    $user_counts = [];
    while ($row = mysqli_fetch_assoc($users_result)) {
        $user_counts[$row['usert_type']] = (int)$row['count'];
    }
    
    // Get jeepney status counts
    $jeepney_query = "SELECT status, COUNT(*) as count FROM jeepney_tbl GROUP BY status";
    $jeepney_result = mysqli_query($conn, $jeepney_query);
    $jeepney_counts = [];
    while ($row = mysqli_fetch_assoc($jeepney_result)) {
        $jeepney_counts[$row['status']] = (int)$row['count'];
    }
    
    // Get active trips
    $trips_query = "SELECT COUNT(*) as active_trips FROM trip_tbl WHERE status = 'active'";
    $trips_result = mysqli_query($conn, $trips_query);
    $active_trips = mysqli_fetch_assoc($trips_result)['active_trips'];
    
    // Get total revenue today
    $revenue_query = "SELECT SUM(revenue) as today_revenue FROM trip_tbl 
                     WHERE DATE(start_time) = CURDATE() AND status = 'completed'";
    $revenue_result = mysqli_query($conn, $revenue_query);
    $today_revenue = mysqli_fetch_assoc($revenue_result)['today_revenue'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'data' => [
            'user_counts' => $user_counts,
            'jeepney_counts' => $jeepney_counts,
            'active_trips' => (int)$active_trips,
            'today_revenue' => (float)$today_revenue
        ]
    ]);
}

function getDrivers($conn) {
    $query = "SELECT u.*, j.jeepney_id, j.route, j.status as jeepney_status 
              FROM user_tbl u 
              LEFT JOIN jeepney_tbl j ON u.id = j.driver_id 
              WHERE u.usert_type = 'driver' 
              ORDER BY u.date_created DESC";
    $result = mysqli_query($conn, $query);
    
    $drivers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $drivers[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $drivers]);
}

function getAllJeepneys($conn) {
    $query = "SELECT j.*, u.firstname, u.lastname, u.phone,
              l.latitude, l.longitude, l.speed, l.timestamp as last_location,
              pc.passenger_count, pc.available_seats
              FROM jeepney_tbl j
              LEFT JOIN user_tbl u ON j.driver_id = u.id
              LEFT JOIN (
                  SELECT jeepney_id, latitude, longitude, speed, timestamp,
                         ROW_NUMBER() OVER (PARTITION BY jeepney_id ORDER BY timestamp DESC) as rn
                  FROM location_tbl
              ) l ON j.jeepney_id = l.jeepney_id AND l.rn = 1
              LEFT JOIN (
                  SELECT jeepney_id, passenger_count, available_seats,
                         ROW_NUMBER() OVER (PARTITION BY jeepney_id ORDER BY timestamp DESC) as rn
                  FROM passenger_count_tbl
              ) pc ON j.jeepney_id = pc.jeepney_id AND pc.rn = 1
              ORDER BY j.jeepney_id";
    
    $result = mysqli_query($conn, $query);
    
    $jeepneys = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $jeepneys[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $jeepneys]);
}

function getSystemLogs($conn, $limit) {
    $query = "SELECT sl.*, u.username, u.firstname, u.lastname 
              FROM system_log_tbl sl
              LEFT JOIN user_tbl u ON sl.user_id = u.id
              ORDER BY sl.timestamp DESC
              LIMIT ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $logs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $logs[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $logs]);
    mysqli_stmt_close($stmt);
}

function registerDriver($conn, $admin_id, $data) {
    $required_fields = ['firstname', 'lastname', 'username', 'password', 'route'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Field $field is required"]);
            return;
        }
    }
    
    // Check if username exists
    $check_query = "SELECT id FROM user_tbl WHERE username = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $data['username']);
    mysqli_stmt_execute($stmt);
    
    if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
        http_response_code(409);
        echo json_encode(['error' => 'Username already exists']);
        mysqli_stmt_close($stmt);
        return;
    }
    mysqli_stmt_close($stmt);
    
    // Hash password
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    $date_created = time();
    
    // Insert driver user
    $user_query = "INSERT INTO user_tbl (firstname, lastname, username, password, date_created, usert_type, status) 
                   VALUES (?, ?, ?, ?, ?, 'driver', 'active')";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "ssssi", 
        $data['firstname'], $data['lastname'], $data['username'], $hashed_password, $date_created);
    
    if (mysqli_stmt_execute($stmt)) {
        $driver_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        
        // Create jeepney entry
        $jeepney_id = $data['jeepney_id'] ?? 'JP-' . str_pad($driver_id, 3, '0', STR_PAD_LEFT);
        $capacity = $data['capacity'] ?? 14;
        
        $jeepney_query = "INSERT INTO jeepney_tbl (jeepney_id, driver_id, route, capacity) 
                         VALUES (?, ?, ?, ?)";
        $stmt2 = mysqli_prepare($conn, $jeepney_query);
        mysqli_stmt_bind_param($stmt2, "sisi", $jeepney_id, $driver_id, $data['route'], $capacity);
        
        if (mysqli_stmt_execute($stmt2)) {
            logSystemActivity($conn, $admin_id, "Registered new driver", "Driver: {$data['username']}, Jeepney: $jeepney_id");
            
            echo json_encode([
                'success' => true, 
                'message' => 'Driver registered successfully',
                'data' => [
                    'driver_id' => $driver_id,
                    'jeepney_id' => $jeepney_id
                ]
            ]);
        } else {
            // Rollback user creation if jeepney creation fails
            mysqli_query($conn, "DELETE FROM user_tbl WHERE id = $driver_id");
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create jeepney entry']);
        }
        mysqli_stmt_close($stmt2);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to register driver']);
        mysqli_stmt_close($stmt);
    }
}

function updateDriver($conn, $admin_id, $data) {
    if (empty($data['driver_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Driver ID is required']);
        return;
    }
    
    $update_fields = [];
    $params = [];
    $types = '';
    
    if (!empty($data['firstname'])) {
        $update_fields[] = "firstname = ?";
        $params[] = $data['firstname'];
        $types .= 's';
    }
    if (!empty($data['lastname'])) {
        $update_fields[] = "lastname = ?";
        $params[] = $data['lastname'];
        $types .= 's';
    }
    if (!empty($data['phone'])) {
        $update_fields[] = "phone = ?";
        $params[] = $data['phone'];
        $types .= 's';
    }
    if (!empty($data['status'])) {
        $update_fields[] = "status = ?";
        $params[] = $data['status'];
        $types .= 's';
    }
    
    if (empty($update_fields)) {
        http_response_code(400);
        echo json_encode(['error' => 'No fields to update']);
        return;
    }
    
    $query = "UPDATE user_tbl SET " . implode(', ', $update_fields) . " WHERE id = ? AND usert_type = 'driver'";
    $params[] = $data['driver_id'];
    $types .= 'i';
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    
    if (mysqli_stmt_execute($stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        if ($affected_rows > 0) {
            logSystemActivity($conn, $admin_id, "Updated driver", "Driver ID: {$data['driver_id']}");
            echo json_encode(['success' => true, 'message' => 'Driver updated successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Driver not found']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update driver']);
    }
    
    mysqli_stmt_close($stmt);
}

function deleteDriver($conn, $admin_id, $driver_id) {
    if (empty($driver_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Driver ID is required']);
        return;
    }
    
    // Get driver info for logging
    $info_query = "SELECT username FROM user_tbl WHERE id = ? AND usert_type = 'driver'";
    $stmt = mysqli_prepare($conn, $info_query);
    mysqli_stmt_bind_param($stmt, "i", $driver_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $driver_info = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$driver_info) {
        http_response_code(404);
        echo json_encode(['error' => 'Driver not found']);
        return;
    }
    
    // Delete driver (cascade will handle related records)
    $delete_query = "DELETE FROM user_tbl WHERE id = ? AND usert_type = 'driver'";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $driver_id);
    
    if (mysqli_stmt_execute($stmt)) {
        logSystemActivity($conn, $admin_id, "Deleted driver", "Driver: {$driver_info['username']}");
        echo json_encode(['success' => true, 'message' => 'Driver deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete driver']);
    }
    
    mysqli_stmt_close($stmt);
}

function createTrafficAlert($conn, $admin_id, $data) {
    $required_fields = ['location', 'alert_type', 'description'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Field $field is required"]);
            return;
        }
    }
    
    $severity = $data['severity'] ?? 'medium';
    $end_time = !empty($data['end_time']) ? $data['end_time'] : null;
    
    $query = "INSERT INTO traffic_alert_tbl (location, alert_type, severity, description, end_time, created_by) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssi", 
        $data['location'], $data['alert_type'], $severity, $data['description'], $end_time, $admin_id);
    
    if (mysqli_stmt_execute($stmt)) {
        logSystemActivity($conn, $admin_id, "Created traffic alert", "Location: {$data['location']}");
        echo json_encode(['success' => true, 'message' => 'Traffic alert created']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create traffic alert']);
    }
    
    mysqli_stmt_close($stmt);
}

function exportUsers($conn) {
    $query = "SELECT id, firstname, lastname, username, usert_type, status, 
                     FROM_UNIXTIME(date_created) as date_created, last_login
              FROM user_tbl 
              ORDER BY date_created DESC";
    $result = mysqli_query($conn, $query);
    
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d_H-i-s') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Write CSV headers
    if (!empty($users)) {
        fputcsv($output, array_keys($users[0]));
        
        // Write data rows
        foreach ($users as $user) {
            fputcsv($output, $user);
        }
    }
    
    fclose($output);
    exit();
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