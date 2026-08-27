<?php
/**
 * Hostel Management Controller
 * Handles hostel buildings, rooms, bed capacity, and student room allotments
 */
class HostelController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('auth/login');
        }
    }

    /**
     * Main Hostel Dashboard
     */
    public function index()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId) {
            Response::abort(403);
            return;
        }

        $tab = $_GET['tab'] ?? 'hostels';

        // 1. Fetch Hostels
        $hostels = Database::fetchAll(
            "SELECT h.*, 
                    (SELECT COUNT(*) FROM hostel_rooms WHERE hostel_id = h.id) as room_count,
                    (SELECT COALESCE(SUM(number_of_beds), 0) FROM hostel_rooms WHERE hostel_id = h.id) as total_beds,
                    (SELECT COUNT(*) FROM hostel_allocations ha 
                     JOIN hostel_rooms hr ON ha.room_id = hr.id 
                     WHERE hr.hostel_id = h.id AND ha.status = 'active') as occupied_beds
             FROM hostels h
             WHERE h.school_id = ?
             ORDER BY h.name ASC",
            [$schoolId]
        );

        // 2. Fetch Rooms with Hostel details
        $rooms = Database::fetchAll(
            "SELECT hr.*, h.name as hostel_name, h.type as hostel_type,
                    (SELECT COUNT(*) FROM hostel_allocations WHERE room_id = hr.id AND status = 'active') as occupied_count
             FROM hostel_rooms hr
             JOIN hostels h ON hr.hostel_id = h.id
             WHERE hr.school_id = ?
             ORDER BY h.name ASC, hr.room_no ASC",
            [$schoolId]
        );

        // 3. Fetch Active Allocations
        $allocations = Database::fetchAll(
            "SELECT ha.*, u.full_name as student_name, u.phone as student_phone,
                    c.name as class_name, sec.name as section_name,
                    h.name as hostel_name, hr.room_no, hr.room_type, hr.cost_per_bed
             FROM hostel_allocations ha
             JOIN users u ON ha.student_id = u.id
             LEFT JOIN student_details sd ON u.id = sd.user_id
             LEFT JOIN classes c ON sd.class_id = c.id
             LEFT JOIN sections sec ON sd.section_id = sec.id
             JOIN hostels h ON ha.hostel_id = h.id
             JOIN hostel_rooms hr ON ha.room_id = hr.id
             WHERE ha.school_id = ? AND ha.status = 'active'
             ORDER BY h.name ASC, hr.room_no ASC, u.full_name ASC",
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
               AND u.id NOT IN (SELECT student_id FROM hostel_allocations WHERE school_id = ? AND status = 'active')
             ORDER BY c.name ASC, sec.name ASC, u.full_name ASC",
            [$schoolId, $schoolId]
        );

        // Calculate Stats
        $totalBeds = array_sum(array_column($hostels, 'total_beds'));
        $occupiedBeds = count($allocations);
        $stats = [
            'total_hostels' => count($hostels),
            'total_rooms'   => count($rooms),
            'total_beds'    => $totalBeds,
            'occupied_beds' => $occupiedBeds,
            'available_beds'=> max(0, $totalBeds - $occupiedBeds)
        ];

        Response::view('hostel/index', [
            'pageTitle'           => 'Hostel Management',
            'breadcrumbs'         => [['label' => 'Hostel']],
            'tab'                 => $tab,
            'hostels'             => $hostels,
            'rooms'               => $rooms,
            'allocations'         => $allocations,
            'unallocatedStudents' => $unallocatedStudents,
            'stats'               => $stats
        ]);
    }

    /**
     * Save / Update Hostel Building
     */
    public function saveHostel()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $id       = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $name     = trim($_POST['name'] ?? '');
        $type     = $_POST['type'] ?? 'boys';
        $capacity = max(1, (int)($_POST['intake_capacity'] ?? 100));
        $warden   = trim($_POST['warden_name'] ?? '');
        $phone    = trim($_POST['warden_phone'] ?? '');
        $address  = trim($_POST['address'] ?? '');
        $desc     = trim($_POST['description'] ?? '');

        if (empty($name)) {
            Session::flash('error', 'Hostel Name is required.');
            Response::redirect('hostel?tab=hostels');
            return;
        }

        $data = [
            'school_id'       => $schoolId,
            'name'            => $name,
            'type'            => $type,
            'intake_capacity' => $capacity,
            'warden_name'     => $warden,
            'warden_phone'    => $phone,
            'address'         => $address,
            'description'     => $desc,
            'status'          => 'active'
        ];

        if ($id) {
            Database::update('hostels', $data, 'id = ? AND school_id = ?', [$id, $schoolId]);
            Session::flash('success', 'Hostel details updated.');
        } else {
            Database::insert('hostels', $data);
            Session::flash('success', 'New hostel created.');
        }

        Response::redirect('hostel?tab=hostels');
    }

    /**
     * Delete Hostel
     */
    public function deleteHostel($id = null)
    {
        $schoolId = Session::schoolId();
        $id = (int)($id ?? $_POST['id'] ?? 0);

        if (!$schoolId || !$id) {
            Response::abort(403);
            return;
        }

        // Check if any rooms exist
        $roomCount = Database::fetch("SELECT COUNT(*) as cnt FROM hostel_rooms WHERE hostel_id = ?", [$id])['cnt'] ?? 0;
        if ($roomCount > 0) {
            Session::flash('error', 'Cannot delete hostel containing rooms. Delete or move the rooms first.');
            Response::redirect('hostel?tab=hostels');
            return;
        }

        Database::delete('hostels', 'id = ? AND school_id = ?', [$id, $schoolId]);
        Session::flash('success', 'Hostel deleted.');
        Response::redirect('hostel?tab=hostels');
    }

    /**
     * Save / Update Room
     */
    public function saveRoom()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $id       = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $hostelId = (int)($_POST['hostel_id'] ?? 0);
        $roomNo   = trim($_POST['room_no'] ?? '');
        $roomType = $_POST['room_type'] ?? 'non_ac';
        $beds     = max(1, (int)($_POST['number_of_beds'] ?? 4));
        $cost     = (float)($_POST['cost_per_bed'] ?? 0.0);
        $desc     = trim($_POST['description'] ?? '');

        if (!$hostelId || empty($roomNo)) {
            Session::flash('error', 'Hostel and Room Number are required.');
            Response::redirect('hostel?tab=rooms');
            return;
        }

        $data = [
            'school_id'      => $schoolId,
            'hostel_id'      => $hostelId,
            'room_no'        => $roomNo,
            'room_type'      => $roomType,
            'number_of_beds' => $beds,
            'cost_per_bed'   => $cost,
            'description'    => $desc,
            'status'         => 'available'
        ];

        if ($id) {
            Database::update('hostel_rooms', $data, 'id = ? AND school_id = ?', [$id, $schoolId]);
            Session::flash('success', 'Room updated.');
        } else {
            Database::insert('hostel_rooms', $data);
            Session::flash('success', 'New room added to hostel.');
        }

        Response::redirect('hostel?tab=rooms');
    }

    /**
     * Delete Room
     */
    public function deleteRoom($id = null)
    {
        $schoolId = Session::schoolId();
        $id = (int)($id ?? $_POST['id'] ?? 0);

        if (!$schoolId || !$id) {
            Response::abort(403);
            return;
        }

        $occupants = Database::fetch("SELECT COUNT(*) as cnt FROM hostel_allocations WHERE room_id = ? AND status = 'active'", [$id])['cnt'] ?? 0;
        if ($occupants > 0) {
            Session::flash('error', 'Cannot delete room with active student residents.');
            Response::redirect('hostel?tab=rooms');
            return;
        }

        Database::delete('hostel_rooms', 'id = ? AND school_id = ?', [$id, $schoolId]);
        Session::flash('success', 'Room deleted.');
        Response::redirect('hostel?tab=rooms');
    }

    /**
     * Allocate Student to Room & Bed
     */
    public function allocate()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $studentId   = (int)($_POST['student_id'] ?? 0);
        $hostelId    = (int)($_POST['hostel_id'] ?? 0);
        $roomId      = (int)($_POST['room_id'] ?? 0);
        $bedNumber   = trim($_POST['bed_number'] ?? 'Bed-1');
        $checkinDate = trim($_POST['checkin_date'] ?? date('Y-m-d'));

        if (!$studentId || !$hostelId || !$roomId) {
            Session::flash('error', 'Please select student, hostel, and room.');
            Response::redirect('hostel?tab=allocations');
            return;
        }

        // Check if room has bed availability
        $room = Database::fetch("SELECT * FROM hostel_rooms WHERE id = ? AND school_id = ?", [$roomId, $schoolId]);
        $occupied = Database::fetch("SELECT COUNT(*) as cnt FROM hostel_allocations WHERE room_id = ? AND status = 'active'", [$roomId])['cnt'] ?? 0;

        if ($room && $occupied >= $room['number_of_beds']) {
            Session::flash('error', 'This room is already at full bed capacity.');
            Response::redirect('hostel?tab=allocations');
            return;
        }

        // Deactivate previous active allocation if any
        Database::update('hostel_allocations', ['status' => 'vacated', 'checkout_date' => date('Y-m-d')], 'student_id = ? AND school_id = ? AND status = "active"', [$studentId, $schoolId]);

        Database::insert('hostel_allocations', [
            'school_id'    => $schoolId,
            'student_id'   => $studentId,
            'hostel_id'    => $hostelId,
            'room_id'      => $roomId,
            'bed_number'   => $bedNumber,
            'checkin_date' => $checkinDate,
            'status'       => 'active'
        ]);

        Session::flash('success', 'Student allocated to hostel room.');
        Response::redirect('hostel?tab=allocations');
    }

    /**
     * Vacate Student from Hostel
     */
    public function vacate($id = null)
    {
        $schoolId = Session::schoolId();
        $id = (int)($id ?? $_POST['id'] ?? 0);

        if (!$schoolId || !$id) {
            Response::abort(403);
            return;
        }

        Database::update('hostel_allocations', [
            'status'        => 'vacated',
            'checkout_date' => date('Y-m-d')
        ], 'id = ? AND school_id = ?', [$id, $schoolId]);

        Session::flash('success', 'Student hostel room vacated.');
        Response::redirect('hostel?tab=allocations');
    }
}