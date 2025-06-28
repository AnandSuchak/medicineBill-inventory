<?php
namespace App\Filament\Resources\PurchaseBillResource\Pages;

use App\Filament\Resources\PurchaseBillResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist; // Import Infolist
use Filament\Infolists\Components\Section; // Import Section
use Filament\Infolists\Components\TextEntry; // Import TextEntry
use Filament\Infolists\Components\RepeatableEntry; // Import RepeatableEntry for nested data
use Filament\Infolists\Components\Grid; // Import Grid for layout
use Filament\Infolists\Components\Tabs; // Import Tabs if we want sections
use App\Models\Medicine; // Import Medicine model to get name


class ViewPurchaseBill extends ViewRecord
{
    protected static string $resource = PurchaseBillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(), // Allows editing the record directly from the view page
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Purchase Bill Overview')
                    ->columns(3) // Display basic details in 3 columns
                    ->schema([
                        TextEntry::make('bill_number')
                            ->label('Bill No.'),
                        TextEntry::make('supplier.name')
                            ->label('Supplier'),
                        TextEntry::make('bill_date')
                            ->label('Bill Date')
                            ->date(),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Generated' => 'gray',
                                'Received' => 'success',
                                'Paid' => 'info',
                                'Unpaid' => 'warning',
                                'Cancelled' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('total_gst_amount')
                            ->label('Actual GST Paid')
                            ->money('INR'),
                        TextEntry::make('total_amount')
                            ->label('Grand Total')
                            ->money('INR'),
                        TextEntry::make('notes')
                            ->columnSpanFull(),
                    ]),

                Section::make('Medicine Batches in this Bill')
                    ->schema([
                        RepeatableEntry::make('stockBatches')
                            ->label('Purchased Items')
                            ->schema([
                                TextEntry::make('medicine.name')
                                    ->label('Medicine Name'),
                                TextEntry::make('batch_number')
                                    ->label('Batch No.'),
                                TextEntry::make('expiry_date')
                                    ->label('Expiry Date')
                                    ->date(),
                                TextEntry::make('quantity')
                                    ->label('Quantity')
                                    ->suffix(fn (\App\Models\StockBatch $record) => ' ' . ($record->medicine->unit ?? 'Units')), // Display unit
                                TextEntry::make('purchase_price')
                                    ->label('Purchase Price (Per Unit/Pack)')
                                    ->money('INR'),
                                TextEntry::make('ptr')
                                    ->label('PTR')
                                    ->money('INR'),
                                TextEntry::make('discount_percentage')
                                    ->label('Discount (%)')
                                    ->suffix('%'),
                            ])
                            ->columns(4) // Display repeater items in a 4-column grid
                            ->grid(3), // Displays 3 items per row in the grid layout for repeatable entry
                    ]),
            ]);
    }
}
