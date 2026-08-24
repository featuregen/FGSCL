<?php
class LibraryController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) { Response::redirect('auth/login'); }
    }

    public function index()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId) { Response::abort(403); return; }

        Response::view('library/index', [
            'pageTitle' => 'Library Management',
            'breadcrumbs' => [['label' => 'Library Management']]
        ]);
    }
}