# Kitchen Spurs API

Backend API for the **Restaurant Order Trends Dashboard**.

This project is built using Laravel and processes restaurant and order data from JSON files to provide analytics such as daily orders, revenue, average order value, peak order hour, and top-performing restaurants.

---

## 🚀 Tech Stack

- Laravel (PHP)
- REST APIs
- JSON-based data storage

---

## 📊 Features

### Restaurants

- View all restaurants
- Search by name, location, or cuisine
- Sort by:
  - id
  - name
  - location
  - cuisine
  - total orders
  - total revenue
  - average order value
- Pagination support
- View single restaurant details

---

### Analytics (Per Restaurant)

For a selected restaurant and date range:

- Daily orders count
- Daily revenue
- Average order value (AOV)
- Peak order hour per day

---

### Top Restaurants

- Top 3 restaurants by revenue
- Works with filters

---

### Filters Supported

- Date range
- Amount range
- Hour range
- Restaurant

---

## 📂 Dataset

Data is stored in JSON files:

```txt
storage/app/data/restaurants.json
storage/app/data/orders.json
```

## ⚙️ Setup Instructions
1. Clone the repository
git clone <your-backend-repo-url>
cd kitchen-spurs-api
2. Install dependencies
composer install)

3. Generate app key
php artisan key:generate
4. Run migrations
php artisan migrate
5. Start server
php artisan serve

## Server runs at:

http://127.0.0.1:8000
⚠️ Important

Ensure JSON files exist:

storage/app/data/restaurants.json
storage/app/data/orders.json
