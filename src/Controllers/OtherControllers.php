<?php

namespace App\Controllers;

use App\Controller;

/**
 * PricingController — Pricing page
 */
class PricingController extends Controller
{
    public function index()
    {
        echo '<h1>Pricing</h1>';
        echo '<p>Pricing page coming soon.</p>';
    }
}

/**
 * FeaturesController — Features page
 */
class FeaturesController extends Controller
{
    public function index()
    {
        echo '<h1>Features</h1>';
        echo '<p>Features page coming soon.</p>';
    }
}

/**
 * AboutController — About page
 */
class AboutController extends Controller
{
    public function index()
    {
        echo '<h1>About</h1>';
        echo '<p>About page coming soon.</p>';
    }
}

/**
 * WhitelistController — Whitelist management
 */
class WhitelistController extends Controller
{
    public function index()
    {
        echo '<h1>Whitelist</h1>';
    }

    public function add()
    {
        if ($this->isPost()) {
            $this->json(['success' => 'Account whitelisted']);
        }
    }

    public function remove($id)
    {
        if ($this->isPost()) {
            $this->json(['success' => 'Account removed from whitelist']);
        }
    }
}

/**
 * KanbanController — Kanban board visualization
 */
class KanbanController extends Controller
{
    public function index()
    {
        echo '<h1>Kanban Board</h1>';
    }

    public function getCards()
    {
        $this->json(['cards' => []]);
    }

    public function moveCard($id)
    {
        $this->json(['success' => 'Card moved']);
    }

    public function updateCard($id)
    {
        $this->json(['success' => 'Card updated']);
    }
}

/**
 * ActivityController — Activity calendar and feed
 */
class ActivityController extends Controller
{
    public function index()
    {
        echo '<h1>Activity Calendar</h1>';
    }

    public function getCalendar($year, $month)
    {
        $this->json(['days' => []]);
    }
}
