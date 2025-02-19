<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Actions\AttachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Builder;

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
                    ->color(fn (string $state) => match ($state) {
                        'draft' => 'warning',
                        'published' => 'success',
                        'closed' => 'danger',
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
            ])
            ->actions([
                DetachAction::make(),
            ]);
    }
}
