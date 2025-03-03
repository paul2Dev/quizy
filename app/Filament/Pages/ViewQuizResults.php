<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\Auth;

class ViewQuizResults extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.view-quiz-results';

    protected static bool $shouldRegisterNavigation = false;

    public ?Quiz $quiz = null;
    public ?QuizAttempt $attempt = null;
    public array $answers = [];
    public array $correctAnswers = [];

    public function mount(): void
    {
        $quizId = request()->query('quizId'); // Retrieve quizId from URL
        $this->quiz = Quiz::with('questions.options')->findOrFail($quizId);
        $this->attempt = QuizAttempt::where(['user_id' => Auth::id(), 'quiz_id' => $quizId])->first();

        if (!$this->attempt) {
            abort(404);
        }

        // Prepopulate answers from the attempt
        $this->answers = $this->attempt->answers ?? [];

        // Store correct answers
        foreach ($this->quiz->questions as $question) {
            $this->correctAnswers[$question->id] = $question->options->where('is_correct', true)->pluck('id')->toArray();
        }
    }

    protected function getFormSchema(): array
{
    return collect($this->quiz->questions)->map(function ($question) {
        $userAnswer = $this->answers[$question->id] ?? null;
        $correctAnswers = $this->correctAnswers[$question->id];

        // Modify options to append "- Correct Answer" to the correct choices
        $options = $question->options->mapWithKeys(function ($option) use ($correctAnswers) {
            $label = $option->option_text;
            if (in_array($option->id, $correctAnswers)) {
                $label .= " - Correct Answer ✅"; // Append correct answer text
            }
            return [$option->id => $label];
        });

        if ($question->question_type === 'single_choice') {
            return Forms\Components\Radio::make("answers.{$question->id}")
                ->label($question->question_text)
                ->options($options)
                ->default($userAnswer)
                ->disabled();
        }
        
        if ($question->question_type === 'multiple_choice') {
            return Forms\Components\CheckboxList::make("answers.{$question->id}")
                ->label($question->question_text)
                ->options($options)
                ->default($userAnswer)
                ->disabled();
        }

        return Forms\Components\Textarea::make("answers.{$question->id}")
            ->label($question->question_text)
            ->default($userAnswer)
            ->disabled();
    })->toArray();
}
}
