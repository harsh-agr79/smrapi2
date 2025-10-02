<x-filament::page>
    <style>
        .outofstock {
            color: red;
        }

        .instock {
            color: green;
        }

        .hide-scrollbar {
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE and Edge */
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari, Opera */
        }
    </style>
    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
            {{ session('message') }}
        </div>
    @endif
    <div
        class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-order-summary dark:bg-gray-900 text-black dark:text-white shadow-sm items-center border rounded-lg p-3">
        {{ $this->form }}
        <div class="py-2 flex justify-between items-center">
            <div class="w-30">
                <x-filament::actions :actions="$this->getActions()" />
            </div>
            <div class="flex-1 px-2">
                <input type="text" id="productSearch" style="color:black;" placeholder="Search products..."
                    class="w-full px-4 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
            </div>
        </div>
        <div class="flex items-center justify-between px-2 py-1">
            {{-- Cart total --}}
            <div class="font-semibold text-sm">
                Cart Total: रु{{ number_format($this->cartTotal, 2) }}
            </div>

            {{-- Toggle --}}
            {{-- <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                <input type="checkbox" id="toggleHidden"
                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:checked:bg-primary-600">
                <span>Show all products</span>
            </label> --}}
        </div>

    </div>
    <div style="height: 65vh; overflow-y:scroll;" class="hide-scrollbar">
        <form wire:submit.prevent="checkout">
            @foreach ($this->Products as $product)
                <div class="product-card flex items-center gap-4 border border-gray-200 dark:border-gray-700 rounded-lg p-2 bg-white dark:bg-gray-900 text-black dark:text-white shadow-sm w-full"
                    style="margin-bottom:5px;" data-hidden="{{ $product->hidden ? 'true' : 'false' }}">
                    <div class="flex overflow-hidden rounded border">
                        <img src="{{ $product->images[0] && file_exists(storage_path('app/public/' . $product->images[0]))
                            ? asset($product->images[0])
                            : asset('images/dummy.png') }}"
                            alt="{{ $product->name }}" style="width: 80px;" />
                    </div>

                    {{-- Details --}}
                    <div class="flex-1">
                        <p class="product-name" style="font-size: 13px; font-weight: 500;">{{ $product->name }}</p>
                        <p class="text-xs text-gray-600 product-category">
                            {{ $product->category->name ?? 'N/A' }}
                        </p>

                        <div class="product-price">
                            <span
                                style="background: rgb(153, 105, 0); color: white; font-size: 12px; font-weight: 500; padding: 3px; border-radius: 4px; margin: 1px;">रु{{ $product->price }}</span>



                            {{-- @if ($product->offer)
                                <span>
                                    @foreach ($product->offer as $pcs => $price)
                                        <span
                                            style="background: rgb(98, 0, 255); color: white; font-size: 12px; font-weight: 500; padding: 3px; border-radius: 4px; margin: 1px;">{{ $pcs }}
                                            pcs @ Rs. {{ $price }}</span>
                                    @endforeach
                                </span>
                            @endif --}}
                        </div>
                    </div>

                    {{-- Quantity --}}
                    <div class="w-20">
                        <div class="text-xs">
                            <span class="{{ $product->stock ? 'outofstock' : 'instock' }} product-stock">
                                {{ $product->stock ? 'Out of Stock' : 'In Stock' }}
                            </span>
                        </div>
                        <input type="number" wire:model.lazy="quantities.{{ $product->id }}" min="0"
                            placeholder="Qty"
                            class="w-full px-3 py-1 text-sm border border-gray-300 rounded-md text-black shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50"
                            style="color:black;" />
                    </div>
                </div>
            @endforeach
        </form>
    </div>


    {{-- <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('productSearch');
            const productCards = document.querySelectorAll('.product-card');

            searchInput.addEventListener('input', () => {
                const query = searchInput.value.toLowerCase();

                productCards.forEach(card => {
                    const name = card.querySelector('.product-name')?.textContent.toLowerCase() ||
                        '';
                    const category = card.querySelector('.product-category')?.textContent
                        .toLowerCase() || '';
                    const price = card.querySelector('.product-price')?.textContent.toLowerCase() ||
                        '';
                    const stock = card.querySelector('.product-stock')?.textContent.toLowerCase() ||
                        '';

                    const matches = name.includes(query) || category.includes(query) || price
                        .includes(query) || stock.includes(query);

                    card.style.display = matches ? 'flex' : 'none';
                });
            });
        });
    </script> --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('productSearch');
            const productCards = document.querySelectorAll('.product-card');
            const toggleHidden = document.getElementById('toggleHidden');

            function filterProducts() {
                const query = searchInput.value.toLowerCase();
                const showAll = toggleHidden.checked;

                productCards.forEach(card => {
                    const name = card.querySelector('.product-name')?.textContent.toLowerCase() || '';
                    const category = card.querySelector('.product-category')?.textContent.toLowerCase() ||
                        '';
                    const price = card.querySelector('.product-price')?.textContent.toLowerCase() || '';
                    const stock = card.querySelector('.product-stock')?.textContent.toLowerCase() || '';
                    const hidden = card.getAttribute('data-hidden') === 'true';

                    const matchesSearch = name.includes(query) || category.includes(query) || price
                        .includes(query) || stock.includes(query);

                    // default: hide hidden ones, unless "Show all" is checked
                    const matchesHidden = showAll || !hidden;

                    card.style.display = (matchesSearch && matchesHidden) ? 'flex' : 'none';
                });
            }

            searchInput.addEventListener('input', filterProducts);
            toggleHidden.addEventListener('change', filterProducts);

            // run once on load
            filterProducts();
        });
    </script>



</x-filament::page>
