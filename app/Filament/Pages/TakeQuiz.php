<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;
use Illuminate\Validation\ValidationException;

class TakeQuiz extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.take-quiz';

    protected static bool $shouldRegisterNavigation = false;

    public ?Quiz $quiz = null;
    public ?QuizAttempt $attempt = null;

    public array $answers = [];

    public function mount(): void
    {
        $quizId = request()->query('quizId'); // Retrieve quizId from the URL

        if(!auth()->user()->quizzes()->where('quiz_id', $quizId)->first()) {
            abort(404);
        }

        $this->quiz = Quiz::with('questions.options')->findOrFail($quizId);
        $this->attempt = QuizAttempt::firstOrCreate(
            ['user_id' => Auth::id(), 'quiz_id' => $quizId],
            ['status' => 'in_progress']
        );

        if($this->attempt->status === 'completed') {
            $this->redirect('/dashboard/user-quizzes');
        }
        
        foreach($this->quiz->questions as $question) {
            if($question->question_type === 'multiple_choice') {
                $this->answers[$question->id] = [];
                continue;
            }

            if($question->question_type === 'single_choice') {
                $this->answers[$question->id] = null;
                continue;
            }
            
            $this->answers[$question->id] = null;
        }

    }

    protected function getFormSchema(): array
    {
        return collect($this->quiz->questions)->map(function ($question) {
            if ($question->question_type === 'single_choice') {
                return Forms\Components\Radio::make("answers.{$question->id}")
                    ->label($question->question_text)
                    ->options($question->options->pluck('option_text', 'id'))
                    ->required();
            }
            
            if ($question->question_type === 'multiple_choice') {
                return Forms\Components\CheckboxList::make("answers.{$question->id}")
                    ->label($question->question_text)
                    ->options($question->options->pluck('option_text', 'id'))
                    ->required();
            }
            

            return Forms\Components\Textarea::make("answers.{$question->id}")
                ->label($question->question_text)
                ->required();
        })->toArray();
    }

    // Action to handle submission
    protected function getActions(): array
    {
        return [
            Action::make('submit')
                ->label('Submit Quiz')
                ->action(function(Action $action) {
                    try {
                        $this->submit();
                    } catch (ValidationException $e) {
                        $this->setErrorBag($e->validator->errors());
                    }
                })
                ->requiresConfirmation() // Show confirmation dialog before submitting
                ->modalHeading('Did you finish the quiz?')
                ->modalDescription('Are you sure you want to submit your answers?')
                ->modalSubmitActionLabel('Yes, i am done!')
                ->modalCancelActionLabel('No, let me check again!')
                
        ];
    }

    protected function isFormValid() 
    {
        try {
            $this->form->validate();
            return true;
        } catch (ValidationException $e) {
            return false;
        }
    }

    public function submit(): void
    {
        $this->attempt->answers = $this->form->getState()['answers'];
        $this->attempt->status = 'completed';
        $this->attempt->save();

        $this->redirect('/dashboard/user-quizzes'); // Redirect to dashboard after completion
    }
}
