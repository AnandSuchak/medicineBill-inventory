<?php

namespace App\Filament\Resources; // Corrected: Used '\' for namespace separator

use App\Filament\Resources\MedicineResource\Pages;
use App\Filament\Resources\MedicineResource\RelationManagers;
use App\Models\Medicine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MedicineResource extends Resource
{
    protected static ?string $model = Medicine::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box'; // Icon for medicines

    protected static ?string $navigationGroup = 'Master Data'; // Grouping for navigation

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name') // Corrected: Used '\'
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., Dolo 650mg')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('hsn_code') // Corrected: Used '\'
                    ->label('HSN Code')
                    ->maxLength(20)
                    ->nullable()
                    ->placeholder('e.g., 3004.90.11'),
                Forms\Components\Select::make('unit') // Corrected: Used '\'
                    ->options([
                        'Tablet' => 'Tablet',
                        'Bottle' => 'Bottle',
                        'Box' => 'Box',
                        'Syrup' => 'Syrup',
                        'Injection' => 'Injection',
                        // Add more units as needed
                    ])
                    ->required()
                    ->placeholder('Select Unit'),
                Forms\Components\TextInput::make('pack') // Corrected: Used '\'
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., Strip of 10, Bottle of 100ml'),
                Forms\Components\TextInput::make('gst_rate') // Corrected: Used '\'
                    ->label('GST Rate (%)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100) // GST rate typically won't exceed 100%
                    ->step(0.01)
                    ->suffix('%')
                    ->placeholder('e.g., 5, 12, 18'),
                Forms\Components\TextInput::make('company_name') // Corrected: Used '\'
                    ->label('Manufacturing Company')
                    ->maxLength(255)
                    ->nullable()
                    ->placeholder('e.g., Micro Labs Ltd.'),
                Forms\Components\Textarea::make('description') // Corrected: Used '\'
                    ->maxLength(65535)
                    ->rows(3)
                    ->nullable()
                    ->columnSpanFull()
                    ->placeholder('Detailed description of the medicine.'),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name') // Corrected: Used '\'
                    ->searchable()
                    ->sortable()
                    ->label('Medicine Name'),
                Tables\Columns\TextColumn::make('hsn_code') // Corrected: Used '\'
                    ->label('HSN Code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit') // Corrected: Used '\'
                    ->searchable(),
                Tables\Columns\TextColumn::make('pack') // Corrected: Used '\'
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_name') // Corrected: Used '\'
                    ->label('Manufacturer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gst_rate') // Corrected: Used '\'
                    ->label('GST (%)')
                    ->suffix('%')
                    ->sortable(),
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
            'index' => Pages\ListMedicines::route('/'), // Corrected: Used '\'
            'create' => Pages\CreateMedicine::route('/create'), // Corrected: Used '\'
            'edit' => Pages\EditMedicine::route('/{record}/edit'), // Corrected: Used '\'
        ];
    }
}
