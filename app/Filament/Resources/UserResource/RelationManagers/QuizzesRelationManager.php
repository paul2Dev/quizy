<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Pages\UserQuizzes;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Actions\AttachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\Action;
use App\Models\QuizAttempt;


class QuizzesRelationManager extends RelationManager
{
    protected static string $relationship = 'quizzes';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(
                        fn (string $state) : string => match ($state) {
                            'not started' => 'danger',
                            'in progress' => 'warning',
                            'completed' => 'success',
                        }
                    )
                    ->getStateUsing(function (Model $quiz) {
                        // Fetch the status from QuizAttempt based on the quiz and user
                        return UserQuizzes::getQuizAttemptStatus($quiz, $this->ownerRecord->id);
                    }),
                TextColumn::make('pivot.created_at')
                    ->label('Assigned at')
                    ->dateTime(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->recordSelect(
                        fn (Select $select) => $select->placeholder('Select a quiz'),
                    )
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->where('status', 'published'))
                    ->recordSelectSearchColumns(['title', 'description'])
                    ->recordTitle(fn ($record) => $record->title)
                    ->multiple()
                    ->label('Assign Quiz'),
            ])
            ->actions([
                Action::make('view_results')
                        ->label('View Results')
                        ->icon('heroicon-o-eye')
                        ->url(fn (Model $record) => route('filament.dashboard.pages.view-quiz-results', ['quizId' => $record->id, 'userId' => $this->ownerRecord->id]))
                        ->hidden(fn (Model $record) => UserQuizzes::getQuizAttemptStatus($record, $this->ownerRecord->id) === 'not started' || UserQuizzes::getQuizAttemptStatus($record, $this->ownerRecord->id) === 'in progress'),
                Action::make('reset_quiz')
                    ->label('Reset Quiz')
                    ->icon('heroicon-o-arrow-path')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Quiz Attempt')
                    ->modalDescription('Are you sure you want to reset '.$this->ownerRecord->name.' attempt? This will erase '.$this->ownerRecord->name.' previous answers.')
                    ->modalSubmitActionLabel('Yes, Reset')
                    ->modalCancelActionLabel('No, Cancel')
                    ->action(function (Model $record) {
                        $attempt = QuizAttempt::where('quiz_id', $record->id)
                            ->where('user_id', $this->ownerRecord->id)
                            ->first();
                
                        if ($attempt) {
                            $attempt->delete(); // Delete attempt so user can retake quiz
                        }
                    })
                    ->hidden(fn (Model $record) => UserQuizzes::getQuizAttemptStatus($record, $this->ownerRecord->id) === 'not started' || UserQuizzes::getQuizAttemptStatus($record, $this->ownerRecord->id) === 'in progress'),
                
                DetachAction::make()->color('warning'),
            ]);
    }
}
