<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CurrentBalanceResource\Pages;
use App\Filament\Resources\CurrentBalanceResource\RelationManagers;
use App\Models\CurrentBalance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CurrentBalanceResource extends Resource
{
    protected static ?string $model = CurrentBalance::class;

    protected static ?string $navigationIcon = 'heroicon-m-banknotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('balance')
                    ->required()
                    ->numeric()
                    ->disabled(fn () => \DB::table('current_balances')
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
                Tables\Columns\TextColumn::make('balance')
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
            'index' => Pages\CreateCurrentBalance::route('/'),
        ];
    }

    public static function beforeCreate($record)
    {
        $hasTransaction = \DB::table('current_balances')
        ->where('user_id', auth()->id())
        ->exists();

        if ($hasTransaction) {
            throw new \Exception('Balance tidak dapat diubah karena sudah ada record transaksi.');
        }

        $record->user_id = auth()->id();

    }

    public static function shouldRegisterNavigation(): bool
    {
        return !\DB::table('current_balances')
            ->where('user_id', auth()->id())
            ->exists();
    }
}
