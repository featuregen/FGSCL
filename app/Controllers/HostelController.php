<?php
class HostelController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) { Response::redirect('auth/login'); }
    }

    public function index()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId) { Response::abort(403); return; }

        Response::view('hostel/index', [
            'pageTitle' => 'Hostel Management',
            'breadcrumbs' => [['label' => 'Hostel Management']]
        ]);
    }
}