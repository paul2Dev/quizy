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
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->label('Assigned At')->dateTime(),
            ]);
    }
}
