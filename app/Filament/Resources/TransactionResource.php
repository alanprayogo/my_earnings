<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $isDisabled = !\DB::table('balances') // Balikkan logika dengan `!`
        ->where('user_id', auth()->id())
        ->exists();

        return $form
            ->schema(
                $isDisabled ? [Forms\Components\Placeholder::make('message')
                ->content('Form ini tidak dapat diubah karena balance belum diatur.')]
            : [
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('transaction_date')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Name')
                    ->description(fn (Transaction $record): string => $record->name)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('category.is_expense')
                    ->label('Transaction')
                    ->boolean()
                    ->trueIcon('hugeicons-money-send-02')
                    ->falseIcon('hugeicons-money-receive-02')
                    ->trueColor('danger')
                    ->falseColor('success'),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return \DB::table('balances')
            ->where('user_id', auth()->id())
            ->exists();
    }

    public static function beforeCreate($record)
    {
        $hasTransaction = !\DB::table('balances')
        ->where('user_id', auth()->id())
        ->exists();

        if ($hasTransaction) {
            throw new \Exception('Transaksi tidak dapat diubah karena balance  belum diatur.');
        }

        $record->user_id = auth()->id();
    }

    protected function afterCreate($record): void
    {
        $userId = $record->user_id;
        $transactionName = $record->name;
        $amount = $record->amount;

        // Simpan transaksi
        $transactionId = DB::table('transactions')->insertGetId([
            'user_id' => $userId,
            'name' => $transactionName,
            'amount' => $amount,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Perbarui saldo
        $currentBalance = DB::table('balances')->where('user_id', $userId)->first(); // Ganti nama tabel
        $newBalance = $currentBalance->current_balance + $amount; // Penyesuaian sesuai jenis transaksi
            DB::table('balances')->updateOrInsert( // Ganti nama tabel
                ['user_id' => $userId],
                [
                    'transaction_id' => $transactionId,
                    'previous_balance' => $currentBalance->current_balance,
                    'current_balance' => $newBalance,
                    'updated_at' => now(),
                ]
            );
    }
}
