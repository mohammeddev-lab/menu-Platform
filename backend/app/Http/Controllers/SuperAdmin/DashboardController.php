<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\DashboardAnalyticsService;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardAnalyticsService $analyticsService
    ) {}

    public function index()
    {
        return response()->json(
            $this->analyticsService->superAdminDashboard()
        );
    }
}
