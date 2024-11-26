<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BalanceResource\Pages;
use App\Filament\Resources\BalanceResource\RelationManagers;
use App\Models\Balance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BalanceResource extends Resource
{
    protected static ?string $model = Balance::class;

    protected static ?string $navigationIcon = 'heroicon-m-banknotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('current_balance')
                    ->required()
                    ->numeric()
                    ->disabled(fn () => \DB::table('balances')
                    ->where('user_id', auth()->id())
                    ->exists()
                ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_balance')
                    ->numeric()
                    ->sortable(),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\CreateBalance::route('/'),
        ];
    }

    public static function beforeCreate($record)
    {
        $hasTransaction = \DB::table('balances')
        ->where('user_id', auth()->id())
        ->exists();

        if ($hasTransaction) {
            throw new \Exception('Balance tidak dapat diubah karena sudah ada record transaksi.');
        }

        $record->user_id = auth()->id();

    }

    public static function shouldRegisterNavigation(): bool
    {
        return !\DB::table('balances')
            ->where('user_id', auth()->id())
            ->exists();
    }
}
