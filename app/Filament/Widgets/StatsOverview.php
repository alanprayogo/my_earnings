<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\CurrentBalance;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = auth()->id();
        $initialBalance = CurrentBalance::where('user_id', $userId)->value('balance') ?? 0;

        // Total pemasukan dan pengeluaran
        $income = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.is_expense', false)
            ->where('transactions.user_id', $userId)
            ->sum('transactions.amount');

        $expense = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.is_expense', true)
            ->where('transactions.user_id', $userId)
            ->sum('transactions.amount');

        $balance = $initialBalance + ($income - $expense);

        $formatCurrency = function ($amount) {
            return 'IDR ' . number_format($amount, 0, ',', '.');
        };

        // Pemasukan terakhir
        $incomeTransactions = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.is_expense', false)
            ->where('transactions.user_id', $userId)
            ->orderByDesc('transactions.created_at')
            ->limit(2)
            ->get();

        // Pengeluaran terakhir
        $expenseTransactions = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.is_expense', true)
            ->where('transactions.user_id', $userId)
            ->orderByDesc('transactions.created_at')
            ->limit(2)
            ->get();

        // Perubahan pemasukan
        $previousIncome = $incomeTransactions->count() < 2 ? 0 : $incomeTransactions[1]->amount;
        $currentIncome = $incomeTransactions->isNotEmpty() ? $incomeTransactions[0]->amount : 0;
        $incomeChange = $currentIncome - $previousIncome;
        $incomePercentageChange = $previousIncome > 0 ? (($incomeChange / $previousIncome) * 100) : 0;

        // Deskripsi pemasukan
        $incomeDescription = $incomePercentageChange > 0
            ? number_format(abs($incomePercentageChange), 2) . '% Increase'
            : ($incomePercentageChange < 0
                ? number_format(abs($incomePercentageChange), 2) . '% Decrease'
                : 'Sideway');
        $incomeIcon = $incomePercentageChange > 0
            ? 'heroicon-m-arrow-trending-up'
            : ($incomePercentageChange < 0
                ? 'heroicon-m-arrow-trending-down'
                : 'heroicon-m-arrows-right-left');

        // Perubahan pengeluaran
        $previousExpense = $expenseTransactions->count() < 2 ? 0 : $expenseTransactions[1]->amount;
        $currentExpense = $expenseTransactions->isNotEmpty() ? $expenseTransactions[0]->amount : 0;
        $expenseChange = $currentExpense - $previousExpense;
        $expensePercentageChange = $previousExpense > 0 ? (($expenseChange / $previousExpense) * 100) : 0;

        // Deskripsi pengeluaran
        $expenseDescription = $expensePercentageChange > 0
            ? number_format(abs($expensePercentageChange), 2) . '% Increase'
            : ($expensePercentageChange < 0
                ? number_format(abs($expensePercentageChange), 2) . '% Decrease'
                : 'Sideway');
        $expenseIcon = $expensePercentageChange > 0
            ? 'heroicon-m-arrow-trending-up'
            : ($expensePercentageChange < 0
                ? 'heroicon-m-arrow-trending-down'
                : 'heroicon-m-arrows-right-left');

        return [
            Stat::make('Total Pemasukan', $formatCurrency($income))
                ->description($incomeDescription)
                ->descriptionIcon($incomeIcon),
            Stat::make('Total Pengeluaran', $formatCurrency($expense))
                ->description($expenseDescription)
                ->descriptionIcon($expenseIcon),
            Stat::make('Saldo', $formatCurrency($balance))
                // ->description($balanceDescription)
                // ->descriptionIcon($balanceIcon),
        ];
    }
}
