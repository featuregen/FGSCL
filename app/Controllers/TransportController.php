<?php
/**
 * Transport Management Controller
 * Handles vehicles fleet, bus routes, stops with pickup/drop timings, and passenger allocation
 */
class TransportController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('auth/login');
        }
    }

    /**
     * Main Transport Dashboard
     */
    public function index()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId) {
            Response::abort(403);
            return;
        }

        $tab = $_GET['tab'] ?? 'routes';

        // 1. Fetch Vehicles
        $vehicles = Database::fetchAll(
            "SELECT v.*, 
                    (SELECT COUNT(*) FROM transport_allocations ta 
                     JOIN transport_routes tr ON ta.route_id = tr.id 
                     WHERE tr.vehicle_id = v.id AND ta.status = 'active') as passenger_count
             FROM transport_vehicles v
             WHERE v.school_id = ?
             ORDER BY v.vehicle_no ASC",
            [$schoolId]
        );

        // 2. Fetch Routes with assigned Vehicle & Stops
        $routes = Database::fetchAll(
            "SELECT r.*, v.vehicle_no, v.driver_name, v.phone as driver_phone, v.capacity,
                    (SELECT COUNT(*) FROM transport_stops WHERE route_id = r.id) as stop_count,
                    (SELECT COUNT(*) FROM transport_allocations WHERE route_id = r.id AND status = 'active') as student_count
             FROM transport_routes r
             LEFT JOIN transport_vehicles v ON r.vehicle_id = v.id
             WHERE r.school_id = ?
             ORDER BY r.name ASC",
            [$schoolId]
        );

        // For each route, fetch its stops
        foreach ($routes as &$rt) {
            $rt['stops'] = Database::fetchAll(
                "SELECT * FROM transport_stops WHERE route_id = ? ORDER BY order_no ASC, pickup_time ASC",
                [$rt['id']]
            );
        }
        unset($rt);

        // 3. Fetch Allocations (Students / Staff assigned to routes)
        $allocations = Database::fetchAll(
            "SELECT ta.*, u.full_name as student_name, u.phone as student_phone,
                    c.name as class_name, sec.name as section_name,
                    tr.route_title, tr.fare as route_fare,
                    ts.stop_name, ts.pickup_time, ts.drop_time,
                    tv.vehicle_no, tv.driver_name, tv.driver_phone
             FROM transport_allocations ta
             JOIN users u ON ta.student_id = u.id
             LEFT JOIN student_details sd ON u.id = sd.user_id
             LEFT JOIN classes c ON sd.class_id = c.id
             LEFT JOIN sections sec ON sd.section_id = sec.id
             JOIN transport_routes tr ON ta.route_id = tr.id
             LEFT JOIN transport_stops ts ON ta.stop_id = ts.id
             LEFT JOIN transport_vehicles tv ON tr.vehicle_id = tv.id
             WHERE ta.school_id = ? AND ta.status = 'active'
             ORDER BY tr.route_title ASC, u.full_name ASC",
            [$schoolId]
        );

        // Unallocated students for allocation dropdown
        $unallocatedStudents = Database::fetchAll(
            "SELECT u.id, u.full_name, c.name as class_name, sec.name as section_name
             FROM users u
             JOIN student_details sd ON u.id = sd.user_id
             LEFT JOIN classes c ON sd.class_id = c.id
             LEFT JOIN sections sec ON sd.section_id = sec.id
             WHERE u.school_id = ? AND u.is_active = 1
               AND u.id NOT IN (SELECT student_id FROM transport_allocations WHERE school_id = ? AND status = 'active')
             ORDER BY c.name ASC, sec.name ASC, u.full_name ASC",
            [$schoolId, $schoolId]
        );

        // Stats
        $stats = [
            'total_vehicles' => count($vehicles),
            'total_routes'   => count($routes),
            'allocated'      => count($allocations),
            'total_capacity' => array_sum(array_column($vehicles, 'capacity'))
        ];

        Response::view('transport/index', [
            'pageTitle'           => 'Transport Management',
            'breadcrumbs'         => [['label' => 'Transport']],
            'tab'                 => $tab,
            'vehicles'            => $vehicles,
            'routes'              => $routes,
            'allocations'         => $allocations,
            'unallocatedStudents' => $unallocatedStudents,
            'stats'               => $stats
        ]);
    }

    /**
     * Save / Update Vehicle
     */
    public function saveVehicle()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $id           = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $vehicleNo    = trim($_POST['vehicle_no'] ?? '');
        $model        = trim($_POST['model'] ?? '');
        $capacity     = max(1, (int)($_POST['capacity'] ?? 30));
        $driverName   = trim($_POST['driver_name'] ?? '');
        $driverPhone  = trim($_POST['driver_phone'] ?? '');
        $license      = trim($_POST['driver_license'] ?? '');
        $insuranceExp = !empty($_POST['insurance_expiry']) ? $_POST['insurance_expiry'] : null;
        $status       = $_POST['status'] ?? 'active';

        if (empty($vehicleNo)) {
            Session::flash('error', 'Vehicle Number is required.');
            Response::redirect('transport?tab=vehicles');
            return;
        }

        $data = [
            'school_id'        => $schoolId,
            'vehicle_no'       => $vehicleNo,
            'model'            => $model,
            'capacity'         => $capacity,
            'driver_name'      => $driverName,
            'driver_phone'     => $driverPhone,
            'driver_license'   => $license,
            'insurance_expiry' => $insuranceExp,
            'status'           => $status
        ];

        if ($id) {
            Database::update('transport_vehicles', $data, 'id = ? AND school_id = ?', [$id, $schoolId]);
            Session::flash('success', 'Vehicle updated successfully.');
        } else {
            Database::insert('transport_vehicles', $data);
            Session::flash('success', 'New vehicle added to fleet.');
        }

        Response::redirect('transport?tab=vehicles');
    }

    /**
     * Delete Vehicle
     */
    public function deleteVehicle($id = null)
    {
        $schoolId = Session::schoolId();
        $id = (int)($id ?? $_POST['id'] ?? 0);

        if (!$schoolId || !$id) {
            Response::abort(403);
            return;
        }

        // Check if assigned to any route
        $assigned = Database::fetch("SELECT COUNT(*) as cnt FROM transport_routes WHERE vehicle_id = ?", [$id])['cnt'] ?? 0;
        if ($assigned > 0) {
            Session::flash('error', 'Cannot delete vehicle assigned to active transport routes.');
            Response::redirect('transport?tab=vehicles');
            return;
        }

        Database::delete('transport_vehicles', 'id = ? AND school_id = ?', [$id, $schoolId]);
        Session::flash('success', 'Vehicle removed.');
        Response::redirect('transport?tab=vehicles');
    }

    /**
     * Save / Update Route
     */
    public function saveRoute()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $id         = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $title      = trim($_POST['route_title'] ?? '');
        $vehicleId  = !empty($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : null;
        $startPoint = trim($_POST['start_point'] ?? '');
        $endPoint   = trim($_POST['end_point'] ?? '');
        $fare       = (float)($_POST['fare'] ?? 0.0);
        $desc       = trim($_POST['description'] ?? '');

        if (empty($title)) {
            Session::flash('error', 'Route title is required.');
            Response::redirect('transport?tab=routes');
            return;
        }

        $data = [
            'school_id'   => $schoolId,
            'route_title' => $title,
            'vehicle_id'  => $vehicleId,
            'start_point' => $startPoint,
            'end_point'   => $endPoint,
            'fare'        => $fare,
            'description' => $desc,
            'status'      => 'active'
        ];

        if ($id) {
            Database::update('transport_routes', $data, 'id = ? AND school_id = ?', [$id, $schoolId]);
            Session::flash('success', 'Route updated successfully.');
        } else {
            Database::insert('transport_routes', $data);
            Session::flash('success', 'New route created.');
        }

        Response::redirect('transport?tab=routes');
    }

    /**
     * Delete Route
     */
    public function deleteRoute($id = null)
    {
        $schoolId = Session::schoolId();
        $id = (int)($id ?? $_POST['id'] ?? 0);

        if (!$schoolId || !$id) {
            Response::abort(403);
            return;
        }

        Database::delete('transport_stops', 'route_id = ?', [$id]);
        Database::delete('transport_allocations', 'route_id = ?', [$id]);
        Database::delete('transport_routes', 'id = ? AND school_id = ?', [$id, $schoolId]);

        Session::flash('success', 'Route and associated stops deleted.');
        Response::redirect('transport?tab=routes');
    }

    /**
     * Save / Add Stop to Route
     */
    public function saveStop()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $routeId    = (int)($_POST['route_id'] ?? 0);
        $stopName   = trim($_POST['stop_name'] ?? '');
        $pickupTime = !empty($_POST['pickup_time']) ? $_POST['pickup_time'] : null;
        $dropTime   = !empty($_POST['drop_time']) ? $_POST['drop_time'] : null;
        $fare       = (float)($_POST['fare'] ?? 0.0);
        $orderNo    = (int)($_POST['order_no'] ?? 1);

        if (!$routeId || empty($stopName)) {
            Session::flash('error', 'Route and Stop name are required.');
            Response::redirect('transport?tab=routes');
            return;
        }

        Database::insert('transport_stops', [
            'school_id'   => $schoolId,
            'route_id'    => $routeId,
            'stop_name'   => $stopName,
            'pickup_time' => $pickupTime,
            'drop_time'   => $dropTime,
            'fare'        => $fare,
            'order_no'    => $orderNo
        ]);

        Session::flash('success', 'Stop added to route.');
        Response::redirect('transport?tab=routes');
    }

    /**
     * Delete Stop
     */
    public function deleteStop($id = null)
    {
        $schoolId = Session::schoolId();
        $id = (int)($id ?? $_POST['id'] ?? 0);

        if (!$schoolId || !$id) {
            Response::abort(403);
            return;
        }

        Database::delete('transport_stops', 'id = ? AND school_id = ?', [$id, $schoolId]);
        Session::flash('success', 'Stop removed from route.');
        Response::redirect('transport?tab=routes');
    }

    /**
     * Allocate Student to Route & Stop
     */
    public function allocate()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $studentId = (int)($_POST['student_id'] ?? 0);
        $routeId   = (int)($_POST['route_id'] ?? 0);
        $stopId    = !empty($_POST['stop_id']) ? (int)$_POST['stop_id'] : null;

        if (!$studentId || !$routeId) {
            Session::flash('error', 'Please select student and transport route.');
            Response::redirect('transport?tab=allocations');
            return;
        }

        // Deactivate previous active allocation if any
        Database::update('transport_allocations', ['status' => 'inactive'], 'student_id = ? AND school_id = ?', [$studentId, $schoolId]);

        Database::insert('transport_allocations', [
            'school_id'  => $schoolId,
            'student_id' => $studentId,
            'route_id'   => $routeId,
            'stop_id'    => $stopId,
            'status'     => 'active'
        ]);

        Session::flash('success', 'Student assigned to bus route successfully.');
        Response::redirect('transport?tab=allocations');
    }

    /**
     * Remove / Cancel Student Allocation
     */
    public function removeAllocation($id = null)
    {
        $schoolId = Session::schoolId();
        $id = (int)($id ?? $_POST['id'] ?? 0);

        if (!$schoolId || !$id) {
            Response::abort(403);
            return;
        }

        Database::delete('transport_allocations', 'id = ? AND school_id = ?', [$id, $schoolId]);
        Session::flash('success', 'Transport allocation cancelled.');
        Response::redirect('transport?tab=allocations');
    }
}