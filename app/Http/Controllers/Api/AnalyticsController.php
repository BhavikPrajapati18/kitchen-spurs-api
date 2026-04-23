<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RestaurantDataService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private RestaurantDataService $dataService)
    {
    }

    public function restaurantAnalytics(Request $request, int $id)
    {
        $restaurant = $this->dataService->getRestaurantById($id);

        if (!$restaurant) {
            return response()->json([
                'message' => 'Restaurant not found',
            ], 404);
        }
$request->validate([
    'start_date' => ['nullable', 'date'],
    'end_date' => ['nullable', 'date'],
    'min_amount' => ['nullable', 'numeric'],
    'max_amount' => ['nullable', 'numeric'],
    'start_hour' => ['nullable', 'integer', 'min:0', 'max:23'],
    'end_hour' => ['nullable', 'integer', 'min:0', 'max:23'],
]);
        $filters = [
            'restaurant_id' => $id,
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'min_amount' => $request->query('min_amount'),
            'max_amount' => $request->query('max_amount'),
            'start_hour' => $request->query('start_hour'),
            'end_hour' => $request->query('end_hour'),
        ];

        $orders = $this->dataService->getFilteredOrders($filters);

        $dailyMetrics = $orders
            ->groupBy(function ($order) {
                return Carbon::parse($order['order_time'])->toDateString();
            })
            ->map(function ($dayOrders, $date) {
                $ordersCount = $dayOrders->count();
                $revenue = $dayOrders->sum('order_amount');
                $averageOrderValue = $ordersCount > 0 ? round($revenue / $ordersCount, 2) : 0;

                $peakHourGroup = $dayOrders
                    ->groupBy(function ($order) {
                        return Carbon::parse($order['order_time'])->format('H');
                    })
                    ->sortByDesc(function ($hourOrders) {
                        return $hourOrders->count();
                    })
                    ->first();

                $peakOrderHour = null;

                if ($peakHourGroup && count($peakHourGroup) > 0) {
                    $peakOrderHour = Carbon::parse($peakHourGroup[0]['order_time'])->format('H:00');
                }

                return [
                    'date' => $date,
                    'orders_count' => $ordersCount,
                    'revenue' => $revenue,
                    'average_order_value' => $averageOrderValue,
                    'peak_order_hour' => $peakOrderHour,
                ];
            })
            ->sortBy('date')
            ->values();

        $totalOrders = $orders->count();
        $totalRevenue = $orders->sum('order_amount');
        $overallAov = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;

        return response()->json([
            'restaurant' => $restaurant,
            'filters' => [
                'start_date' => $filters['start_date'],
                'end_date' => $filters['end_date'],
                'min_amount' => $filters['min_amount'],
                'max_amount' => $filters['max_amount'],
                'start_hour' => $filters['start_hour'],
                'end_hour' => $filters['end_hour'],
            ],
            'daily_metrics' => $dailyMetrics,
            'summary' => [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'average_order_value' => $overallAov,
            ],
        ]);
    }

    public function topRestaurants(Request $request)
    {
        $request->validate([
    'start_date' => ['nullable', 'date'],
    'end_date' => ['nullable', 'date'],
    'min_amount' => ['nullable', 'numeric'],
    'max_amount' => ['nullable', 'numeric'],
    'start_hour' => ['nullable', 'integer', 'min:0', 'max:23'],
    'end_hour' => ['nullable', 'integer', 'min:0', 'max:23'],
]);
        $filters = [
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'min_amount' => $request->query('min_amount'),
            'max_amount' => $request->query('max_amount'),
            'start_hour' => $request->query('start_hour'),
            'end_hour' => $request->query('end_hour'),
        ];

        $orders = $this->dataService->getFilteredOrders($filters);
        $restaurants = $this->dataService->getRestaurants()->keyBy('id');

        $topRestaurants = $orders
            ->groupBy('restaurant_id')
            ->map(function ($restaurantOrders, $restaurantId) use ($restaurants) {
                $restaurant = $restaurants->get($restaurantId);

                return [
                    'restaurant_id' => (int) $restaurantId,
                    'restaurant_name' => $restaurant['name'] ?? 'Unknown',
                    'location' => $restaurant['location'] ?? null,
                    'cuisine' => $restaurant['cuisine'] ?? null,
                    'total_orders' => $restaurantOrders->count(),
                    'revenue' => $restaurantOrders->sum('order_amount'),
                    'average_order_value' => $restaurantOrders->count() > 0
                        ? round($restaurantOrders->sum('order_amount') / $restaurantOrders->count(), 2)
                        : 0,
                ];
            })
            ->sortByDesc('revenue')
            ->take(3)
            ->values();

        return response()->json([
            'filters' => $filters,
            'data' => $topRestaurants,
        ]);
    }
}