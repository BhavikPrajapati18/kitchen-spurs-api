<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class RestaurantDataService
{
    protected string $restaurantsPath;
    protected string $ordersPath;

    public function __construct()
    {
        $this->restaurantsPath = storage_path('app/data/restaurants.json');
        $this->ordersPath = storage_path('app/data/orders.json');
    }

    public function getRestaurants(): Collection
    {
        $data = json_decode(file_get_contents($this->restaurantsPath), true);

        return collect($data);
    }

    public function getOrders(): Collection
    {
        $data = json_decode(file_get_contents($this->ordersPath), true);

        return collect($data);
    }

    public function getRestaurantById(int $id): ?array
    {
        return $this->getRestaurants()->firstWhere('id', $id);
    }

    public function getFilteredOrders(array $filters = []): Collection
    {
        $orders = $this->getOrders();

        if (!empty($filters['restaurant_id'])) {
            $orders = $orders->where('restaurant_id', (int) $filters['restaurant_id']);
        }

        if (!empty($filters['start_date'])) {
            $orders = $orders->filter(function ($order) use ($filters) {
                return Carbon::parse($order['order_time'])->toDateString() >= $filters['start_date'];
            });
        }

        if (!empty($filters['end_date'])) {
            $orders = $orders->filter(function ($order) use ($filters) {
                return Carbon::parse($order['order_time'])->toDateString() <= $filters['end_date'];
            });
        }

        if (isset($filters['min_amount']) && $filters['min_amount'] !== '') {
            $orders = $orders->where('order_amount', '>=', (float) $filters['min_amount']);
        }

        if (isset($filters['max_amount']) && $filters['max_amount'] !== '') {
            $orders = $orders->where('order_amount', '<=', (float) $filters['max_amount']);
        }

        if (isset($filters['start_hour']) && $filters['start_hour'] !== '') {
            $orders = $orders->filter(function ($order) use ($filters) {
                $hour = Carbon::parse($order['order_time'])->hour;
                return $hour >= (int) $filters['start_hour'];
            });
        }

        if (isset($filters['end_hour']) && $filters['end_hour'] !== '') {
            $orders = $orders->filter(function ($order) use ($filters) {
                $hour = Carbon::parse($order['order_time'])->hour;
                return $hour <= (int) $filters['end_hour'];
            });
        }

        return $orders->values();
    }
}