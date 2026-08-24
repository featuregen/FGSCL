<?php
class VisitorController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) { Response::redirect('auth/login'); }
    }

    public function index()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId) { Response::abort(403); return; }

        Response::view('visitors/index', [
            'pageTitle' => 'Visitor Logs',
            'breadcrumbs' => [['label' => 'Visitor Logs']]
        ]);
    }
}