<?php
class InventoryController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) { Response::redirect('auth/login'); }
    }

    public function index()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId) { Response::abort(403); return; }

        Response::view('inventory/index', [
            'pageTitle' => 'Inventory Management',
            'breadcrumbs' => [['label' => 'Inventory Management']]
        ]);
    }
}