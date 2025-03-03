<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use App\Models\QuizAttempt;
use Illuminate\Support\Str;

class UserQuizzes extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard';
    protected static string $view = 'filament.pages.user-quizzes';
    protected static ?string $navigationLabel = 'My Quizzes';
    protected static ?string $title = 'My Assigned Quizzes';

    public static function shouldRegisterNavigation(): bool
    {
        // Hide the page from admins (assuming admin has ID 1)
        return Auth::user()?->id > 1;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Quiz::whereHas('users', fn (Builder $query) => 
                $query->where('users.id', Auth::id())
            ))
            ->columns([
                TextColumn::make('title')->sortable()->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(
                        fn (string $state) : string => match ($state) {
                            'not started' => 'danger',
                            'in progress' => 'warning',
                            'completed' => 'success',
                        }
                    )
                    ->getStateUsing(function (Quiz $quiz) {
                        // Fetch the status from QuizAttempt based on the quiz and user
                        return $this->getQuizAttemptStatus($quiz);
                    }),
                TextColumn::make('created_at')->label('Assigned At')->dateTime(),
            ])->actions([
                // Take Quiz Action - Shown when status is 'not started' or 'in progress'
                Action::make('take_quiz')
                    ->label('Take Quiz')
                    ->icon('heroicon-o-play')
                    ->url(fn (Model $record) => route('filament.dashboard.pages.take-quiz', ['quizId' => $record->id]))
                    ->hidden(fn (Model $record) => $this->getQuizAttemptStatus($record) !== 'not started' && $this->getQuizAttemptStatus($record) !== 'in progress'),

                // View Results Action - Shown when status is 'completed' or other status
                Action::make('view_results')
                    ->label('View Results')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Model $record) => route('filament.dashboard.pages.view-quiz-results', ['quizId' => $record->id]))
                    ->hidden(fn (Model $record) => $this->getQuizAttemptStatus($record) === 'not started' || $this->getQuizAttemptStatus($record) === 'in progress'),
            ]);
    }

    private function getQuizAttemptStatus(Model $quiz): string
    {
        // Fetch the status from QuizAttempt based on the quiz and user
        $quizAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', Auth::id())
            ->latest() // You can modify this to choose the relevant attempt (e.g., latest or completed)
            ->first();

        // Return status or 'Not Attempted' if no attempt exists
        return $quizAttempt ? Str::replace('_', ' ', $quizAttempt->status) : 'not started';
    }
}
