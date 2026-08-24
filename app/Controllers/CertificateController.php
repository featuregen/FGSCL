<?php
class CertificateController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) { Response::redirect('auth/login'); }
    }

    public function index()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId) { Response::abort(403); return; }

        Response::view('certificates/index', [
            'pageTitle' => 'Certificates',
            'breadcrumbs' => [['label' => 'Certificates']]
        ]);
    }
}