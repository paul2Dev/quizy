<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class Students extends BaseWidget
{

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::whereKeyNot(1) // Exclude admin (ID = 1)
                ->withCount([
                    'quizzes as total_quizzes',
                    'quizAttempts as quizzes_in_progress' => fn ($query) => $query->where('status', 'in_progress'),
                    'quizAttempts as quizzes_completed' => fn ($query) => $query->where('status', 'completed'),
                    'quizAttempts as quizzes_not_started' => fn ($query) => $query->where('status', 'not_started'),
                ])
            )
            ->columns([
                TextColumn::make('name')->label('User Name')->sortable()->searchable(),
                TextColumn::make('total_quizzes')
                    ->label('Quizzes Assigned')
                    ->sortable()
                    ->badge()
                    ->color('indigo'),
                TextColumn::make('quizzes_in_progress')
                    ->label('In Progress')
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                TextColumn::make('quizzes_completed')
                    ->label('Completed')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('quizzes_not_started')
                    ->label('Not Started')
                    ->sortable()
                    ->badge()
                    ->color('danger'),
                TextColumn::make('last_login_at')->label('Last Login')->sortable()->dateTime(),
            ])->actions([
                Action::make('edit_user')
                    ->label('Edit User') // Label for the action button
                    ->url(fn (Model $record) => route('filament.dashboard.resources.users.edit', ['record' => $record])) // Redirect to the edit page
                    ->icon('heroicon-o-pencil-square') // Add an icon if you want
            ]);;
    }

    public static function canView(): bool
    {
        return Auth::user()?->is_admin ?? false; // Ensure only admins see the widget
    }
}
