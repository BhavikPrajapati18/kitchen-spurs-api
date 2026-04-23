<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RestaurantDataService;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function __construct(private RestaurantDataService $dataService)
    {
    }

  public function index(Request $request)
{
    $restaurants = $this->dataService->getRestaurants();
    $orders = $this->dataService->getOrders();

    $restaurantStats = $orders
        ->groupBy('restaurant_id')
        ->map(function ($restaurantOrders) {
            $totalOrders = $restaurantOrders->count();
            $totalRevenue = $restaurantOrders->sum('order_amount');

            return [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'average_order_value' => $totalOrders > 0
                    ? round($totalRevenue / $totalOrders, 2)
                    : 0,
            ];
        });

    $restaurants = $restaurants->map(function ($restaurant) use ($restaurantStats) {
        $stats = $restaurantStats->get($restaurant['id'], [
            'total_orders' => 0,
            'total_revenue' => 0,
            'average_order_value' => 0,
        ]);

        return array_merge($restaurant, $stats);
    });

    $search = $request->query('search');
    $sortBy = $request->query('sort_by', 'id');
    $sortOrder = $request->query('sort_order', 'asc');
    $page = (int) $request->query('page', 1);
    $perPage = (int) $request->query('per_page', 10);

    if ($search) {
        $search = strtolower($search);

        $restaurants = $restaurants->filter(function ($restaurant) use ($search) {
            return str_contains(strtolower($restaurant['name']), $search)
                || str_contains(strtolower($restaurant['location']), $search)
                || str_contains(strtolower($restaurant['cuisine']), $search);
        });
    }

    if (in_array($sortBy, [
        'id',
        'name',
        'location',
        'cuisine',
        'total_orders',
        'total_revenue',
        'average_order_value'
    ])) {
        $restaurants = $sortOrder === 'desc'
            ? $restaurants->sortByDesc($sortBy)
            : $restaurants->sortBy($sortBy);
    }

    $restaurants = $restaurants->values();

    $total = $restaurants->count();
    $offset = ($page - 1) * $perPage;
    $paginated = $restaurants->slice($offset, $perPage)->values();

    return response()->json([
        'data' => $paginated,
        'meta' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => (int) ceil($total / $perPage),
        ],
    ]);
}

    public function show(int $id)
    {
        $restaurant = $this->dataService->getRestaurantById($id);

        if (!$restaurant) {
            return response()->json([
                'message' => 'Restaurant not found',
            ], 404);
        }

        return response()->json($restaurant);
    }
}