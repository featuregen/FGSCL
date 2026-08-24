<?php
class ReportController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) { Response::redirect('auth/login'); }
    }

    public function index()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId) { Response::abort(403); return; }

        Response::view('reports/index', [
            'pageTitle' => 'Reports & Analytics',
            'breadcrumbs' => [['label' => 'Reports & Analytics']]
        ]);
    }
}