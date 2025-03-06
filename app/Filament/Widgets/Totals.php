<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;

class Totals extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Quizzes', Quiz::count())
                ->icon('heroicon-o-clipboard')
                ->color('success'),
            Stat::make('Total Students', User::where('id', '!=', 1)->count())
                ->icon('heroicon-o-users')
                ->color('success'),
            Stat::make('Completed Quizzes', QuizAttempt::where('status', 'completed')->count())
                ->icon('heroicon-o-document-check')
                ->color('success'),
            Stat::make('In Progress Quizzes', QuizAttempt::where('status', 'in_progress')->count())
                ->icon('heroicon-o-clock')
                ->color('success'),
        ];
    }

    
    public static function canView(): bool
    {
        return Auth::user()?->id === 1 ?? false; // Ensure only admin sees the widget
    }
}
