<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Spatie multi-tenancy roles
        $isStaffOnly = !$user->hasAnyRole(['Owner', 'Admin']);

        // Base Query (Tenant context is automatically handled by MultiTenantTrait)
        $ordersQuery = Order::query();
        if ($isStaffOnly) {
            $ordersQuery->where('user_id', $user->id);
        }

        // Today's metrics
        $todayOrdersQuery = clone $ordersQuery;
        $todayOrdersQuery->whereDate('created_at', Carbon::today());
        $todaySales = $todayOrdersQuery->sum('total_amount');
        $todayOrdersCount = $todayOrdersQuery->count();

        // Monthly metrics
        $monthlyOrdersQuery = clone $ordersQuery;
        $monthlyOrdersQuery->whereMonth('created_at', Carbon::now()->month)
                           ->whereYear('created_at', Carbon::now()->year);
        $monthlySales = $monthlyOrdersQuery->sum('total_amount');

        // Recent Orders
        $recentOrders = (clone $ordersQuery)
                        ->with(['customer', 'user'])
                        ->latest()
                        ->take(5)
                        ->get();

        // Chart Data (Last 7 Days Sales)
        $chartData = [];
        $chartLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->format('M d');
            
            $daySales = (clone $ordersQuery)
                        ->whereDate('created_at', $date)
                        ->sum('total_amount');
            $chartData[] = $daySales;
        }

        return view('dashboard', compact(
            'todaySales', 
            'todayOrdersCount', 
            'monthlySales', 
            'recentOrders', 
            'chartLabels', 
            'chartData',
            'isStaffOnly'
        ));
    }
}
