<?php

namespace App\Controllers;

use App\Controller;

/**
 * HomeController — Handles public landing page and marketing routes
 */
class HomeController extends Controller
{
    /**
     * Show home page
     */
    public function index()
    {
        return $this->view('pages/home', [
            'pageTitle' => 'Home'
        ]);
    }
}
