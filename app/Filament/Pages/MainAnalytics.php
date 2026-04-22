<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\User;
use App\Models\Brand;
use Filament\Forms\Form;
use Carbon\Carbon;
use Filament\Forms\Components\ {
    Grid, Select, DatePicker}
    ;
    use Filament\Facades\Filament;
    use App\Services\AnalyticsService;

    class MainAnalytics extends Page {
        protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
        protected static ?string $navigationGroup = 'Deep Analytics';
        protected static string $view = 'filament.pages.main-analytics';

        public ?int $customerId = null;
        public ?int $brandId = null;
        public ?string $startDate = null;
        public ?string $endDate = null;
        public array $analyticsData = [];

        public function getTitle(): string {
            return '';
        }

        // public static function canAccess(): bool {
        //     return Filament::auth()->user()->hasPermissionTo( 'Main Analytics' );
        // }

        public function form( Form $form ): Form {

            $today = Carbon::today();
            $yearOffset = ($today->month > 9 || ($today->month == 9 && $today->day >= 17)) ? 0 : -1;

            $start = Carbon::create($today->year + $yearOffset, 9, 17);
            $end   = (clone $start)->addYear()->subDay();

            return $form->schema( [
                Grid::make( 4 )->schema( [
                    Select::make( 'customerId' )
                    ->label( 'Select Customer' )
                    ->options( User::pluck( 'name', 'id' ) )
                    ->searchable()
                    ->nullable()
                    ->live(),

                    Select::make( 'brandId' )
                    ->label( 'Select Brand' )
                    ->options( Brand::pluck( 'name', 'id' ) )
                    ->searchable()
                    ->nullable()
                    ->live(),

                    DatePicker::make( 'startDate' )
                    ->label( 'Start Date' )
                    ->required()
                    ->default( $start->toDateString() )
                    ->live(),

                    DatePicker::make( 'endDate' )
                    ->label( 'End Date' )
                    ->required()
                    ->default( $end->toDateString() )
                    ->live(),
                ] ),
            ] );
        }

        public function mount(?int $customerId = null): void
        {
            $this->customerId = $customerId ?? request()->get('customerId');
            $this->brandId = $brandId ?? request()->get('brandId');

            $today = Carbon::today();
            $yearOffset = ($today->month > 9 || ($today->month == 9 && $today->day >= 17)) ? 0 : -1;

            $start = Carbon::create($today->year + $yearOffset, 9, 17);
            $end   = (clone $start)->addYear()->subDay();

            $this->startDate = request()->get('startDate') ?? $start->toDateString();
            $this->endDate   = request()->get('endDate') ?? $end->toDateString();

            $analyticsService = app(\App\Services\AnalyticsService::class);

            $this->analyticsData = $analyticsService->getSalesData(
                $this->customerId,
                $this->startDate,
                $this->endDate,
                $this->brandId
            );

            $this->dispatch('analyticsDataUpdated', $this->analyticsData);
        }

        public function updated($property): void
        {
            if (in_array($property, ['customerId', 'brandId', 'startDate', 'endDate'])) {
                $analyticsService = app(\App\Services\AnalyticsService::class);

                $this->analyticsData = $analyticsService->getSalesData(

                    $this->customerId,
                    $this->startDate,
                    $this->endDate,
                    $this->brandId
                );

                $this->dispatch('analyticsDataUpdated', $this->analyticsData);
            }
        }
    }
