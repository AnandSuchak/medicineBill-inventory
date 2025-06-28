<?php
namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseBillResource\Pages;
use App\Filament\Resources\PurchaseBillResource\RelationManagers;
use App\Models\Medicine; // Imported Medicine model
use App\Models\PurchaseBill;
use App\Models\Supplier; // Imported Supplier model
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Repeater; // Imported Repeater
use Filament\Forms\Components\Select; // Imported Select
use Filament\Forms\Components\TextInput; // Imported TextInput
use Filament\Forms\Components\DatePicker; // Imported DatePicker
use Filament\Forms\Set; // Imported Set for Livewire interaction
use Filament\Forms\Get; // Imported Get for Livewire interaction
use Illuminate\Support\HtmlString; // For hint text HTML

// Specific imports for actions to ensure correct type hinting and prevent errors
use Filament\Forms\Components\Actions\Action as FormAction; // Alias FormAction to avoid conflict
use Filament\Tables\Actions\DeleteAction; // Specific import for Tables\Actions
use Filament\Tables\Actions\EditAction; // Specific import for Tables\Actions
use Filament\Tables\Actions\ViewAction; // Specific import for Tables\Actions
use Filament\Tables\Actions\BulkActionGroup; // Specific import for Tables\Actions
use Filament\Tables\Actions\DeleteBulkAction; // Specific import for Tables\Actions


class PurchaseBillResource extends Resource
{
    protected static ?string $model = PurchaseBill::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag'; // Icon for purchase bills

    protected static ?string $navigationGroup = 'Inventory & Purchases'; // New navigation group

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Purchase Bill Details')
                    ->description('Basic information for the purchase transaction.')
                    ->schema([
                        Forms\Components\Select::make('supplier_id')
                            ->label('Supplier')
                            ->required()
                            ->relationship('supplier', 'name') // Assumes a 'supplier' relationship in PurchaseBill model
                            ->searchable()
                            ->preload()
                            ->native(false) // Better UI for select
                            ->createOptionForm([ // Allows creating new supplier on the fly
                                Forms\Components\TextInput::make('name')->required()->unique(),
                                Forms\Components\TextInput::make('gst_number')->label('GST Number')->required()->unique(),
                                Forms\Components\TextInput::make('dln')->label('Drug License Number (DLN)')->required()->unique(),
                                Forms\Components\TextInput::make('phone')->tel()->nullable(),
                                Forms\Components\TextInput::make('email')->email()->nullable(),
                                Forms\Components\Textarea::make('address')->nullable(),
                            ])
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('bill_date')
                            ->label('Bill Date')
                            ->default(now()) // Default to today's date
                            ->required()
                            ->native(false) // Better UI for date picker
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('bill_number')
                            ->label('Bill Number')
                            ->maxLength(255)
                            ->nullable()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),
                        Forms\Components\Select::make('status')
                            ->options([
                                'Generated' => 'Generated',
                                'Received' => 'Received',
                                'Partially Received' => 'Partially Received', // Can add if partial deliveries are needed
                                'Paid' => 'Paid',
                                'Unpaid' => 'Unpaid',
                                'Cancelled' => 'Cancelled',
                            ])
                            ->default('Generated')
                            ->required()
                            ->native(false)
                            ->columnSpan(1),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->rows(2)
                            ->nullable()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Medicine Batches Purchased')
                    ->description('Add each medicine item and its batch details from this purchase bill.')
                    ->schema([
                        Repeater::make('stockBatches')
                            ->relationship('stockBatches') // Assumes 'stockBatches' relationship in PurchaseBill model
                            ->label('Medicines Purchased')
                            ->schema([
                                Forms\Components\Select::make('medicine_id')
                                    ->label('Medicine')
                                    ->required()
                                    ->relationship('medicine', 'name') // Assumes 'medicine' relationship in StockBatch model
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->placeholder('Select Medicine')
                                    ->columnSpan(1)
                                    ->reactive() // Make this field reactive for live updates
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        // When medicine is selected, try to pre-fill GST rate
                                        $medicine = Medicine::find($get('medicine_id'));
                                        if ($medicine) {
                                            $set('medicine_gst_rate', $medicine->gst_rate);
                                        }
                                    }),

                                Forms\Components\Hidden::make('medicine_gst_rate'), // Hidden field to store medicine's GST rate for calculation

                                Forms\Components\TextInput::make('batch_number')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter Batch Number')
                                    ->columnSpan(1)
                                    ->rules([ // Custom validation for uniqueness per medicine
                                        function (Get $get) {
                                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                                $medicineId = $get('medicine_id');
                                                if (
                                                    $medicineId &&
                                                    \App\Models\StockBatch::where('medicine_id', $medicineId)
                                                        ->where('batch_number', $value)
                                                        ->whereNot('id', $get('id')) // Ignore current record on edit
                                                        ->exists()
                                                ) {
                                                    $fail("This batch number already exists for the selected medicine.");
                                                }
                                            };
                                        },
                                    ]),

                                Forms\Components\DatePicker::make('expiry_date')
                                    ->required()
                                    ->native(false)
                                    ->minDate(now()->addMonth()) // Expiry date must be at least one month in future
                                    ->placeholder('Select Expiry Date')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('quantity')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->hint(fn (Get $get) => new HtmlString('Unit: ' . ($get('medicine_id') ? Medicine::find($get('medicine_id'))?->unit : 'N/A')))
                                    ->placeholder('e.g., 100')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('purchase_price')
                                    ->label('Purchase Price (Per Unit/Pack)')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('₹')
                                    ->placeholder('e.g., 150.00')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('ptr')
                                    ->label('PTR (Price to Retail)')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('₹')
                                    ->placeholder('e.g., 200.00')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('discount_percentage')
                                    ->label('Discount (%) from Supplier')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->default(0)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->placeholder('e.g., 5')
                                    ->columnSpan(1),
                            ])
                            ->columns(3) // Arrange repeater fields in 3 columns
                            ->minItems(1) // At least one item required per bill
                            ->defaultItems(1)
                            ->createItemButtonLabel('Add Another Medicine')
                            ->deleteAction(
                                fn (FormAction $action) => $action // FIXED: Changed type hint to FormAction
                            )
                            ->reorderable(false) // Order doesn't matter for purchase items
                            ->addActionLabel('Add New Medicine Item')
                            ->itemLabel(fn (array $state): ?string => Medicine::find($state['medicine_id'])?->name ?? null), // Display medicine name as item label
                    ]),

                Forms\Components\Section::make('Bill Summary')
                    ->schema([
                        Forms\Components\Placeholder::make('calculated_subtotal') // Renamed for clarity vs stored total
                            ->label('Calculated Subtotal (Excl. GST)')
                            ->content(function (Get $get) {
                                $total = 0;
                                foreach ($get('stockBatches') as $item) {
                                    $quantity = (float)($item['quantity'] ?? 0);
                                    $purchasePrice = (float)($item['purchase_price'] ?? 0);
                                    $discount = (float)($item['discount_percentage'] ?? 0);
                                    $itemTotal = $quantity * $purchasePrice;
                                    $itemTotalAfterDiscount = $itemTotal - ($itemTotal * ($discount / 100));
                                    $total += $itemTotalAfterDiscount;
                                }
                                return '₹ ' . number_format($total, 2);
                            }),

                        Forms\Components\Placeholder::make('calculated_gst') // Renamed for clarity vs stored total
                            ->label('Calculated GST Amount')
                            ->content(function (Get $get) {
                                $totalGst = 0;
                                foreach ($get('stockBatches') as $item) {
                                    $quantity = (float)($item['quantity'] ?? 0);
                                    $purchasePrice = (float)($item['purchase_price'] ?? 0);
                                    $gstRate = (float)($item['medicine_gst_rate'] ?? 0); // Use stored GST rate
                                    $discount = (float)($item['discount_percentage'] ?? 0);

                                    $itemTotal = $quantity * $purchasePrice;
                                    $itemTotalAfterDiscount = $itemTotal - ($itemTotal * ($discount / 100));
                                    $itemGst = $itemTotalAfterDiscount * ($gstRate / 100);
                                    $totalGst += $itemGst;
                                }
                                return '₹ ' . number_format($totalGst, 2);
                            }),

                        Forms\Components\TextInput::make('total_gst_amount') // Now this field is for user input
                            ->label('Actual GST Paid (Manual Input)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('₹')
                            ->placeholder('Enter total GST from bill')
                            ->live(onBlur: true) // Update grand total on blur
                            ->hint(new HtmlString('Enter the exact GST amount from the supplier\'s bill. If left empty, calculated GST will be used.')),

                        Forms\Components\Placeholder::make('calculated_grand_total')
                            ->label('Grand Total (Incl. GST)')
                            ->content(function (Get $get) {
                                $subtotal = 0;
                                foreach ($get('stockBatches') as $item) {
                                    $quantity = (float)($item['quantity'] ?? 0);
                                    $purchasePrice = (float)($item['purchase_price'] ?? 0);
                                    $discount = (float)($item['discount_percentage'] ?? 0);

                                    $itemTotal = $quantity * $purchasePrice;
                                    $itemTotalAfterDiscount = $itemTotal - ($itemTotal * ($discount / 100));
                                    $subtotal += $itemTotalAfterDiscount;
                                }
                                $manualGst = (float)($get('total_gst_amount') ?? 0); // Get manual GST input
                                $calculatedGst = 0;
                                // Recalculate GST based on items for fallback if manual is empty
                                if (empty($get('total_gst_amount'))) {
                                    foreach ($get('stockBatches') as $item) {
                                        $quantity = (float)($item['quantity'] ?? 0);
                                        $purchasePrice = (float)($item['purchase_price'] ?? 0);
                                        $gstRate = (float)($item['medicine_gst_rate'] ?? 0);
                                        $discount = (float)($item['discount_percentage'] ?? 0);
                                        $itemTotal = $quantity * $purchasePrice;
                                        $itemTotalAfterDiscount = $itemTotal - ($itemTotal * ($discount / 100));
                                        $itemGst = $itemTotalAfterDiscount * ($gstRate / 100);
                                        $calculatedGst += $itemGst;
                                    }
                                }
                                $finalGst = $manualGst > 0 ? $manualGst : $calculatedGst;

                                return '₹ ' . number_format($subtotal + $finalGst, 2);
                            })
                            ->hint(new HtmlString('This is the final amount to be paid for the purchase bill. It will be saved to the `total_amount` database field.')),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bill_number')
                    ->searchable()
                    ->sortable()
                    ->label('Bill No.'),
                Tables\Columns\TextColumn::make('supplier.name') // Display supplier name
                    ->searchable()
                    ->sortable()
                    ->label('Supplier'),
                Tables\Columns\TextColumn::make('bill_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge() // Display status as a badge
                    ->color(fn (string $state): string => match ($state) {
                        'Generated' => 'gray',
                        'Received' => 'success',
                        'Paid' => 'info',
                        'Unpaid' => 'warning',
                        'Cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('INR') // Display as Indian Rupees
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('total_gst_amount') // This column will show the stored GST amount
                    ->money('INR')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->label('Filter by Supplier'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Generated' => 'Generated',
                        'Received' => 'Received',
                        'Paid' => 'Paid',
                        'Unpaid' => 'Unpaid',
                        'Cancelled' => 'Cancelled',
                    ])
                    ->label('Filter by Status'),
                Tables\Filters\Filter::make('bill_date')
                    ->form([
                        DatePicker::make('bill_date_from')->label('From Date'),
                        DatePicker::make('bill_date_to')->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['bill_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('bill_date', '>=', $date),
                            )
                            ->when(
                                $data['bill_date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('bill_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // No direct relation managers needed for now, as stockBatches are handled in the main form repeater
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseBills::route('/'),
            'create' => Pages\CreatePurchaseBill::route('/create'),
            'edit' => Pages\EditPurchaseBill::route('/{record}/edit'),
            'view' => Pages\ViewPurchaseBill::route('/{record}'),
        ];
    }

    /**
     * Override default creation to save total amount and total GST amount
     * after handling stock batches.
     */
public static function mutateFormDataBeforeSave(array $data): array
{
    $calculatedSubtotal = 0;
    $calculatedGstFromItems = 0;

    foreach ($data['stockBatches'] as $item) {
        $quantity = (float)($item['quantity'] ?? 0);
        $purchasePrice = (float)($item['purchase_price'] ?? 0);
        $gstRate = (float)($item['medicine_gst_rate'] ?? 0);
        $discount = (float)($item['discount_percentage'] ?? 0);

        $itemSubtotal = $quantity * $purchasePrice;
        $itemSubtotalAfterDiscount = $itemSubtotal - ($itemSubtotal * ($discount / 100));
        $itemGst = $itemSubtotalAfterDiscount * ($gstRate / 100);

        $calculatedSubtotal += $itemSubtotalAfterDiscount;
        $calculatedGstFromItems += $itemGst;
    }

    $manualGstInput = (float)($data['total_gst_amount'] ?? 0);
    $finalGstAmountToStore = $manualGstInput > 0 ? $manualGstInput : $calculatedGstFromItems;

    $data['total_amount'] = round($calculatedSubtotal + $finalGstAmountToStore, 2);
    $data['total_gst_amount'] = round($finalGstAmountToStore, 2);

    // --- ADD THIS LINE FOR DEBUGGING ---
    dd($data);
    // --- END DEBUGGING LINE ---

    return $data;
}
    /**
     * Override default saving logic for the create and update pages
     * to ensure stock batches are saved correctly and totals are updated.
     */
    public static function afterSave(Forms\ComponentContainer $form, \App\Models\PurchaseBill $record): void
    {
        // The repeater automatically handles saving related models
        // when using ->relationship(). So, stockBatches are saved automatically.
        // We just need to ensure the parent PurchaseBill's total amounts are updated.
        // This is handled by mutateFormDataBeforeSave, which runs before the main record is saved.
    }
}

