<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $title = 'dashboard';

        $today_sales           = Sale::whereDate('created_at', Carbon::today())->sum('total_price');
        $total_categories      = Category::count();
        $total_users           = User::count();
        $total_expired         = Purchase::whereDate('expiry_date', '<=', Carbon::today())->count();
        $low_stock_count       = Purchase::where('quantity', '>', 0)->where('quantity', '<=', 10)->count();

        $total_purchases       = Purchase::count();
        $total_suppliers       = Supplier::count();
        $total_sales           = Sale::count();
        $monthly_revenue       = Sale::whereMonth('created_at', Carbon::now()->month)
                                     ->whereYear('created_at', Carbon::now()->year)
                                     ->sum('total_price');

        // Last 7 days revenue for the sparkline chart
        $weekly_revenue = [];
        $weekly_labels  = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $weekly_labels[]  = $day->format('D');
            $weekly_revenue[] = Sale::whereDate('created_at', $day)->sum('total_price');
        }

        $pieChart = app()->chartjs
            ->name('pieChart')
            ->type('doughnut')
            ->size(['width' => 400, 'height' => 220])
            ->labels(['Purchases', 'Suppliers', 'Sales'])
            ->datasets([[
                'backgroundColor'      => ['#4361ee', '#7bc67e', '#f72585'],
                'hoverBackgroundColor' => ['#3451db', '#6ab86e', '#e01070'],
                'data'                 => [$total_purchases, $total_suppliers, $total_sales],
                'borderWidth'          => 2,
            ]])
            ->options([
                'cutoutPercentage' => 70,
                'legend'           => ['position' => 'bottom'],
            ]);

        return view('admin.dashboard', compact(
            'title',
            'pieChart',
            'today_sales',
            'total_categories',
            'total_users',
            'total_expired',
            'low_stock_count',
            'monthly_revenue',
            'weekly_revenue',
            'weekly_labels'
        ));
    }
}
