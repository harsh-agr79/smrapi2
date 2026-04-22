<x-filament-panels::page>
    <style>
        .amount-positive {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .dark .amount-positive {
            background-color: #4b1c1c;
            color: #f87171;
        }

        .amount-negative {
            background-color: #d1fae5;
            color: #065f46;
        }

        .dark .amount-negative {
            background-color: #064e3b;
            color: #6ee7b7;
        }

        .amount-cell {
            padding: 0.25rem 0.5rem;
            border: 1px solid #e5e7eb;
        }

        .dark .amount-cell {
            border-color: #374151;
        }

        /* Container positioning */
        .chatbot-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            max-width: 360px;
            max-height: 600px;
            z-index: 9999;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Toggle button */
        .chatbot-toggle-btn {
            background-color: #cc8b00;
            color: white;
            border: none;
            border-radius: 9999px;
            padding: 10px 20px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .chatbot-toggle-btn:hover {
            background-color: #af701e;
        }

        /* Chat window */
        .chatbot-window {
            margin-top: 10px;
            background-color: white;
            border: 1px solid #d1d5db;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            padding: 16px;
            display: flex;
            flex-direction: column;
            max-height: 500px;
            width: 100%;
        }

        /* Messages area */
        .chatbot-messages {
            flex: 1;
            overflow-y: auto;
            padding-right: 6px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            overflow-x: hidden;
        }

        /* Scrollbar styling */
        .chatbot-messages::-webkit-scrollbar {
            width: 4px;
        }

        .chatbot-messages::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 3px;
        }

        /* Message wrapper alignment */
        .chatbot-msg-wrapper {
            display: flex;
            padding-bottom: 10px;
        }

        .chatbot-msg-user {
            justify-content: flex-end;
        }

        .chatbot-msg-bot {
            justify-content: flex-start;
        }

        /* Message bubbles */
        .chatbot-msg {
            max-width: 80%;
            padding: 8px 14px;
            border-radius: 16px;
            word-wrap: break-word;
            line-height: 1.4;
        }

        .chatbot-msg-user-bg {
            background-color: #c99300;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .chatbot-msg-bot-bg {
            background-color: #f3f4f6;
            color: black;
            border-bottom-left-radius: 4px;
        }

        /* Loading indicator */
        .chatbot-loading {
            text-align: center;
            color: gray;
            font-size: 12px;
            padding: 8px;
            animation: fadeScale 1s ease-in-out infinite alternate;
        }

        @keyframes fadeScale {
            0% {
                transform: scale(0.95);
                opacity: 0.6;
            }

            100% {
                transform: scale(1.1);
                opacity: 1;
            }
        }

        /* Input section */
        .chatbot-input-wrapper {
            display: flex;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            gap: 0;
        }

        /* Input box */
        .chatbot-input {
            flex: 1;
            border: 1px solid #d1d5db;
            border-radius: 8px 0 0 8px;
            padding: 8px 12px;
            outline: none;
            font-size: 14px;
            transition: border-color 0.2s;
            color: black;
        }

        .chatbot-input:focus {
            border-color: #eba625;
        }

        /* Send button */
        .chatbot-send-btn {
            background-color: #ebb625;
            color: white;
            border: none;
            border-radius: 0 8px 8px 0;
            padding: 8px 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .chatbot-send-btn:hover {
            background-color: #af581e;
        }
    </style>
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    @endpush
    <div
        class="border bg-order-summary border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-900 text-black dark:text-white shadow-sm items-center">
        {{ $this->form }}
    </div>
    <div class="border bg-order-summary border-gray-200 dark:border-gray-700 rounded-lg p-1 bg-white dark:bg-gray-900 text-black dark:text-white shadow-sm text-center"
        style="font-weight: 600; font-size: 1.4rem;">
        Total Sales: Rs. {{ indian_number_format($this->analyticsData['overall_sales']) }}
    </div>

    <div class="overflow-x-auto rounded-b-md">
        <div class="w-full divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900 p-3 rounded-md"
            id="accordion">
            @foreach ($this->analyticsData['categories'] as $index => $category)
                <div x-data="{ open: false }" class="bg-white dark:bg-gray-900"
                    data-category-id="{{ $category['category_id'] }}">
                    <!-- Accordion Header -->
                    <button @click="open = !open"
                        class="w-full flex justify-between items-center px-4 py-3 text-sm sm:text-base text-left font-medium bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <div class="w-1/2 truncate" style="color: rgb(0, 162, 255)">{{ $category['category_name'] }}
                        </div>
                        <div class="w-1/4 text-left">{{ $category['total_quantity'] }}</div>
                        <div class="w-1/4 text-right">{{ indian_number_format($category['total_sales']) }}</div>
                    </button>

                    <!-- Accordion Content -->
                    <div x-show="open" x-collapse>
                        <div>
                            @php
                                $subs = $category['sub_categories'] ?? [];
                            @endphp

                            @foreach ($subs as $item)
                                <label
                                    class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input type="checkbox" id="sub_{{ $category['category_id'] }}_{{ $item['id'] }}"
                                        data-sub-checkbox data-cat-id="{{ $category['category_id'] }}"
                                        data-sub-id="{{ $item['id'] }}"
                                        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:checked:bg-primary-600">
                                    <span>{{ $item['name'] }}</span>
                                </label>
                            @endforeach

                            <label
                                class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" id="showHidden_{{ $category['category_id'] }}" data-show-hidden
                                    data-cat-id="{{ $category['category_id'] }}"
                                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:checked:bg-primary-600">
                                <span>Show Hidden</span>
                            </label>

                            <label
                                class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" id="mustInclude_{{ $category['category_id'] }}"
                                    data-must-include data-cat-id="{{ $category['category_id'] }}"
                                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:checked:bg-primary-600">
                                <span>Must Include Selected Tags</span>
                            </label>
                        </div>

                        <table
                            class="w-full text-sm sm:text-base text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                            <thead class="bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-2 text-left">Product</th>
                                    <th class="px-4 py-2 text-left">Quantity</th>
                                    <th class="px-4 py-2 text-right">Sales (Rs.)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $products = $category['products'] ?? [];
                                    $hasProducts = count($products) > 0;
                                @endphp

                                @if ($hasProducts)
                                    @foreach ($products as $product)
                                        @php
                                            // normalize sub ids to a comma separated string for data attr
                                            $prodSubIds = $product['sub_category_id'] ?? [];
                                            if (is_string($prodSubIds)) {
                                                $decoded = json_decode($prodSubIds, true);
                                                $prodSubIds = is_array($decoded) ? $decoded : [$prodSubIds];
                                            } elseif (!is_array($prodSubIds)) {
                                                $prodSubIds = [$prodSubIds];
                                            }
                                            $prodSubIds = array_filter($prodSubIds, fn($v) => $v !== null && $v !== '');
                                            $prodSubIdsAttr = implode(',', $prodSubIds);
                                            $isHidden = (int) $product['hidden'];
                                        @endphp

                                        <tr class="border-t border-gray-100 dark:border-gray-800"
                                            data-cat-id="{{ $category['category_id'] }}"
                                            data-hidden="{{ $isHidden }}" data-subids="{{ $prodSubIdsAttr }}">
                                            <td class="px-4 py-2" style="color:rgb(0, 162, 255)">
                                                <a
                                                    href="{{ url('/admin/sort-analytics?productId=' . $product['product_id'] . '&startDate=' . $this->startDate . '&endDate=' . $this->endDate . '&customerId=' . $this->customerId) }}">
                                                    {{ $product['product_name'] }}
                                                </a>
                                            </td>
                                            <td class="px-4 py-2 text-left">{{ $product['quantity'] }}</td>
                                            <td class="px-4 py-2 text-right">
                                                {{ indian_number_format($product['sales']) }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    {{-- placeholder row shown when filtering hides all products --}}
                                    <tr class="no-products-match hidden">
                                        <td class="px-4 py-2 text-center text-gray-500 dark:text-gray-400"
                                            colspan="3">
                                            No products match the selected filters
                                        </td>
                                    </tr>
                                @else
                                    {{-- original "no products" state preserved for categories with zero products --}}
                                    <tr>
                                        <td class="px-4 py-2 text-center text-gray-500 dark:text-gray-400"
                                            colspan="3">
                                            No products found
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-4 rounded-lg" wire:ignore>
            <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-100 mb-4">Category-wise Quantity</h2>
            <div id="quantityChart" style="width:100%; height:400px;"></div>
        </div>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-4 rounded-lg" wire:ignore>
            <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-100 mb-4">Category-wise Sales</h2>
            <div id="salesChart" style="width:100%; height:400px;"></div>
        </div>
    </div>


    @push('scripts')
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script>
            google.charts.load('current', {
                packages: ['corechart']
            });

            // Store latest data globally
            let latestData = @json($this->analyticsData);

            google.charts.setOnLoadCallback(() => {
                drawCharts(latestData);
            });

            // Listen for Livewire events to redraw
            document.addEventListener('livewire:load', function() {
                Livewire.hook('message.processed', (message, component) => {
                    // Update latest data from Livewire
                    latestData = @this.get('dataProperty'); // <-- We need to get updated data here

                    // Instead, we’ll use a Livewire event with the updated data:

                    // So, wait for event below (see Step 3)
                });
            });

            // Listen for custom event to redraw charts with new data
            window.addEventListener('analyticsDataUpdated', event => {
                drawCharts(event.detail);
            });

            function drawCharts(data) {
                // console.log
                if (Array.isArray(data)) {
                    data = data[0];
                }
                const quantityData = [
                    ['Category', 'Quantity']
                ];
                const salesData = [
                    ['Category', 'Sales']
                ];

                data.categories.forEach(category => {
                    quantityData.push([category.category_name, category.total_quantity]);
                    salesData.push([category.category_name, category.total_sales]);
                });

                const options = {
                    legend: {
                        position: 'right',
                        alignment: 'center',
                        textStyle: {
                            fontSize: 14
                        }
                    },
                    chartArea: {
                        width: '70%',
                        height: '80%'
                    },
                    backgroundColor: 'transparent'
                };

                const quantityChart = new google.visualization.PieChart(document.getElementById('quantityChart'));
                quantityChart.draw(google.visualization.arrayToDataTable(quantityData), options);

                const salesChart = new google.visualization.PieChart(document.getElementById('salesChart'));
                salesChart.draw(google.visualization.arrayToDataTable(salesData), options);
            }
        </script>
    @endpush
    @php
        $aiContext = $this->analyticsData;
    @endphp
    <script>
        window.__AI_PAGE_CONTEXT__ = @json($aiContext);

        window.addEventListener('context-updated', (e) => {
            window.__AI_PAGE_CONTEXT__ = e.detail.context;
        });
    </script>

</x-filament-panels::page>
