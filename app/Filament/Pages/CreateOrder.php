<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use App\Models\User;
use Filament\Actions\Action;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Filament\Facades\Filament;

class CreateOrder extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-plus';

    protected static ?string $navigationGroup = 'Orders';

    protected static string $view = 'filament.pages.create-order';

    public $quantities = [];
    public $selectedUser = null;
    public $order_date;
    public $search = '';

    public function getTitle(): string
    {
        return ''; // Ensure nothing is rendered
    }

    // public static function canAccess(): bool
    // {
    //     return Filament::auth()->user()->hasPermissionTo('Create Order');
    // }

    public function getActions(): array
    {
        return [
            Action::make('viewCart')
                ->label('View Cart')
                ->modalHeading('Your Cart')
                ->modalSubmitAction(false) // No submit button
                ->modalContent(function () {
                    return view('filament.pages.partials.cart', [
                        'cartItems' => $this->getCartItems(),
                        'total' => $this->getCartTotal()
                    ]);
                })->extraModalFooterActions([
                Action::make('Checkout')
                ->label('Checkout')
                ->color('success')
                ->icon('heroicon-m-shopping-cart')
                ->requiresConfirmation()
                ->action(fn () => $this->checkout()),
            ])
        ];
    }

    public function getCartItems()
    {
        return collect($this->quantities)
            ->filter(fn($qty) => $qty > 0)
            ->map(function ($qty, $id) {
                $product = \App\Models\Product::find($id);
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $qty,
                    'subtotal' => $product->price * $qty,
                ];
            })->values();
    }

    public function getCartTotal()
    {
        return $this->getCartItems()->sum('subtotal');
    }

    public function form( Form $form ): Form {
        return $form->schema( [
            Grid::make( 2 )->schema( [
                Select::make( 'selectedUser' )
                ->label( 'Select User' )
                ->options( User::pluck( 'name', 'id' ) )
                ->searchable()
                ->required()
                 ->createOptionForm([
                    TextInput::make('userid')
                        ->required(),
                ])
                ->createOptionUsing(function (array $data) {
                    $user = User::create([
                        'name' => $data['userid'], 
                        'userid' => $data['userid'],
                        'email' => $data['userid'].'@mypowerworld.com',
                        'password' =>  Hash::make(Str::random(12)),
                        'contact' => random_int(1000000000, 9999999999),
                        'type' => 'retailer',    
                    ]);

                    return $user->id;
                }),
                DatePicker::make( 'order_date' )
                ->label( 'Order Date' )
                ->default( now() ) // ⬅️ sets today's date
                    ->required(),
            ])
            ]);
        
    }

    public function getCartTotalProperty()
    {
        return collect($this->quantities)
            ->filter(fn($qty) => $qty > 0)
            ->map(fn($qty, $id) => \App\Models\Product::find($id)->price * $qty)
            ->sum();
    }


    public function mount()
    {
        foreach (Product::all() as $product) {
            $this->quantities[$product->id] = "";
        }
        $this->form->fill([
            'selectedUser' => null,
            'order_date' => now()->toDateString(),
        ]);
    }

    // public function checkout()
    // {   
    //     $orderid = time().$this->selectedUser;
    //     $order = Order::create([
    //         'user_id' => $this->selectedUser,
    //         'orderid' => $orderid,
    //         'mainstatus' => 'pending',
    //         'date' => $this->order_date,
    //         'save' => false,
    //         'total' => $this->getCartTotal(),
    //         'net_total' => $this->getCartTotal(), // apply discounts if any
    //     ]);
    //         foreach ($this->getCartItems() as $item) {
    //             $product = \App\Models\Product::find($item['id']);
    //             // $offers = $product->offer;
    //             $quantity = $item['quantity'];
    //             OrderItem::create([
    //                 'orderid' => $order->orderid,
    //                 'product_id' => $item['id'],
    //                 'price' => $item['price'],
    //                 'quantity' => $item['quantity'],
    //                 'approvedquantity' => 0,
    //                 'status' => 'pending'
    //             ]);
    //         }
    //     $this->selectedUser = null;
    //     $this->order_date = now()->toDateString();
    //     foreach ($this->quantities as $key => $val) {
    //         $this->quantities[$key] = '';
    //     }
    //     // $this->dispatch('close-modal');
    //     Notification::make()
    //         ->title('Order Created!')
    //         ->success()
    //         ->send();
    //     return redirect('/admin/orders');
        
    // }

   public function getProductsProperty()
    {
        return Product::with('category')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->orderBy('categories.ordernum') // Order by category order
            ->orderBy('products.ordernum')   // Order within each category
            ->select('products.*', 'categories.category as category') // Important: select only product fields to avoid column conflicts
            ->get();
    }

    public function getUserOptionsProperty()
    {
        return \App\Models\User::pluck('name', 'id' );
    }
}
