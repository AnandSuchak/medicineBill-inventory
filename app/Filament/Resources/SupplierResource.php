<?php

namespace App\Filament\Resources; // <<<-- THIS LINE IS CRUCIAL. It must be 'Resources' with an 's'.

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront'; // A relevant icon

    protected static ?string $navigationGroup = 'Master Data'; // Grouping for navigation

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true) // Ensure supplier name is unique, ignore self on edit
                    ->placeholder('Enter Supplier Name')
                    ->columnSpanFull(), // Take full width of the form column
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->nullable()
                    ->placeholder('supplier@example.com'),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(20)
                    ->nullable()
                    ->placeholder('e.g., +91 9876543210'),
                Forms\Components\TextInput::make('gst_number')
                    ->label('GST Number')
                    ->required()
                    ->maxLength(15) // Standard GSTIN length is 15 characters
                    ->unique(ignoreRecord: true) // Ensure GST number is unique
                    ->placeholder('e.g., 22AAAAA0000A1Z5'),
                Forms\Components\TextInput::make('dln')
                    ->label('Drug License Number (DLN)')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true) // DLN should likely be unique
                    ->placeholder('e.g., XYZ/DLN/12345'),
                Forms\Components\Textarea::make('address')
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Supplier Name'),
                Tables\Columns\TextColumn::make('gst_number')
                    ->searchable()
                    ->label('GST No.'),
                Tables\Columns\TextColumn::make('dln')
                    ->searchable()
                    ->label('DLN'),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Add delete action
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
