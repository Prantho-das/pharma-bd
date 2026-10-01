<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Generic;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PharmacyAiService
{
    /**
     * Tool Registry: List of available system tools for function calling
     */
    public function getAvailableTools(): array
    {
        return [
            [
                'name' => 'get_inventory_and_product_summary',
                'description' => 'Get pharmacy master inventory summary, total product count, active/inactive items, total physical stock pieces, stock valuation at cost and MRP, total batches, and catalog stats (categories, generics, manufacturers).',
                'parameters' => [],
            ],
            [
                'name' => 'get_sales_and_revenue',
                'description' => 'Fetch real-time sales transactions, gross revenue, collected cash, dues, and transaction volume for today, yesterday, this week, this month or custom dates.',
                'parameters' => [
                    'period' => ['type' => 'string', 'enum' => ['today', 'yesterday', 'this_week', 'this_month', 'last_month', 'all'], 'description' => 'Time window to aggregate'],
                ],
            ],
            [
                'name' => 'search_product_inventory',
                'description' => 'Search medicines or products by name, generic, SKU, or barcode to get live stock levels, batch details, unit MRP, purchase price, and shelf rack locations.',
                'parameters' => [
                    'query' => ['type' => 'string', 'description' => 'Name, generic, brand or SKU of medicine'],
                ],
            ],
            [
                'name' => 'get_expiring_batches',
                'description' => 'Identify medicine batches nearing expiry within 30, 60, 90, or 180 days with stock count, batch number, and supplier name.',
                'parameters' => [
                    'days' => ['type' => 'integer', 'default' => 60, 'description' => 'Days threshold for expiry check'],
                ],
            ],
            [
                'name' => 'get_low_stock_shortages',
                'description' => 'Get list of products whose stock is below the minimum reorder threshold.',
                'parameters' => [
                    'limit' => ['type' => 'integer', 'default' => 10],
                ],
            ],
            [
                'name' => 'get_customer_dues_and_ledger',
                'description' => 'Get customer receivable balances, top debtors, inactive customers with outstanding dues, and phone contacts.',
                'parameters' => [
                    'search' => ['type' => 'string', 'default' => ''],
                    'min_due' => ['type' => 'number', 'default' => 0],
                ],
            ],
            [
                'name' => 'get_supplier_bills_and_aging',
                'description' => 'Retrieve supplier accounts payables, aging buckets (0-30d, 31-60d, 61-90d, 90+d), and recent purchase orders.',
                'parameters' => [
                    'aging' => ['type' => 'string', 'enum' => ['all', '0-30', '31-60', '61-90', '90+'], 'default' => 'all'],
                ],
            ],
            [
                'name' => 'get_top_selling_medicines',
                'description' => 'Find the most popular and highest selling medications by sales volume and revenue.',
                'parameters' => [
                    'period' => ['type' => 'string', 'enum' => ['today', 'this_week', 'this_month', 'all'], 'default' => 'this_month'],
                    'limit' => ['type' => 'integer', 'default' => 6],
                ],
            ],
            [
                'name' => 'get_profit_and_loss_summary',
                'description' => 'Calculate revenue, estimated Cost of Goods Sold (COGS), gross profit, operational expenses, net profit, and profit margin percentage.',
                'parameters' => [
                    'period' => ['type' => 'string', 'enum' => ['today', 'this_week', 'this_month'], 'default' => 'this_month'],
                ],
            ],
            [
                'name' => 'get_expense_breakdown',
                'description' => 'Analyze pharmacy expenses grouped by expense heads (Rent, Electricity, Salaries, Transport, Misc).',
                'parameters' => [
                    'period' => ['type' => 'string', 'enum' => ['this_month', 'this_week', 'today'], 'default' => 'this_month'],
                ],
            ],
            [
                'name' => 'get_entity_counts_summary',
                'description' => 'Get overall entity counts including customers, suppliers, sales invoices, and purchase orders.',
                'parameters' => [],
            ],
        ];
    }

    /**
     * Tool Execution Router: Executes specific tool with accurate real database queries
     */
    public function executeTool(string $toolName, array $args = []): array
    {
        $startTime = microtime(true);
        $result = [];

        switch ($toolName) {
            case 'get_inventory_and_product_summary':
                $result = $this->toolGetInventoryAndProductSummary();
                break;

            case 'get_sales_and_revenue':
                $result = $this->toolGetSalesAndRevenue($args['period'] ?? 'today');
                break;

            case 'search_product_inventory':
                $result = $this->toolSearchProductInventory($args['query'] ?? '');
                break;

            case 'get_expiring_batches':
                $result = $this->toolGetExpiringBatches((int) ($args['days'] ?? 60));
                break;

            case 'get_low_stock_shortages':
                $result = $this->toolGetLowStockShortages((int) ($args['limit'] ?? 10));
                break;

            case 'get_customer_dues_and_ledger':
                $result = $this->toolGetCustomerDues($args['search'] ?? '', (float) ($args['min_due'] ?? 0));
                break;

            case 'get_supplier_bills_and_aging':
                $result = $this->toolGetSupplierBillsAndAging($args['aging'] ?? 'all');
                break;

            case 'get_top_selling_medicines':
                $result = $this->toolGetTopSellingMedicines($args['period'] ?? 'this_month', (int) ($args['limit'] ?? 6));
                break;

            case 'get_profit_and_loss_summary':
                $result = $this->toolGetProfitAndLoss($args['period'] ?? 'this_month');
                break;

            case 'get_expense_breakdown':
                $result = $this->toolGetExpenseBreakdown($args['period'] ?? 'this_month');
                break;

            case 'get_entity_counts_summary':
                $result = $this->toolGetEntityCountsSummary();
                break;

            default:
                $result = ['error' => "Unknown tool: {$toolName}"];
                break;
        }

        $durationMs = round((microtime(true) - $startTime) * 1000, 1);
        $result['_tool_meta'] = [
            'tool_name' => $toolName,
            'execution_time_ms' => $durationMs,
            'timestamp' => now()->toDateTimeString(),
        ];

        return $result;
    }

    // =========================================================================
    // SPECIFIC TOOL IMPLEMENTATIONS
    // =========================================================================

    protected function toolGetInventoryAndProductSummary(): array
    {
        $totalProducts = (int) Product::count();
        $activeProducts = (int) Product::where('status', 'active')->count();
        $inactiveProducts = (int) Product::where('status', '!=', 'active')->count();

        $batchesQuery = Batch::where('current_stock', '>', 0);
        $totalStockPieces = (int) $batchesQuery->sum('current_stock');
        $activeBatchesCount = (int) $batchesQuery->count();
        $stockValuationCost = (float) Batch::where('current_stock', '>', 0)->sum(DB::raw('current_stock * purchase_rate'));
        $stockValuationMrp = (float) Batch::where('current_stock', '>', 0)->sum(DB::raw('current_stock * mrp_rate'));

        $totalCategories = (int) Category::count();
        $totalGenerics = (int) Generic::count();
        $totalManufacturers = (int) Manufacturer::count();

        // Calculate low stock & out of stock products
        $allProducts = Product::where('status', 'active')
            ->withSum(['batches as current_stock' => function ($q) {
                $q->where('current_stock', '>', 0);
            }], 'current_stock')
            ->get();

        $lowStockCount = $allProducts->filter(fn ($p) => ($p->current_stock ?? 0) <= $p->min_stock_alert && ($p->current_stock ?? 0) > 0)->count();
        $outOfStockCount = $allProducts->filter(fn ($p) => ($p->current_stock ?? 0) <= 0)->count();

        return [
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'inactive_products' => $inactiveProducts,
            'total_stock_pieces' => $totalStockPieces,
            'active_batches_count' => $activeBatchesCount,
            'stock_valuation_cost_bdt' => round($stockValuationCost, 2),
            'stock_valuation_mrp_bdt' => round($stockValuationMrp, 2),
            'potential_retail_margin_bdt' => round(max(0, $stockValuationMrp - $stockValuationCost), 2),
            'total_categories' => $totalCategories,
            'total_generics' => $totalGenerics,
            'total_manufacturers' => $totalManufacturers,
            'low_stock_items_count' => $lowStockCount,
            'out_of_stock_items_count' => $outOfStockCount,
            'currency' => 'BDT (৳)',
        ];
    }

    protected function toolGetEntityCountsSummary(): array
    {
        $totalCustomers = (int) Customer::count();
        $totalCustomerDue = (float) Customer::sum('current_due');
        $totalSuppliers = (int) Supplier::count();
        $totalSupplierDue = (float) Purchase::where('payment_status', '!=', 'PAID')->sum('due_amount');
        $totalSales = (int) Sale::where('status', '!=', 'CANCELLED')->count();
        $totalPOs = (int) PurchaseOrder::count();

        return [
            'total_customers' => $totalCustomers,
            'total_customer_due_bdt' => round($totalCustomerDue, 2),
            'total_suppliers' => $totalSuppliers,
            'total_supplier_due_bdt' => round($totalSupplierDue, 2),
            'total_sales_invoices' => $totalSales,
            'total_purchase_orders' => $totalPOs,
        ];
    }

    protected function toolGetSalesAndRevenue(string $period): array
    {
        $query = Sale::where('status', '!=', 'CANCELLED');
        $now = Carbon::now();
        $label = 'Today';

        if ($period === 'yesterday') {
            $query->whereDate('sale_date', $now->copy()->subDay()->toDateString());
            $label = 'Yesterday';
        } elseif ($period === 'this_week') {
            $query->where('sale_date', '>=', $now->copy()->startOfWeek()->toDateTimeString());
            $label = 'This Week';
        } elseif ($period === 'this_month') {
            $query->where('sale_date', '>=', $now->copy()->startOfMonth()->toDateTimeString());
            $label = 'This Month';
        } elseif ($period === 'last_month') {
            $query->whereBetween('sale_date', [
                $now->copy()->subMonth()->startOfMonth()->toDateTimeString(),
                $now->copy()->subMonth()->endOfMonth()->toDateTimeString(),
            ]);
            $label = 'Last Month';
        } else {
            // Default Today
            $query->whereDate('sale_date', $now->toDateString());
            $label = 'Today';
        }

        $salesCount = (int) $query->count();
        $grossRevenue = (float) $query->sum('grand_total');
        $collectedPaid = (float) $query->sum('paid_amount');
        $dueAmount = (float) $query->sum('due_amount');
        $discountAmount = (float) $query->sum('discount_amount');
        $avgTicket = $salesCount > 0 ? round($grossRevenue / $salesCount, 2) : 0;

        return [
            'period' => $label,
            'total_sales_invoices' => $salesCount,
            'gross_revenue' => $grossRevenue,
            'collected_paid' => $collectedPaid,
            'due_amount' => $dueAmount,
            'discounts_given' => $discountAmount,
            'average_order_value' => $avgTicket,
            'currency' => 'BDT (৳)',
        ];
    }

    protected function toolSearchProductInventory(string $query): array
    {
        $q = trim($query);
        if (empty($q)) {
            return ['products' => []];
        }

        $products = Product::where(function ($sub) use ($q) {
            $sub->where('name', 'LIKE', "%{$q}%")
                ->orWhere('sku', 'LIKE', "%{$q}%")
                ->orWhere('barcode', 'LIKE', "%{$q}%")
                ->orWhereHas('generic', fn ($g) => $g->where('name', 'LIKE', "%{$q}%"));
        })
        ->with(['batches' => function ($b) {
            $b->where('current_stock', '>', 0)->orderBy('expiry_date', 'asc');
        }, 'category:id,name', 'baseUnit:id,name', 'medicineType:id,name', 'manufacturer:id,name', 'generic:id,name'])
        ->limit(6)
        ->get();

        $list = [];
        foreach ($products as $p) {
            $totalStock = (int) $p->batches->sum('current_stock');
            $latestBatch = $p->batches->first();
            $mrp = $latestBatch ? (float) $latestBatch->mrp_rate : 0.0;
            $purchasePrice = $latestBatch ? (float) $latestBatch->purchase_rate : 0.0;

            $batches = $p->batches->map(fn ($b) => [
                'batch_no' => $b->batch_number,
                'stock' => (int) $b->current_stock,
                'mrp' => (float) $b->mrp_rate,
                'cost' => (float) $b->purchase_rate,
                'expiry_date' => $b->expiry_date?->format('Y-m-d') ?? 'N/A',
                'is_expired' => $b->expiry_date && $b->expiry_date->isPast(),
            ])->toArray();

            $list[] = [
                'id' => $p->id,
                'name' => $p->name,
                'strength' => $p->strength ?? '',
                'generic_name' => $p->generic?->name ?? 'N/A',
                'company' => $p->manufacturer?->name ?? 'N/A',
                'dosage_form' => $p->medicineType?->name ?? 'N/A',
                'unit' => $p->baseUnit?->name ?? 'Pcs',
                'total_stock' => $totalStock,
                'min_stock_alert' => $p->min_stock_alert,
                'is_low_stock' => $totalStock <= $p->min_stock_alert,
                'mrp' => $mrp,
                'purchase_price' => $purchasePrice,
                'batches' => $batches,
            ];
        }

        return [
            'query' => $q,
            'match_count' => count($list),
            'results' => $list,
        ];
    }

    protected function toolGetExpiringBatches(int $days = 60): array
    {
        $now = Carbon::now();
        $targetDate = $now->copy()->addDays($days)->toDateString();

        $batches = Batch::with(['product:id,name,strength,generic_id', 'product.generic:id,name', 'purchaseItem.purchase.supplier:id,name,phone'])
            ->where('current_stock', '>', 0)
            ->where('expiry_date', '>=', $now->toDateString())
            ->where('expiry_date', '<=', $targetDate)
            ->orderBy('expiry_date', 'asc')
            ->limit(10)
            ->get();

        $list = $batches->map(fn ($b) => [
            'medicine' => ($b->product?->name ?? 'Unknown') . ($b->product?->strength ? ' ' . $b->product->strength : ''),
            'batch_number' => $b->batch_number,
            'stock_remaining' => (int) $b->current_stock,
            'loss_risk_value' => (float) ($b->current_stock * $b->purchase_rate),
            'expiry_date' => $b->expiry_date?->format('d M, Y') ?? 'N/A',
            'days_left' => $b->expiry_date ? $now->diffInDays($b->expiry_date, false) : 0,
            'supplier' => $b->purchaseItem?->purchase?->supplier?->name ?? 'Direct Stock',
        ])->toArray();

        $totalRiskValue = array_sum(array_column($list, 'loss_risk_value'));

        return [
            'threshold_days' => $days,
            'expiring_batches_count' => count($list),
            'total_financial_risk_bdt' => round($totalRiskValue, 2),
            'items' => $list,
        ];
    }

    protected function toolGetLowStockShortages(int $limit = 10): array
    {
        $products = Product::where('status', 'active')
            ->with(['manufacturer:id,name', 'generic:id,name'])
            ->withSum(['batches as current_stock' => function ($q) {
                $q->where('current_stock', '>', 0);
            }], 'current_stock')
            ->get()
            ->filter(fn ($p) => ($p->current_stock ?? 0) <= $p->min_stock_alert)
            ->sortBy('current_stock')
            ->take($limit)
            ->values();

        $list = $products->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name . ($p->strength ? ' ' . $p->strength : ''),
            'generic_name' => $p->generic?->name ?? 'N/A',
            'manufacturer' => $p->manufacturer?->name ?? 'N/A',
            'current_stock' => (int) ($p->current_stock ?? 0),
            'min_stock_alert' => (int) $p->min_stock_alert,
            'shortage_units' => max(0, (int) $p->min_stock_alert - (int) ($p->current_stock ?? 0)),
            'suggested_reorder_qty' => max(10, (int) $p->min_stock_alert * 2),
        ])->toArray();

        return [
            'total_shortage_items' => count($list),
            'items' => $list,
        ];
    }

    protected function toolGetCustomerDues(string $search = '', float $minDue = 0): array
    {
        $query = Customer::where('current_due', '>', $minDue);

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $totalCustomerDue = (float) Customer::sum('current_due');
        $customers = $query->orderByDesc('current_due')->limit(8)->get();

        $list = $customers->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'phone' => $c->phone ?: 'N/A',
            'current_due' => (float) $c->current_due,
            'credit_limit' => (float) $c->credit_limit,
        ])->toArray();

        return [
            'total_pharmacy_receivables_bdt' => $totalCustomerDue,
            'top_debtors' => $list,
        ];
    }

    protected function toolGetSupplierBillsAndAging(string $aging = 'all'): array
    {
        $purchases = Purchase::with('supplier:id,name,phone')
            ->where('payment_status', '!=', 'PAID')
            ->orderBy('invoice_date', 'asc')
            ->limit(10)
            ->get();

        $totalSupplierDue = (float) Purchase::where('payment_status', '!=', 'PAID')->sum('due_amount');

        $list = $purchases->map(fn ($po) => [
            'po_number' => $po->purchase_no,
            'supplier_name' => $po->supplier?->name ?? 'General Supplier',
            'supplier_phone' => $po->supplier?->phone ?? 'N/A',
            'invoice_date' => $po->invoice_date?->format('d M, Y') ?? 'N/A',
            'grand_total' => (float) $po->grand_total,
            'paid_amount' => (float) $po->paid_amount,
            'due_amount' => (float) $po->due_amount,
            'aging_days' => $po->aging_days,
            'aging_bucket' => $po->aging_bucket,
        ])->toArray();

        return [
            'total_supplier_payable_bdt' => $totalSupplierDue,
            'unpaid_invoices_count' => count($list),
            'invoices' => $list,
        ];
    }

    protected function toolGetTopSellingMedicines(string $period = 'this_month', int $limit = 6): array
    {
        $now = Carbon::now();
        $startDate = $now->copy()->startOfMonth()->toDateTimeString();

        if ($period === 'today') {
            $startDate = $now->copy()->startOfDay()->toDateTimeString();
        } elseif ($period === 'this_week') {
            $startDate = $now->copy()->startOfWeek()->toDateTimeString();
        }

        $topItems = SaleItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total_amount) as total_sales'))
            ->whereHas('sale', function ($q) use ($startDate) {
                $q->where('sale_date', '>=', $startDate)->where('status', '!=', 'CANCELLED');
            })
            ->with(['product:id,name,strength,generic_id', 'product.generic:id,name'])
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();

        $list = $topItems->map(fn ($item) => [
            'product_name' => ($item->product?->name ?? 'Unknown') . ($item->product?->strength ? ' ' . $item->product->strength : ''),
            'generic' => $item->product?->generic?->name ?? 'N/A',
            'units_sold' => (int) $item->total_qty,
            'total_revenue_bdt' => (float) $item->total_sales,
        ])->toArray();

        return [
            'period' => $period,
            'top_medicines' => $list,
        ];
    }

    protected function toolGetProfitAndLoss(string $period = 'this_month'): array
    {
        $now = Carbon::now();
        $startDate = $now->copy()->startOfMonth()->toDateTimeString();
        $label = 'This Month';

        if ($period === 'today') {
            $startDate = $now->copy()->startOfDay()->toDateTimeString();
            $label = 'Today';
        } elseif ($period === 'this_week') {
            $startDate = $now->copy()->startOfWeek()->toDateTimeString();
            $label = 'This Week';
        }

        $revenue = (float) Sale::where('sale_date', '>=', $startDate)->where('status', '!=', 'CANCELLED')->sum('grand_total');

        // Exact COGS from sale_items batch unit_cost
        $cogs = (float) SaleItem::whereHas('sale', function ($q) use ($startDate) {
            $q->where('sale_date', '>=', $startDate)->where('status', '!=', 'CANCELLED');
        })->sum(DB::raw('quantity * unit_cost'));

        if ($cogs <= 0 && $revenue > 0) {
            $cogs = $revenue * 0.82; // Fallback to 82% standard pharma purchase cost
        }

        $grossProfit = max(0, $revenue - $cogs);
        $expenses = (float) Expense::where('expense_date', '>=', $startDate)->sum('amount');
        $netProfit = $grossProfit - $expenses;
        $profitMargin = $revenue > 0 ? round(($netProfit / $revenue) * 100, 2) : 0;

        return [
            'period' => $label,
            'total_revenue' => $revenue,
            'cost_of_goods_sold' => round($cogs, 2),
            'gross_profit' => round($grossProfit, 2),
            'total_expenses' => round($expenses, 2),
            'net_profit' => round($netProfit, 2),
            'net_profit_margin_percent' => $profitMargin,
            'currency' => 'BDT (৳)',
        ];
    }

    protected function toolGetExpenseBreakdown(string $period = 'this_month'): array
    {
        $now = Carbon::now();
        $startDate = $now->copy()->startOfMonth()->toDateTimeString();

        $expenses = Expense::select('category_id', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->where('expense_date', '>=', $startDate)
            ->with('category:id,name')
            ->groupBy('category_id')
            ->orderByDesc('total_amount')
            ->get();

        $list = $expenses->map(fn ($e) => [
            'category' => $e->category?->name ?? 'General Head',
            'amount' => (float) $e->total_amount,
            'entry_count' => (int) $e->count,
        ])->toArray();

        $total = array_sum(array_column($list, 'amount'));

        return [
            'period' => $period,
            'total_expenses_bdt' => $total,
            'breakdown' => $list,
        ];
    }

    // =========================================================================
    // INTENT DETECTION & SIDEKICK TOOL-BASED RESPONSE ORCHESTRATION
    // =========================================================================

    /**
     * Main Ask entry point: Uses tool calling & live database evaluation
     */
    public function ask(string $prompt, array $history = []): array
    {
        $provider = Setting::get('ai_provider', 'local');
        $apiKey = Setting::get('ai_api_key', env('AI_API_KEY', ''));
        $model = Setting::get('ai_model', 'gemini-1.5-flash');

        // 1. Identify best tool based on user intent
        $toolExecution = $this->detectAndExecuteTool($prompt);
        $toolName = $toolExecution['tool'];
        $toolData = $toolExecution['data'];

        // 2. If Cloud LLM configured (Gemini / OpenAI / Groq / DeepSeek), synthesize answers with the live tool results
        if (! empty($apiKey) && $provider !== 'local') {
            try {
                $llmResponse = null;
                if ($provider === 'gemini') {
                    $geminiModel = $model ?: 'gemini-2.5-flash';
                    if (str_contains($geminiModel, 'gemini-1.') || str_contains($geminiModel, 'gemini-pro')) {
                        $geminiModel = 'gemini-2.5-flash';
                    }
                    $llmResponse = $this->callGeminiWithToolResult($prompt, $toolName, $toolData, $history, $apiKey, $geminiModel);
                } elseif (in_array($provider, ['openai', 'groq', 'deepseek'])) {
                    $endpoint = match ($provider) {
                        'groq' => 'https://api.groq.com/openai/v1/chat/completions',
                        'deepseek' => 'https://api.deepseek.com/chat/completions',
                        default => 'https://api.openai.com/v1/chat/completions',
                    };
                    $defaultModel = match ($provider) {
                        'groq' => 'llama-3.3-70b-versatile',
                        'deepseek' => 'deepseek-chat',
                        default => 'gpt-4o-mini',
                    };
                    $llmResponse = $this->callOpenAiCompatibleWithToolResult($endpoint, $prompt, $toolName, $toolData, $history, $apiKey, $model ?: $defaultModel);
                }

                if ($llmResponse) {
                    return [
                        'text' => $llmResponse,
                        'tool_used' => $toolName,
                        'tool_data' => $toolData,
                        'execution_time' => $toolData['_tool_meta']['execution_time_ms'] ?? 0,
                    ];
                }
            } catch (\Throwable $e) {
                Log::error('Cloud AI Tool Synthesis Failed: ' . $e->getMessage());
            }
        }

        // 3. High-Accuracy Sidekick Smart Formatter (Zero-latency Local Engine)
        $formattedText = $this->formatSidekickAnswer($prompt, $toolName, $toolData);

        return [
            'text' => $formattedText,
            'tool_used' => $toolName,
            'tool_data' => $toolData,
            'execution_time' => $toolData['_tool_meta']['execution_time_ms'] ?? 0,
        ];
    }

    /**
     * Fast Intent Detection to pick and execute the right tool
     */
    protected function detectAndExecuteTool(string $prompt): array
    {
        $q = strtolower(trim($prompt));

        // 1. Total Products / Inventory Summary / Stock Valuation / Product Counts
        if (
            preg_match('/(total.*product|koita.*product|product.*koita|koyta.*product|product.*koyta|koyti.*product|product.*koyti|how many.*product|total.*medicine|koita.*medicine|medicine.*koita|koyta.*medicine|how many.*medicine|total.*item|koita.*item|koyta.*item|how many.*item|mot.*product|mot.*osudh|mot.*medicine|mot.*item|মোট.*ওষুধ|কয়টা.*ওষুধ|কয়টি.*ওষুধ|কতটি.*ওষুধ|কতগুলো.*ওষুধ|মোট.*প্রোডাক্ট|কয়টা.*প্রোডাক্ট|কয়টি.*পণ্য|মোট.*পণ্য|স্টক.*মূল্য|ইনভেন্টরি|inventory.*summary|total.*inventory|stock.*valuation|total.*stock.*value|amar.*koita.*total|total.*koita|koita.*total|amar.*total|total.*ache|total.*ase|total.*koyta|koto.*item|koita.*item)/iu', $q)
            || (preg_match('/(product|medicine|item|osudh|ওষুধ|পণ্য|প্রোডাক্ট)/iu', $q) && preg_match('/(koita|koyta|koyti|koto|how many|count|mot|মোট|কয়টা|কয়টি|কতটি|কতগুলো|সংখ্যা|আছে|ase|ache)/iu', $q))
            || (preg_match('/(inventory|stock|মজুত|স্টক)/iu', $q) && preg_match('/(summary|value|valuation|total|mot|মোট|মূল্য|কত|সারসংক্ষেপ)/iu', $q) && !preg_match('/(low stock|alert|napa|ace|seclo|sergel|beximco|square)/iu', $q))
        ) {
            $tool = 'get_inventory_and_product_summary';
            $data = $this->executeTool($tool);
            return ['tool' => $tool, 'data' => $data];
        }

        // 2. Entity / Customer / Supplier / PO Counts
        if (preg_match('/(customer.*koita|koita.*customer|koyta.*customer|how many.*customer|mot.*customer|মোট.*কাস্টমার|কয়জন.*কাস্টমার|সাপ্লায়ার.*কয়টা|how many.*supplier|koita.*supplier|business.*summary|entity.*count)/iu', $q)) {
            $tool = 'get_entity_counts_summary';
            $data = $this->executeTool($tool);
            return ['tool' => $tool, 'data' => $data];
        }

        // 3. Top Selling / Best Selling
        if (preg_match('/(top|best selling|best|popular|highest|most sell|beshi sell|শীর্ষ|বেশি বিক্রি|জনপ্রিয়|বেস্ট)/iu', $q) && !preg_match('/(profit|expense|loss|লাভ|খরচ|ক্ষতি)/iu', $q)) {
            $period = 'this_month';
            if (preg_match('/today|ajk|আজ|আজকে/iu', $q)) $period = 'today';
            elseif (preg_match('/week|shoptaho|সপ্তাহ/iu', $q)) $period = 'this_week';

            $tool = 'get_top_selling_medicines';
            $data = $this->executeTool($tool, ['period' => $period, 'limit' => 6]);
            return ['tool' => $tool, 'data' => $data];
        }

        // 4. Expiry / Damage
        if (preg_match('/(expir|damage|meyad|meyadottirno|overdue batch|date sesh|near expiry|মেয়াদ|মেয়াদোত্তীর্ণ|মেয়াদ শেষ|ডেট শেষ|নষ্ট|ক্ষতি)/iu', $q)) {
            $days = 60;
            if (preg_match('/30|৩০/iu', $q)) $days = 30;
            elseif (preg_match('/90|৯০/iu', $q)) $days = 90;
            elseif (preg_match('/180|১৮০|6 month|half year|৬ মাস/iu', $q)) $days = 180;

            $tool = 'get_expiring_batches';
            $data = $this->executeTool($tool, ['days' => $days]);
            return ['tool' => $tool, 'data' => $data];
        }

        // 5. Low Stock / Shortage / Reorder
        if (preg_match('/(low stock|shortage|reorder|kom stock|shesh|sesh|alert qty|ঘাটতি|কম স্টক|স্টক কম|স্টক শেষ|মজুত কম|অ্যালার্ট)/iu', $q)) {
            $tool = 'get_low_stock_shortages';
            $data = $this->executeTool($tool, ['limit' => 8]);
            return ['tool' => $tool, 'data' => $data];
        }

        // 6. Profit & Loss / Margins
        if (preg_match('/(profit|loss|margin|lav|khoti|net profit|gross profit|লাভ|ক্ষতি|মার্জিন|নিট লাভ|গ্রস প্রফিট)/iu', $q)) {
            $period = 'this_month';
            if (preg_match('/today|ajk|আজ|আজকে/iu', $q)) $period = 'today';
            elseif (preg_match('/week|সপ্তাহ/iu', $q)) $period = 'this_week';

            $tool = 'get_profit_and_loss_summary';
            $data = $this->executeTool($tool, ['period' => $period]);
            return ['tool' => $tool, 'data' => $data];
        }

        // 7. Customer Due / Debtor / Receivable
        if (preg_match('/(customer due|customer|baki|debtor|receivable|ledger|patient|inactive customer|visit|বাকি|কাস্টমার|গ্রাহক|পাওনা|লেজার)/iu', $q) && !preg_match('/(supplier|সাপ্লায়ার|সরবরাহকারী)/iu', $q)) {
            $tool = 'get_customer_dues_and_ledger';
            $data = $this->executeTool($tool);
            return ['tool' => $tool, 'data' => $data];
        }

        // 8. Supplier Due / Purchase Aging
        if (preg_match('/(supplier|purchase|aging|po bill|distributor|company due|সাপ্লায়ার|সরবরাহকারী|দেনা|প্রদেয়|সাপ্লায়ার)/iu', $q) && !preg_match('/(customer|গ্রাহক)/iu', $q)) {
            $tool = 'get_supplier_bills_and_aging';
            $data = $this->executeTool($tool);
            return ['tool' => $tool, 'data' => $data];
        }

        // 9. Expenses
        if (preg_match('/(expense|khoroch|cost|petty cash|rent|salary|bills|খরচ|ব্যয়|ভাড়া|বেতন)/iu', $q) && !preg_match('/(sales|revenue|income|বিক্রি|আয়)/iu', $q)) {
            $tool = 'get_expense_breakdown';
            $data = $this->executeTool($tool, ['period' => 'this_month']);
            return ['tool' => $tool, 'data' => $data];
        }

        // 10. Specific Product / Medicine Stock & Batch Search
        if (preg_match('/(stock|price|mrp|search|napa|ace|seclo|sergel|beximco|square|koto|dam|ase|ache|দাম|মূল্য|খুঁজুন|ওষুধ|স্টক)/iu', $q) && !preg_match('/(revenue|today|yesterday|sales|আজকের বিক্রি|মোট বিক্রি)/iu', $q)) {
            $cleaned = preg_replace('/\b(check|search|stock of|price of|find|koto|dam|ase|ki|ache|kina|info|product|medicine|item|amar|total|koita|koyta|koyti|mot|stock|price|rate|mrp)\b/iu', '', $q);
            $cleaned = preg_replace('/(দাম|মূল্য|খুঁজুন|ওষুধ|স্টক|মোট|কয়টা|কয়টি|কত|আছে|কিনা)/u', '', $cleaned);
            $cleaned = preg_replace('/\s+/', ' ', trim($cleaned));

            if (empty($cleaned) || strlen($cleaned) < 2) {
                $tool = 'get_inventory_and_product_summary';
                $data = $this->executeTool($tool);
                return ['tool' => $tool, 'data' => $data];
            }

            $tool = 'search_product_inventory';
            $data = $this->executeTool($tool, ['query' => $cleaned]);
            return ['tool' => $tool, 'data' => $data];
        }

        // Default: Sales and Revenue
        $period = 'today';
        if (preg_match('/yesterday|kal|গতকাল/iu', $q)) $period = 'yesterday';
        elseif (preg_match('/week|shoptaho|সপ্তাহ/iu', $q)) $period = 'this_week';
        elseif (preg_match('/month|mash|মাস/iu', $q)) $period = 'this_month';

        $tool = 'get_sales_and_revenue';
        $data = $this->executeTool($tool, ['period' => $period]);
        return ['tool' => $tool, 'data' => $data];
    }

    /**
     * Gemini Tool Call Synthesis
     */
    protected function callGeminiWithToolResult(string $prompt, string $toolName, array $toolData, array $history, string $apiKey, string $model): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
        $systemPrompt = "You are Pharmacy Sidekick, an elite AI co-pilot for a retail pharmacy enterprise.
You have directly called the system tool: `{$toolName}`.
Live Tool Results:
" . json_encode($toolData, JSON_PRETTY_PRINT) . "

Instructions:
1. Answer the user prompt directly, concisely, and with extreme accuracy using the tool's live data.
2. Structure your answer using Markdown with bold stats, neat tables or bullet points, and highlight business opportunities.
3. If the user asks in Bengali, reply in Bengali. If in English, reply in English.";

        $contents = [];
        foreach ($history as $msg) {
            $role = ($msg['sender'] === 'user') ? 'user' : 'model';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $msg['text']]],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $prompt]],
        ];

        $response = Http::timeout(15)->post($url, [
            'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => (float) Setting::get('ai_temperature', 0.4),
                'maxOutputTokens' => 1024,
            ],
        ]);

        if ($response->successful()) {
            return $response->json('candidates.0.content.parts.0.text') ?? '';
        }

        throw new \Exception('Gemini API Error: ' . $response->body());
    }

    /**
     * OpenAI / Groq / DeepSeek Tool Call Synthesis
     */
    protected function callOpenAiCompatibleWithToolResult(string $endpoint, string $prompt, string $toolName, array $toolData, array $history, string $apiKey, string $model): string
    {
        $systemPrompt = "You are Pharmacy Sidekick, an elite AI co-pilot for a retail pharmacy enterprise.
You have directly executed the tool: `{$toolName}`.
Live Tool Output:
" . json_encode($toolData, JSON_PRETTY_PRINT) . "

Instructions:
- Provide high precision, beautifully structured insights based on the live tool results.
- Use bold numbers, currency symbols (৳), structured bullet points or tables.
- Respond in the user's language (Bengali or English).";

        $messages = [];
        $messages[] = ['role' => 'system', 'content' => $systemPrompt];

        foreach ($history as $msg) {
            $role = ($msg['sender'] === 'user') ? 'user' : 'assistant';
            $messages[] = ['role' => $role, 'content' => $msg['text']];
        }

        $messages[] = ['role' => 'user', 'content' => $prompt];

        $response = Http::timeout(15)->withToken($apiKey)->post($endpoint, [
            'model' => $model,
            'messages' => $messages,
            'temperature' => (float) Setting::get('ai_temperature', 0.4),
            'max_tokens' => 1024,
        ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content') ?? '';
        }

        throw new \Exception('API Error: ' . $response->body());
    }

    /**
     * High-Precision Sidekick Native Markdown Generator
     */
    protected function formatSidekickAnswer(string $prompt, string $toolName, array $data): string
    {
        $isBn = preg_match('/[\x{0980}-\x{09FF}]/u', $prompt) || preg_match('/(koita|koyta|koyti|koto|ase|ache|dam|baki|khoroch|lav|meyad|mot)/iu', $prompt);

        switch ($toolName) {
            case 'get_inventory_and_product_summary':
                $prods = number_format($data['total_products']);
                $activeProds = number_format($data['active_products']);
                $pieces = number_format($data['total_stock_pieces']);
                $batches = number_format($data['active_batches_count']);
                $costVal = number_format($data['stock_valuation_cost_bdt'], 2);
                $mrpVal = number_format($data['stock_valuation_mrp_bdt'], 2);
                $marginVal = number_format($data['potential_retail_margin_bdt'], 2);
                $lowStock = number_format($data['low_stock_items_count']);
                $outOfStock = number_format($data['out_of_stock_items_count']);
                $cats = $data['total_categories'];
                $gens = $data['total_generics'];
                $mans = $data['total_manufacturers'];

                if ($isBn) {
                    return "### 📦 ফার্মেসি ইনভেন্টরি ও প্রোডাক্ট সারসংক্ষেপ\n\n" .
                           "• **মোট নিবন্ধিত প্রোডাক্ট/ওষুধ:** **{$prods}টি** (সক্রিয়: {$activeProds}টি)\n" .
                           "• **মোট ফিজিক্যাল স্টক:** **{$pieces} পিস / ইউনিট** ({$batches}টি সক্রিয় ব্যাচে)\n" .
                           "• **স্টক ক্রয়মূল্য (Cost Valuation):** ৳{$costVal}\n" .
                           "• **স্টক বিক্রয়মূল্য (MRP Valuation):** ৳{$mrpVal}\n" .
                           "• **সম্ভাব্য নিট রিটেইল লাভ:** ৳{$marginVal}\n" .
                           "• **স্বল্প স্টক সতর্কতা (Low Stock):** `{$lowStock}টি আইটেম`\n" .
                           "• **স্টক শেষ (Out of Stock):** `{$outOfStock}টি আইটেম`\n" .
                           "• **ক্যাটালগ পরিসংখ্যান:** {$cats}টি ক্যাটাগরি, {$gens}টি জেনেরিক, {$mans}টি প্রস্তুতকারক কোম্পানি।\n\n" .
                           "> 💡 **পরামর্শ:** কোনো নির্দিষ্ট ওষুধের ব্যাচ বা বর্তমান রেট জানতে ওষুধের নাম দিয়ে সার্চ করুন (যেমন: *Napa Extra* বা *Maxpro 20*)।";
                }

                return "### 📦 Pharmacy Inventory & Product Master Summary\n\n" .
                       "• **Total Registered Products:** **{$prods} items** (Active: {$activeProds})\n" .
                       "• **Total Physical Stock on Hand:** **{$pieces} units/pcs** across {$batches} active batches\n" .
                       "• **Stock Valuation (At Cost):** ৳{$costVal}\n" .
                       "• **Stock Valuation (At MRP):** ৳{$mrpVal}\n" .
                       "• **Potential Gross Margin:** ৳{$marginVal}\n" .
                       "• **Low Stock Warnings:** `{$lowStock} items`\n" .
                       "• **Out of Stock Items:** `{$outOfStock} items`\n" .
                       "• **Catalog Footprint:** {$cats} categories, {$gens} generics, {$mans} pharmaceutical companies.\n\n" .
                       "> 💡 **Pro-tip:** Search any specific brand or generic (e.g. *Napa Extra* or *Seclo 20*) for instant batch & rack breakdown.";

            case 'get_entity_counts_summary':
                $custs = number_format($data['total_customers']);
                $custDue = number_format($data['total_customer_due_bdt'], 2);
                $supps = number_format($data['total_suppliers']);
                $suppDue = number_format($data['total_supplier_due_bdt'], 2);
                $sales = number_format($data['total_sales_invoices']);
                $pos = number_format($data['total_purchase_orders']);

                if ($isBn) {
                    return "### 🏢 ব্যবসায়িক পার্টনার ও কার্যক্রম সারসংক্ষেপ\n\n" .
                           "• **মোট নিবন্ধিত গ্রাহক (Customers):** **{$custs} জন** (মোট বকেয়া পাওনা: ৳{$custDue})\n" .
                           "• **মোট সরবরাহকারী প্রতিষ্ঠান (Suppliers):** **{$supps} টি** (মোট প্রদেয় দেনা: ৳{$suppDue})\n" .
                           "• **সম্পন্ন বিক্রয় ইনভয়েস সংখ্যা:** {$sales} টি\n" .
                           "• **মোট পারচেজ অর্ডার (PO):** {$pos} টি\n";
                }

                return "### 🏢 Business Entities & Operations Summary\n\n" .
                       "• **Total Customers:** **{$custs}** (Receivable Due: ৳{$custDue})\n" .
                       "• **Total Suppliers:** **{$supps}** (Payable Due: ৳{$suppDue})\n" .
                       "• **Total Sales Invoices:** {$sales}\n" .
                       "• **Total Purchase Orders:** {$pos}\n";

            case 'get_sales_and_revenue':
                $period = $data['period'];
                $rev = number_format($data['gross_revenue'], 2);
                $paid = number_format($data['collected_paid'], 2);
                $due = number_format($data['due_amount'], 2);
                $count = $data['total_sales_invoices'];
                $avg = number_format($data['average_order_value'], 2);

                if ($isBn) {
                    return "### 📊 বিক্রয় ও রাজস্ব সারসংক্ষেপ ({$period})\n\n" .
                           "• **মোট বিক্রয় রাজস্ব:** ৳{$rev}\n" .
                           "• **নগদ সংগ্রহ (Paid):** ৳{$paid}\n" .
                           "• **বকেয়া (Due):** ৳{$due}\n" .
                           "• **মোট ইনভয়েস সংখ্যা:** {$count}টি\n" .
                           "• **গড় ইনভয়েস মূল্য (AOV):** ৳{$avg}\n\n" .
                           "> 💡 **পরামর্শ:** পিওএস টার্মিনালে নতুন দ্রুত বিক্রয়ের জন্য `Alt+P` ব্যবহার করুন।";
                }

                return "### 📊 Sales & Revenue Summary ({$period})\n\n" .
                       "• **Gross Revenue:** ৳{$rev}\n" .
                       "• **Cash Collected:** ৳{$paid}\n" .
                       "• **Receivable Due:** ৳{$due}\n" .
                       "• **Completed Invoices:** {$count}\n" .
                       "• **Average Order Value (AOV):** ৳{$avg}\n\n" .
                       "> 💡 **Pro-tip:** Press `Alt+P` to quickly launch the POS Terminal for new sales.";

            case 'search_product_inventory':
                if (empty($data['results'])) {
                    return $isBn 
                        ? "⚠️ **'{$data['query']}'** দিয়ে কোনো ওষুধ পাওয়া যায়নি। অনুগ্রহ করে সঠিক ব্র্যান্ড বা জেনেরিক নাম লিখুন।"
                        : "⚠️ No medications found matching **'{$data['query']}'**. Please verify the brand or generic name.";
                }

                $out = $isBn 
                    ? "### 💊 ওষুধ ও ইনভেন্টরি অনুসন্ধান ফলাফল ({$data['match_count']}টি পাওয়া গেছে):\n\n"
                    : "### 💊 Medication Inventory Search Results ({$data['match_count']} matched):\n\n";

                foreach ($data['results'] as $p) {
                    $stockBadge = $p['is_low_stock'] ? "⚠️ Low Stock" : "🟢 In Stock";
                    $out .= "#### **{$p['name']} {$p['strength']}** ({$p['company']})\n";
                    $out .= "- **Generic:** `{$p['generic_name']}` | **Type:** {$p['dosage_form']}\n";
                    $out .= "- **Stock:** **{$p['total_stock']} {$p['unit']}** ({$stockBadge})\n";
                    $out .= "- **MRP:** ৳" . number_format($p['mrp'], 2) . " | **Buy Rate:** ৳" . number_format($p['purchase_price'], 2) . "\n";
                    
                    if (!empty($p['batches'])) {
                        $out .= "- **Active Batches:**\n";
                        foreach ($p['batches'] as $b) {
                            $expStatus = $b['is_expired'] ? "🔴 EXPIRED" : "Exp: {$b['expiry_date']}";
                            $out .= "  • `Batch {$b['batch_no']}`: **{$b['stock']} pcs** ({$expStatus})\n";
                        }
                    }
                    $out .= "\n";
                }
                return $out;

            case 'get_expiring_batches':
                $days = $data['threshold_days'];
                $count = $data['expiring_batches_count'];
                $risk = number_format($data['total_financial_risk_bdt'], 2);

                if ($count === 0) {
                    return $isBn 
                        ? "✅ চমৎকার! আগামী {$days} দিনের মধ্যে মেয়াদোত্তীর্ণ হওয়ার মতো কোনো ব্যাচ নেই।"
                        : "✅ Excellent! No medicine batches are expiring in the next {$days} days.";
                }

                $out = $isBn
                    ? "### ⏳ মেয়াদ সতর্কবার্তা (আগামী {$days} দিনের মধ্যে)\n\n" .
                      "**মোট ঝুঁকিপূর্ণ ব্যাচ:** {$count}টি | **মোট সম্ভাব্য আর্থিক ক্ষতি:** ৳{$risk}\n\n" .
                      "| ওষুধ | ব্যাচ | মজুত | মেয়াদ | সাপ্লায়ার |\n" .
                      "| :--- | :--- | :--- | :--- | :--- |\n"
                    : "### ⏳ Expiry Alerts (Next {$days} Days)\n\n" .
                      "**Batches at Risk:** {$count} | **Total Financial Value:** ৳{$risk}\n\n" .
                      "| Medicine | Batch | Stock | Expiry | Supplier |\n" .
                      "| :--- | :--- | :--- | :--- | :--- |\n";

                foreach ($data['items'] as $item) {
                    $out .= "| {$item['medicine']} | `{$item['batch_number']}` | {$item['stock_remaining']} | {$item['expiry_date']} ({$item['days_left']}d) | {$item['supplier']} |\n";
                }

                $out .= "\n> ⚡ **Action:** অবিলম্বে সাপ্লায়ারের সাথে যোগাযোগ করে রিটার্ন বা ক্রেডিট অ্যাডজাস্টমেন্ট ক্লেইম করুন।";
                return $out;

            case 'get_low_stock_shortages':
                $count = $data['total_shortage_items'];
                if ($count === 0) {
                    return $isBn 
                        ? "✅ সকল ওষুধের পর্যাপ্ত মজুত রয়েছে। কোনো ঘাটতি নেই।"
                        : "✅ All inventory items are currently well-stocked above minimum alert thresholds.";
                }

                $out = $isBn
                    ? "### ⚡ স্বল্প স্টক ও রি-অর্ডার রিকমেন্ডেশন ({$count}টি ওষুধ)\n\n" .
                      "| ওষুধ | প্রস্তুতকারক | বর্তমান স্টক | অ্যালার্ট লেভেল | প্রস্তাবিত অর্ডার |\n" .
                      "| :--- | :--- | :--- | :--- | :--- |\n"
                    : "### ⚡ Low Stock & Reorder Recommendations ({$count} items)\n\n" .
                      "| Medicine | Manufacturer | Current Stock | Alert Level | Suggested Reorder |\n" .
                      "| :--- | :--- | :--- | :--- | :--- |\n";

                foreach ($data['items'] as $item) {
                    $out .= "| {$item['name']} | {$item['manufacturer']} | **{$item['current_stock']}** | {$item['min_stock_alert']} | `+{$item['suggested_reorder_qty']} units` |\n";
                }

                $out .= "\n> 🛒 **Tip:** ক্রয় তালিকা থেকে দ্রুত GRN তৈরি করে অর্ডার নিশ্চিত করুন।";
                return $out;

            case 'get_customer_dues_and_ledger':
                $total = number_format($data['total_pharmacy_receivables_bdt'], 2);
                $out = $isBn
                    ? "### 👥 গ্রাহকের বাকি ও লেজার বিবরণী\n\n" .
                      "• **ফার্মেসির মোট পাওনা (বাকি):** **৳{$total}**\n\n" .
                      "#### শীর্ষ বাকীদার গ্রাহকগণ:\n"
                    : "### 👥 Customer Accounts & Receivable Ledger\n\n" .
                      "• **Total Outstanding Receivables:** **৳{$total}**\n\n" .
                      "#### Top Debtor Accounts:\n";

                foreach ($data['top_debtors'] as $c) {
                    $due = number_format($c['current_due'], 2);
                    $out .= "• **{$c['name']}** (📞 `{$c['phone']}`) — বকেয়া: **৳{$due}**\n";
                }
                return $out;

            case 'get_supplier_bills_and_aging':
                $total = number_format($data['total_supplier_payable_bdt'], 2);
                $out = $isBn
                    ? "### 🏢 সাপ্লায়ার প্রদেয় বিল ও এইজিং রিপোর্ট\n\n" .
                      "• **সাপ্লায়ারদের মোট দেনা:** **৳{$total}**\n\n" .
                      "| PO নম্বর | সাপ্লায়ার | মোট বিল | পরিশোধিত | বাকি | এইজিং |\n" .
                      "| :--- | :--- | :--- | :--- | :--- | :--- |\n"
                    : "### 🏢 Supplier Bills & Aging Analysis\n\n" .
                      "• **Total Accounts Payable:** **৳{$total}**\n\n" .
                      "| PO # | Supplier | Grand Total | Paid | Due | Aging |\n" .
                      "| :--- | :--- | :--- | :--- | :--- | :--- |\n";

                foreach ($data['invoices'] as $po) {
                    $grand = number_format($po['grand_total'], 2);
                    $paid = number_format($po['paid_amount'], 2);
                    $due = number_format($po['due_amount'], 2);
                    $out .= "| `{$po['po_number']}` | {$po['supplier_name']} | ৳{$grand} | ৳{$paid} | **৳{$due}** | `{$po['aging_bucket']}` |\n";
                }
                return $out;

            case 'get_top_selling_medicines':
                $period = $data['period'];
                $out = $isBn
                    ? "### 🏆 শীর্ষ বিক্রিত ওষুধের তালিকা ({$period})\n\n" .
                      "| ওষুধ | জেনেরিক | বিক্রিত পরিমাণ | মোট রাজস্ব |\n" .
                      "| :--- | :--- | :--- | :--- |\n"
                    : "### 🏆 Top Selling Medications ({$period})\n\n" .
                      "| Medicine | Generic | Quantity Sold | Revenue |\n" .
                      "| :--- | :--- | :--- | :--- |\n";

                foreach ($data['top_medicines'] as $m) {
                    $rev = number_format($m['total_revenue_bdt'], 2);
                    $out .= "| **{$m['product_name']}** | `{$m['generic']}` | **{$m['units_sold']} pcs** | ৳{$rev} |\n";
                }
                return $out;

            case 'get_profit_and_loss_summary':
                $rev = number_format($data['total_revenue'], 2);
                $cogs = number_format($data['cost_of_goods_sold'], 2);
                $gross = number_format($data['gross_profit'], 2);
                $exp = number_format($data['total_expenses'], 2);
                $net = number_format($data['net_profit'], 2);
                $margin = $data['net_profit_margin_percent'];

                if ($isBn) {
                    return "### 📈 লাভ ও ক্ষতি বিবরণী ({$data['period']})\n\n" .
                           "• **মোট বিক্রয় রাজস্ব:** ৳{$rev}\n" .
                           "• **ক্রয় খরচ (COGS):** ৳{$cogs}\n" .
                           "• **গ্রস প্রফিট (Gross Profit):** ৳{$gross}\n" .
                           "• **অপারেটিং খরচ (Expenses):** ৳{$exp}\n" .
                           "• **নিট লাভ (Net Profit):** **৳{$net}**\n" .
                           "• **নিট প্রফিট মার্জিন:** **{$margin}%**\n\n" .
                           "> 💡 সুস্থ ফার্মেসি ব্যবসার জন্য আদর্শ নিট মার্জিন ১৫%-২২%।";
                }

                return "### 📈 Profit & Loss Statement ({$data['period']})\n\n" .
                       "• **Total Revenue:** ৳{$rev}\n" .
                       "• **Cost of Goods Sold (COGS):** ৳{$cogs}\n" .
                       "• **Gross Profit:** ৳{$gross}\n" .
                       "• **Operating Expenses:** ৳{$exp}\n" .
                       "• **Net Profit:** **৳{$net}**\n" .
                       "• **Net Margin:** **{$margin}%**\n\n" .
                       "> 💡 Target net profit margin for retail pharmacies is typically 15% - 22%.";

            case 'get_expense_breakdown':
                $total = number_format($data['total_expenses_bdt'], 2);
                $out = $isBn
                    ? "### 💸 ব্যয় ও খরচ বিশ্লেষণ ({$data['period']})\n\n" .
                       "• **মোট খরচ:** **৳{$total}**\n\n" .
                       "| খাতের নাম | খরচের পরিমাণ | এন্ট্রি সংখ্যা |\n" .
                       "| :--- | :--- | :--- |\n"
                    : "### 💸 Operating Expense Breakdown ({$data['period']})\n\n" .
                       "• **Total Expenses:** **৳{$total}**\n\n" .
                       "| Category Head | Amount (৳) | Entries |\n" .
                       "| :--- | :--- | :--- |\n";

                foreach ($data['breakdown'] as $b) {
                    $amt = number_format($b['amount'], 2);
                    $out .= "| {$b['category']} | ৳{$amt} | {$b['entry_count']} |\n";
                }
                return $out;

            default:
                return "Information retrieved successfully.";
        }
    }
}

