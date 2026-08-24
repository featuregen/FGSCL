<?php
class MasterController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) { Response::redirect('auth/login'); }
    }

    public function index()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId) { Response::abort(403); return; }

        Response::view('masters/index', [
            'pageTitle' => 'Master Data',
            'breadcrumbs' => [['label' => 'Master Data']]
        ]);
    }
}