<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Table;
use App\Filament\Tables\UserQuizzesTable;
use Illuminate\Support\Facades\Auth;

class UserQuizzes extends BaseWidget
{
    use UserQuizzesTable;

    protected static ?string $heading = 'My Quizzes';

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->id > 1;
    }

    public function table(Table $table): Table
    {
        return $this->userQuizzesTable($table);
    }
    
}
