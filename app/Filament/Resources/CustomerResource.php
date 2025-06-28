<?php

namespace App\Filament\Resources; // Corrected: Used '\' for namespace separator

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Closure; // This 'use Closure;' statement MUST be placed AFTER the namespace declaration.

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users'; // Icon for customers

    protected static ?string $navigationGroup = 'Master Data'; // Grouping for navigation

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('shop_name') // Corrected: Used '\'
                    ->label('Customer Shop Name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->placeholder('e.g., Sharma Medicals')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('email') // Corrected: Used '\'
                    ->email()
                    ->maxLength(255)
                    ->nullable()
                    ->placeholder('customer@example.com'),
                Forms\Components\TextInput::make('phone') // Corrected: Used '\'
                    ->tel()
                    ->maxLength(20)
                    ->nullable()
                    ->placeholder('e.g., +91 9876543210'),
                Forms\Components\TextInput::make('gst_number') // Corrected: Used '\'
                    ->label('GST Number')
                    ->maxLength(15)
                    ->unique(ignoreRecord: true)
                    ->nullable()
                    ->placeholder('e.g., 22AAAAA0000A1Z5')
                    // Conditional validation: require GST if PAN is not present
                    ->rules([
                        fn (Forms\Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) { // Corrected: Used '\' for Forms\Get
                            if (empty($value) && empty($get('pan_number'))) {
                                $fail("Either GST Number or PAN Number is required.");
                            }
                        },
                    ]),
                Forms\Components\TextInput::make('pan_number') // Corrected: Used '\'
                    ->label('PAN Number')
                    ->maxLength(10) // Standard PAN length is 10 characters
                    ->unique(ignoreRecord: true)
                    ->nullable()
                    ->placeholder('e.g., ABCDE1234F')
                    // Conditional validation: require PAN if GST is not present
                    ->rules([
                        fn (Forms\Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) { // Corrected: Used '\' for Forms\Get
                            if (empty($value) && empty($get('gst_number'))) {
                                $fail("Either GST Number or PAN Number is required.");
                            }
                        },
                    ]),
                Forms\Components\Textarea::make('address') // Corrected: Used '\'
                    ->maxLength(65535)
                    ->rows(3)
                    ->nullable()
                    ->columnSpanFull()
                    ->placeholder('Enter full address, city, state, pin code'),
            ])->columns(2); // Arrange fields in 2 columns
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('shop_name') // Corrected: Used '\'
                    ->searchable()
                    ->sortable()
                    ->label('Shop Name'),
                Tables\Columns\TextColumn::make('phone') // Corrected: Used '\'
                    ->searchable(),
                Tables\Columns\TextColumn::make('gst_number') // Corrected: Used '\'
                    ->label('GST No.')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('pan_number') // Corrected: Used '\'
                    ->label('PAN No.')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('email') // Corrected: Used '\'
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at') // Corrected: Used '\'
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at') // Corrected: Used '\'
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(), // Corrected: Used '\'
                Tables\Actions\DeleteAction::make(), // Corrected: Used '\'
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([ // Corrected: Used '\'
                    Tables\Actions\DeleteBulkAction::make(), // Corrected: Used '\'
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(), // Corrected: Used '\'
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'), // Corrected: Used '\'
            'create' => Pages\CreateCustomer::route('/create'), // Corrected: Used '\'
            'edit' => Pages\EditCustomer::route('/{record}/edit'), // Corrected: Used '\'
        ];
    }
}
