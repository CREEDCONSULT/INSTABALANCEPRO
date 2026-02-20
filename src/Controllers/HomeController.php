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
        // TODO: Render home/landing page template
        echo '<h1>Welcome to UnfollowIQ</h1>';
        echo '<p>Home page content coming in PROMPT 5.</p>';
    }
}
