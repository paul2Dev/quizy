<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizResource\Pages;
use App\Models\Quiz;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\QuestionResource;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard';

    protected static ?int $navigationSort = 1;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make('Quiz Details')
                ->schema([
                    TextInput::make('title')->required(),
                    Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'published' => 'Published',
                            'closed' => 'Closed',
                        ])
                        ->required(),
                    Textarea::make('description')->columnSpan(2),
                ])
                ->columns(2),
            Forms\Components\Section::make('Questions')
                ->schema([
                    Select::make('questions')
                    ->multiple()
                    ->relationship('questions', 'question_text')
                    ->searchable()
                    ->preload()
                    ->label('')
                    ->createOptionForm(QuestionResource::getFormSchema())
                ])
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            TextColumn::make('title')
                ->description(fn (Quiz $record): string => $record->description),
            TextColumn::make('status')
                ->badge()
                ->color(
                    fn (string $state) : string => match ($state) {
                        'draft' => 'warning',
                        'published' => 'success',
                        'closed' => 'danger',
                    }
                ),
            TextColumn::make('created_at')
                ->label('Created at')
                ->dateTime()
                ->badge()
                ->color('indigo'),
        ])
        ->defaultSort('id', 'desc')
        ->filters([
            Tables\Filters\TrashedFilter::make(),
        ])
        ->filtersTriggerAction(function ($action) {
            return $action->button()->label('Filters');
        })
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\ViewAction::make(),
            Tables\Actions\DeleteAction::make(),
            Tables\Actions\ForceDeleteAction::make(),
            Tables\Actions\RestoreAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
            ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizzes::route('/'),
            'create' => Pages\CreateQuiz::route('/create'),
            'edit' => Pages\EditQuiz::route('/{record}/edit'),
        ];
    }
}

