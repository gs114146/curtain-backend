<?php

namespace App\Http\Controllers;

use App\Models\Dashboard;
use App\Models\Product;
use App\Models\Color;
use App\Models\Design;
use App\Models\Material;
use App\Models\Transparency;
use App\Models\Characteristic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the dashboard with dynamic data.
     */
    public function index()
    {
        // Get basic counts
        $totalProducts = Product::count();
        $totalColors = Color::count();
        $totalDesigns = Design::count();
        $totalMaterials = Material::count();
        $totalTransparencies = Transparency::count();
        $totalCharacteristics = Characteristic::count();
        $totalUsers = User::count();

        // Get active products
        $activeProducts = Product::where('status', 'active')->count();
        $featuredProducts = Product::where('featured', true)->count();
        $inStockProducts = Product::where('in_stock', true)->count();

        // Calculate total inventory value
        $totalInventoryValue = Product::where('in_stock', true)
            ->sum(DB::raw('price * stock_quantity'));

        // Get recent products (last 30 days)
        $recentProducts = Product::where('created_at', '>=', Carbon::now()->subDays(30))->count();

        // Get products by status
        $productsByStatus = Product::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Get top colors by product count
        $topColors = Color::withCount('products')
            ->orderBy('products_count', 'desc')
            ->limit(5)
            ->get();

        // Get top designs by product count
        $topDesigns = Design::withCount('products')
            ->orderBy('products_count', 'desc')
            ->limit(5)
            ->get();

        // Get top materials by product count
        $topMaterials = Material::withCount('products')
            ->orderBy('products_count', 'desc')
            ->limit(5)
            ->get();

        // Calculate growth percentages (mock data for now - you can implement real calculations)
        $productGrowth = $this->calculateGrowthPercentage($totalProducts, $recentProducts);
        $inventoryGrowth = $this->calculateInventoryGrowth($totalInventoryValue);

        // Get monthly product creation data for charts
        $monthlyProducts = Product::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Get current user
        $user = auth()->user();

        return view('admin.dashboard.index', compact(
            'totalProducts',
            'totalColors',
            'totalDesigns',
            'totalMaterials',
            'totalTransparencies',
            'totalCharacteristics',
            'totalUsers',
            'activeProducts',
            'featuredProducts',
            'inStockProducts',
            'totalInventoryValue',
            'recentProducts',
            'productsByStatus',
            'topColors',
            'topDesigns',
            'topMaterials',
            'productGrowth',
            'inventoryGrowth',
            'monthlyProducts',
            'user'
        ));
    }

    /**
     * Calculate growth percentage
     */
    private function calculateGrowthPercentage($current, $recent)
    {
        if ($current == 0) return 0;
        return round(($recent / $current) * 100, 2);
    }

    /**
     * Calculate inventory growth (mock calculation)
     */
    private function calculateInventoryGrowth($currentValue)
    {
        // Mock calculation - you can implement real logic here
        return round(($currentValue * 0.15), 2); // 15% growth
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Dashboard $dashboard)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dashboard $dashboard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dashboard $dashboard)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dashboard $dashboard)
    {
        //
    }
}
