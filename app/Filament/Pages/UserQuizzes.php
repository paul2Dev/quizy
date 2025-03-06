<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use App\Filament\Tables\UserQuizzesTable;

class UserQuizzes extends Page implements HasTable
{
    use InteractsWithTable, UserQuizzesTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard';
    protected static string $view = 'filament.pages.user-quizzes';
    protected static ?string $navigationLabel = 'My Quizzes';
    protected static ?string $title = 'My Assigned Quizzes';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->id > 1;
    }

    public function table(Table $table): Table
    {
        return $this->userQuizzesTable($table);
    }
}
