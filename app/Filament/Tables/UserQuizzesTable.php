<?php

namespace App\Filament\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait UserQuizzesTable
{
    public function userQuizzesTable(Table $table): Table
    {
        return $table
            ->query(fn () => Quiz::whereHas('users', fn (Builder $query) => 
                $query->where('users.id', Auth::id())
            ))
            ->columns([
                TextColumn::make('title')->sortable()->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'not started' => 'danger',
                        'in progress' => 'warning',
                        'completed' => 'success',
                    })
                    ->getStateUsing(fn (Quiz $quiz) => $this->getQuizAttemptStatus($quiz, Auth::id())),
                TextColumn::make('created_at')->label('Assigned At')->dateTime(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('take_quiz')
                    ->label('Take Quiz')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->url(fn (Model $record) => route('filament.dashboard.pages.take-quiz', ['quizId' => $record->id]))
                    ->hidden(fn (Model $record) => $this->getQuizAttemptStatus($record, Auth::id()) !== 'not started' && $this->getQuizAttemptStatus($record, Auth::id()) !== 'in progress'),

                Action::make('view_results')
                    ->label('View Results')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Model $record) => route('filament.dashboard.pages.view-quiz-results', ['quizId' => $record->id]))
                    ->hidden(fn (Model $record) => $this->getQuizAttemptStatus($record, Auth::id()) === 'not started' || $this->getQuizAttemptStatus($record, Auth::id()) === 'in progress'),
            ]);
    }

    public static function getQuizAttemptStatus(Model $quiz, $user_id): string
    {
        $quizAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user_id)
            ->latest()
            ->first();

        return $quizAttempt ? Str::replace('_', ' ', $quizAttempt->status) : 'not started';
    }
}
