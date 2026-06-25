<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\HtmlString;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->globalSearch(false)
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('EDZELVOR FUEL TRADING WEB APP')
           ->colors([
                'primary' => [
                    50 => '#ecfdf5',
                    100 => '#d1fae5',
                    200 => '#a7f3d0',
                    300 => '#6ee7b7',
                    400 => '#34d399',
                    500 => '#10b981',
                    600 => '#059669',
                    700 => '#047857',
                    800 => '#065f46',
                    900 => '#064e3b',
                    950 => '#022c22',
                ],
            ])

            // Use full dashboard/content width
            ->maxContentWidth(Width::Full)

            // Sidebar can fully collapse on desktop
            ->sidebarFullyCollapsibleOnDesktop()

            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages'
            )
            ->pages([
                Dashboard::class,
            ])
            // ->discoverWidgets(
            //     in: app_path('Filament/Widgets'),
            //     for: 'App\\Filament\\Widgets'
            // )
            ->widgets([
                // \App\Filament\Widgets\FuelSalesStatsOverview::class,
                // \App\Filament\Widgets\FuelStockByProductChart::class,

                // \App\Filament\Widgets\FuelStocksChart::class,
                // \App\Filament\Widgets\SupplierPayablesTable::class,

                 \App\Filament\Widgets\FuelStockCardsWidget::class,

                \App\Filament\Widgets\FuelCustomerPurchaseStatsOverview::class,
                \App\Filament\Widgets\FuelSalesStatsOverview::class,
               
               
            ])
            ->renderHook(
                'panels::head.end',
                fn(): HtmlString => new HtmlString(<<<'HTML'
        <style>
            tr.customer-purchase-row-paid > td {
                background-color: rgba(34, 197, 94, 0.28) !important;
            }

            tr.customer-purchase-row-paid:hover > td {
                background-color: rgba(34, 197, 94, 0.42) !important;
            }

            tr.customer-purchase-row-balance > td {
                background-color: rgba(239, 68, 68, 0.30) !important;
            }

            tr.customer-purchase-row-balance:hover > td {
                background-color: rgba(239, 68, 68, 0.45) !important;
            }

            tr.customer-purchase-row-overpaid > td {
                background-color: rgba(59, 130, 246, 0.25) !important;
            }

            tr.customer-purchase-row-overpaid:hover > td {
                background-color: rgba(59, 130, 246, 0.40) !important;
            }

               tr.supplier-order-row-paid > td {
        background-color: rgba(34, 197, 94, 0.30) !important;
    }

    tr.supplier-order-row-paid:hover > td {
        background-color: rgba(34, 197, 94, 0.45) !important;
    }

    tr.supplier-order-row-balance > td {
        background-color: rgba(239, 68, 68, 0.35) !important;
    }

    tr.supplier-order-row-balance:hover > td {
        background-color: rgba(239, 68, 68, 0.50) !important;
    }
        </style>
    HTML)
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
