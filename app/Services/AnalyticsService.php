<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\DamageItemDetail;

class AnalyticsService
{
    protected int $cacheTtl = 300; // seconds

    /**
     * Public entry: returns the detailed report array
     */
    public function generate(int $startYear, int $startMonth, int $endYear, int $endMonth, ?int $customerId = null, ?int $brandId = null): array
    {
        // Step 1: Months
        $months = getNepaliMonthRange($startYear, $startMonth, $endYear, $endMonth);
        $monthKeys = array_map(fn($entry) =>
            $entry['year'] . '-' . str_pad($entry['month'], 2, '0', STR_PAD_LEFT),
            $months
        );

        // Step 2: Customers
        $customerQuery = User::select('id', 'name', 'type');
        if ($customerId) {
            $customerQuery->where('id', $customerId);
        }
        $customers = $customerQuery->get();
        $customerMap = $customers->keyBy('id')->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'type' => $c->type,
        ])->toArray();

        // Step 3: Orders
        $orders = Order::select('orderid', 'user_id', 'nepyear', 'nepmonth', 'discount')
            ->where('mainstatus', 'approved')
            ->when($customerId, fn($q) => $q->where('user_id', $customerId))
            ->when($brandId, fn($q) => $q->where('brand_id', $brandId))
            ->where(function ($query) use ($startYear, $startMonth, $endYear, $endMonth) {
                $query->where(function ($q) use ($startYear, $startMonth) {
                    $q->where('nepyear', '>', $startYear)
                        ->orWhere(function ($q2) use ($startYear, $startMonth) {
                            $q2->where('nepyear', $startYear)
                                ->where('nepmonth', '>=', $startMonth);
                        });
                })->where(function ($q) use ($endYear, $endMonth) {
                    $q->where('nepyear', '<', $endYear)
                        ->orWhere(function ($q2) use ($endYear, $endMonth) {
                            $q2->where('nepyear', $endYear)
                                ->where('nepmonth', '<=', $endMonth);
                        });
                });
            })
            ->get();

        $orderMeta = $orders->keyBy('orderid');
        $orderIds = $orders->pluck('orderid')->toArray();

        // Step 4: Order items
        $items = OrderItem::whereIn('orderid', $orderIds)
            ->where('status', 'approved')
            ->get();

        // Step 5: Base structure
        $data = [];
        foreach ($monthKeys as $monthKey) {
            [$year, $month] = explode('-', $monthKey);
            $startDate = getEnglishDate($year, intval($month), 1);
            $endDate   = getEnglishDate($year, intval($month), getLastDate(intval($month), $year % 100));

            $data[$monthKey] = [
                'net_sales'  => 0,
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'customers'  => [],
            ];

            foreach ($customerMap as $custId => $custData) {
                $data[$monthKey]['customers'][$custId] = [
                    'id'    => $custData['id'],
                    'name'  => $custData['name'],
                    'sales' => 0,
                    'type'  => $custData['type'],
                ];
            }
        }

        // Step 6: Aggregate sales
        foreach ($items as $item) {
            $order = $orderMeta[$item->orderid] ?? null;
            if (!$order) continue;

            $year  = $order->nepyear;
            $month = str_pad($order->nepmonth, 2, '0', STR_PAD_LEFT);
            $key   = $year . '-' . $month;
            if (!isset($data[$key])) continue;

            $customerIdFromOrder = $order->user_id;
            if (!isset($data[$key]['customers'][$customerIdFromOrder])) {
                $data[$key]['customers'][$customerIdFromOrder] = [
                    'id'    => $customerIdFromOrder,
                    'name'  => $customerMap[$customerIdFromOrder]['name'] ?? 'Unknown Customer',
                    'sales' => 0,
                    'type'  => $customerMap[$customerIdFromOrder]['type'] ?? null,
                ];
            }

            $gross = $item->approvedquantity * $item->price;
            $discount = $order->discount ?? 0;
            $netSale = $gross - ($gross * $discount / 100);

            $data[$key]['net_sales'] += $netSale;
            $data[$key]['customers'][$customerIdFromOrder]['sales'] += $netSale;
        }

        // Step 7: Quarters
        $quarters = [];
        foreach ($monthKeys as $key) {
            [$year, $month] = explode('-', $key);
            $quarter = ceil($month / 3);
            $quarters["{$year}-Q{$quarter}"][] = $key;
        }

        $quarterSales = [];
        foreach ($quarters as $quarter => $months) {
            $quarterSales[$quarter] = array_sum(array_map(fn($mKey) => $data[$mKey]['net_sales'] ?? 0, $months));
        }

        // Step 8: Sort customers per month
        foreach ($data as $monthKey => &$monthData) {
            $monthData['customers'] = collect($monthData['customers'])->sortByDesc('sales')->values()->toArray();
        }

        // Step 9: Totals / averages
        $totalSales  = array_sum(array_column($data, 'net_sales'));
        $totalBills  = $orders->count();
        $totalMonths = count($monthKeys);

        $startDate = Carbon::parse(getEnglishDate($startYear, $startMonth, 1));
        $endDate   = Carbon::parse(getEnglishDate($endYear, $endMonth, getLastDate($endMonth, $endYear % 100)));
        if ($endDate->greaterThan(Carbon::today())) {
            $endDate = Carbon::today();
        }
        $totalDays = $startDate->diffInDays($endDate) + 1;

        $averageSales = [
            'total_bills'   => $totalBills,
            'total_sales'   => $totalSales,
            'avg_per_bill'  => $totalBills > 0 ? ($totalSales / $totalBills) : 0,
            'avg_per_month' => $totalMonths > 0 ? ($totalSales / $totalMonths) : 0,
            'avg_per_day'   => $totalDays > 0 ? ($totalSales / $totalDays) : 0,
        ];

        return [
            'data'          => $data,
            'months'        => $monthKeys,
            'customers'     => collect($customerMap)->mapWithKeys(fn($c) => [$c['id'] => $c['name']])->toArray(),
            'quarter_sales' => $quarterSales,
            'overall_sales' => $totalSales,
            'average_sales' => $averageSales,
        ];
    }


    public function getSalesData(?int $customerId, string $startDate, string $endDate, ?int $brandId): array
    {
        $netSalesData = DB::table('products')
             ->when($brandId, function ($q) use ($brandId) {
                $q->where('products.brand_id', $brandId);
            })
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function ($join) use ($startDate, $endDate, $customerId, $brandId) {
                $join->on('orders.id', '=', 'order_items.order_id')
                    ->whereIn('orders.current_status', ['approved', 'packing', 'shipped', 'delivered'])
                    ->whereBetween('orders.order_date', [$startDate, $endDate]);
                if ($customerId) {
                    $join->where('orders.user_id', $customerId);
                }
                if ($brandId) {
                    $join->where('products.brand_id', $brandId);
                }
            })
            ->select(
                'categories.id as category_id',
                'categories.category as category_name',
                'products.id as product_id',
                'products.name as product_name',
                'products.hide',
                // 'products.sub_category_id',
                // Only count quantity when the order actually matched the join AND item is approved
                DB::raw('COALESCE(SUM(CASE WHEN orders.id IS NOT NULL AND orders.current_status IN ("approved","packing","shipped","delivered") THEN order_items.quantity ELSE 0 END), 0) as total_quantity'),
                // Only sum sales when the order matched and item is approved; guard discount with COALESCE
                DB::raw('COALESCE(SUM(CASE WHEN orders.id IS NOT NULL AND orders.current_status IN ("approved","packing","shipped","delivered") THEN order_items.quantity * order_items.discounted_price ELSE 0 END), 0) as total_sales')
            )
            ->groupBy(
                'categories.id',
                'categories.category',
                'products.id',
                'products.name',
                'products.hide',
                // 'products.sub_category_id'
            )
            ->get();

        // Eager load categories with subCategories
        $categoriesWithSubs = \App\Models\Category::get()->keyBy('id');

        $categoryStats = $netSalesData
            ->groupBy('category_id')
            ->map(function ($products, $categoryId) use ($categoriesWithSubs) {
                $category = $categoriesWithSubs->get($categoryId);

                return [
                    'category_name'   => $products->first()->category_name,
                    'category_id'     => $categoryId,
                    'total_quantity'  => $products->sum('total_quantity'),
                    'total_sales'     => $products->sum('total_sales'),
                    'sub_categories'  => $category?->subCategories ?? [],
                    'products'        => $products
                        ->map(fn($p) => [
                            'product_name'    => $p->product_name,
                            'product_id'      => $p->product_id,
                            'hidden'          => (bool) $p->hide,
                            'sub_category_id' => '',
                            'quantity'        => $p->total_quantity,
                            'sales'           => $p->total_sales,
                        ])
                        ->sortByDesc('sales')
                        ->values()
                        ->all(),
                ];
            })
            ->sortByDesc('total_sales')
            ->values()
            ->all();

        return [
            'overall_sales' => $netSalesData->sum('total_sales'),
            'categories'    => $categoryStats,
        ];
    }




     public function getSortAnalytics(?int $customerId, ?int $productId, string $startDate, string $endDate): array
    {
        if ($productId && !$customerId) {
            return $this->getProductWiseAnalytics($productId, $startDate, $endDate);
        }

        if (!$productId && $customerId) {
            return $this->getCustomerWiseAnalytics($customerId, $startDate, $endDate);
        }

        if ($productId && $customerId) {
            return $this->getCustomerProductAnalytics($customerId, $productId, $startDate, $endDate);
        }

        return [];
    }

    protected function getProductWiseAnalytics(int $productId, string $startDate, string $endDate): array
    {
        $product = Product::find($productId);
        $users = User::with([
            'orders' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate])
                    ->with(['items' => fn($query) => $query->where('status', 'approved')]);
            }
        ])->get();

        $result = [];
        $totalQty = 0;
        $totalSales = 0;

        foreach ($users as $user) {
            $qty = 0;
            $sales = 0;

            foreach ($user->orders as $order) {
                foreach ($order->items as $item) {
                    if ($item->product_id == $product->id) {
                        $lineTotal = $item->approvedquantity * $item->price;
                        $discountAmount = $order->discount ? ($lineTotal * ($order->discount / 100)) : 0;
                        $qty += $item->approvedquantity;
                        $sales += $lineTotal - $discountAmount;
                    }
                }
            }

            $result[] = [
                'id' => $user->id,
                'name' => $user->name,
                'contact' => $user->contact,
                'type' => $user->type,
                'quantity' => $qty,
                'sales' => $sales,
            ];

            $totalQty += $qty;
            $totalSales += $sales;
        }

        return [
            'data' => collect($result)->sortByDesc('sales')->values()->all(),
            'total_quantity' => $totalQty,
            'total_sales' => $totalSales,
        ];
    }

    protected function getCustomerWiseAnalytics(int $customerId, string $startDate, string $endDate): array
    {
        $orders = Order::where('user_id', $customerId)
            ->where('mainstatus', 'approved')
            ->whereBetween('date', [$startDate, $endDate])
            ->with('items')
            ->get();

        $categories = Category::with('products')->get();
        $orderItems = $orders->pluck('items')->flatten();

        $categoryStats = [];
        $overallQty = 0;
        $overallSales = 0;

        foreach ($categories as $category) {
            $catQty = 0;
            $catSales = 0;
            $products = [];

            foreach ($category->products as $product) {
                $qty = 0;
                $sales = 0;

                foreach ($orderItems as $item) {
                    if ($item->product_id == $product->id) {
                        $order = $orders->firstWhere('orderid', $item->orderid);
                        $lineTotal = $item->approvedquantity * $item->price;
                        $discountAmount = $order->discount ? ($lineTotal * ($order->discount / 100)) : 0;
                        $qty += $item->approvedquantity;
                        $sales += $lineTotal - $discountAmount;
                    }
                }

                $products[] = [
                    'product_name' => $product->name,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'sales' => $sales,
                ];

                $catQty += $qty;
                $catSales += $sales;
            }

            $categoryStats[] = [
                'category_name' => $category->name,
                'category_id' => $category->id,
                'total_quantity' => $catQty,
                'total_sales' => $catSales,
                'products' => collect($products)->sortByDesc('sales')->values()->all(),
            ];

            $overallQty += $catQty;
            $overallSales += $catSales;
        }

        return [
            'overall_sales' => $overallSales,
            'overall_quantity' => $overallQty,
            'categories' => collect($categoryStats)->sortByDesc('total_sales')->values()->all(),
        ];
    }

    protected function getCustomerProductAnalytics(int $customerId, int $productId, string $startDate, string $endDate): array
    {
        $orders = Order::where('user_id', $customerId)
            ->where('mainstatus', 'approved')
            ->whereBetween('date', [$startDate, $endDate])
            ->with('items')
            ->orderByDesc('date')
            ->get();

        $result = [];
        $totalQty = 0;
        $totalSales = 0;

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                if ($item->product_id == $productId) {
                    $lineTotal = $item->approvedquantity * $item->price;
                    $discountAmount = $order->discount ? ($lineTotal * ($order->discount / 100)) : 0;
                    $net = $lineTotal - $discountAmount;

                    $result[] = [
                        'id' => $order->id,
                        'orderid' => $order->orderid,
                        'date' => $order->date,
                        'name' => $order->user->name,
                        'quantity' => $item->approvedquantity,
                        'sales' => $net,
                    ];

                    $totalQty += $item->approvedquantity;
                    $totalSales += $net;
                }
            }
        }

        return [
            'data' => collect($result)->sortByDesc('date')->values()->all(),
            'total_quantity' => $totalQty,
            'total_sales' => $totalSales,
        ];
    }

  public function getReportData(
        ?int $customerId,
        ?int $categoryId,
        int $startYear,
        int $startMonth,
        int $endYear,
        int $endMonth,
        ?int $brandId
    ): array {
        // Step 1: Build Nepali month range
        $months = getNepaliMonthRange($startYear, $startMonth, $endYear, $endMonth);
        $monthKeys = [];
        foreach ($months as $entry) {
            $key = $entry['year'] . '-' . str_pad($entry['month'], 2, '0', STR_PAD_LEFT);
            $monthKeys[] = $key;
        }

        // Step 2: Categories & Products map (with brand filter)
        $categoriesQuery = Category::with(['products' => function ($query) use ($brandId) {
            if ($brandId !== null) {
                $query->where('brand_id', $brandId);
            }
        }]);
        
        $categories = $categoriesQuery->get();
        $categoryMap = $categories->mapWithKeys(fn($cat) => [$cat->id => $cat->name]);

        $productToCategory = [];
        $productMap = [];
        $allowedProductIds = []; // Track allowed product IDs for brand filtering
        
        foreach ($categories as $category) {
            foreach ($category->products as $product) {
                $productToCategory[$product->id] = $category->id;
                $productMap[$product->id] = $product->name;
                $allowedProductIds[] = $product->id;
            }
        }

        // Step 3: Define columns (categories or products)
        $columns = [];
        if ($categoryId) {
            $selectedCategory = $categories->firstWhere('id', $categoryId);
            $productIds = $selectedCategory ? $selectedCategory->products->pluck('id')->toArray() : [];
            foreach ($productIds as $pid) {
                $columns[$pid] = $productMap[$pid] ?? 'Unknown';
            }
        } else {
            $columns = $categoryMap->toArray();
        }

        // Step 4: Orders filtering
        $ordersQuery = Order::where('mainstatus', 'approved')
            ->where(function ($query) use ($startYear, $startMonth, $endYear, $endMonth) {
                $query->where(function ($q) use ($startYear, $startMonth) {
                    $q->where('nepyear', '>', $startYear)
                        ->orWhere(function ($q2) use ($startYear, $startMonth) {
                            $q2->where('nepyear', $startYear)
                                ->where('nepmonth', '>=', $startMonth);
                        });
                })->where(function ($q) use ($endYear, $endMonth) {
                    $q->where('nepyear', '<', $endYear)
                        ->orWhere(function ($q2) use ($endYear, $endMonth) {
                            $q2->where('nepyear', $endYear)
                                ->where('nepmonth', '<=', $endMonth);
                        });
                });
            });

        if ($customerId) {
            $ordersQuery->where('user_id', $customerId);
        }

        $orders = $ordersQuery->pluck('orderid');

        // Step 5: Approved items (with brand filter via product IDs)
        $itemsQuery = OrderItem::with('order')
            ->whereIn('orderid', $orders)
            ->where('status', 'approved');
        
        // Apply brand filter by limiting to allowed product IDs
        if ($brandId !== null && !empty($allowedProductIds)) {
            $itemsQuery->whereIn('product_id', $allowedProductIds);
        } elseif ($brandId !== null && empty($allowedProductIds)) {
            // No products match the brand, return empty result
            $itemsQuery->whereRaw('1 = 0');
        }
        
        $items = $itemsQuery->get();

        // Step 6: Init table
        $data = [];
        foreach ($monthKeys as $key) {
            $data[$key] = [];
            foreach ($columns as $colKey => $colName) {
                $data[$key][$colName] = 0;
            }
        }

        // Step 7: Fill table
        foreach ($items as $item) {
            $order = $item->order;
            if (!$order) continue;

            $year = $order->nepyear;
            $month = str_pad($order->nepmonth, 2, '0', STR_PAD_LEFT);
            $key = $year . '-' . $month;

            if (!isset($data[$key])) continue;

            if ($categoryId) {
                // Product-based
                if (!isset($columns[$item->product_id])) continue;
                $productName = $columns[$item->product_id];
                $data[$key][$productName] += $item->approvedquantity;
            } else {
                // Category-based
                $catId = $productToCategory[$item->product_id] ?? null;
                if (!$catId) continue;

                $categoryName = $columns[$catId] ?? 'Unknown';
                if (!isset($data[$key][$categoryName])) continue;
                $data[$key][$categoryName] += $item->approvedquantity;
            }
        }

        return [
            'categoryId' => $categoryId,
            'categories' => array_values($columns),
            'data' => $data,
        ];
    }


    public function getDamageAnalyticsData(array $filters = [])
    {
        $query = DamageItemDetail::query()
            ->with([
                'product',
                'batch',
                'problem',
                'damageItem',
                'damageItem.damage',
                'damageItem.damage.user',
                'replacedProduct',
            ])
            ->whereHas('damageItem', fn ($q) => $q->where('instatus', 'completed'));

        // ✅ Apply filters
        if (!empty($filters['customerId'])) {
            $query->whereHas('damage', fn ($q) =>
                $q->where('user_id', $filters['customerId'])
            );
        }

        if (!empty($filters['productId'])) {
            $query->where('product_id', $filters['productId']);
        }

        if (!empty($filters['categoryId'])) {
            $query->whereHas('product', fn ($q) =>
                $q->where('category_id', $filters['categoryId'])
            );
        }

        if (!empty($filters['batchId'])) {
            $query->where('batch_id', $filters['batchId']);
        }

        if (!empty($filters['problemId'])) {
            $query->where('problem_id', $filters['problemId']);
        }

        if (!empty($filters['partId'])) {
            $query->whereJsonContains('replaced_part', (string) $filters['partId']);
        }

        if (!empty($filters['solution'])) {
            $query->where('solution', $filters['solution']);
        }

        if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
            $query->whereHas('damage', fn ($q) =>
                $q->whereBetween('date', [
                    Carbon::parse($filters['startDate'])->startOfDay(),
                    Carbon::parse($filters['endDate'])->endOfDay(),
                ])
            );
        }

        return $query->get()->map(function ($item) {
            return [
                'Damage Date' => optional($item->damage)->date?->format('d-m-Y') ?? '-',
                'damageId' => $item->damage?->id ?? '-',
                'Customer' => optional($item->damage?->user)->name ?? '-',
                'Invoice ID' => $item->invoice_id,
                'Product' => optional($item->product)->name ?? '-',
                'Category' => optional($item->product?->category)->name ?? '-',
                'Batch No' => optional($item->batch)->batch_no ?? '-',
                'Problem' => optional($item->problem)->problem ?? '-',
                'Part Replaced' => $item->getReplacedPartNamesAttribute() ?? '-',
                'Replaced Product' => optional($item->replacedProduct)->name ?? '-',
                'Solution' => $item->solution ?? '-',
                'Condition' => $item->condition ?? '-',
                'Warranty' => $item->warranty ?? '-',
                'Detail Quantity' => $item->quantity ?? 0,
                'Item Quantity' => $item->damageItem?->quantity ?? 0,
                'Remarks' => $item->remarks ?? '-',
                'instatus' => $item->damageItem?->instatus ?? '-',
                'mainstatus' => $item->damageItem?->damage?->mainstatus ?? '-',
            ];
        });
    }
}
