<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Question;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Toggle;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?int $navigationSort = 2;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema(
            static::getFormSchema()
        );
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            TextColumn::make('question_text'),
            TextColumn::make('question_type')
                ->badge()
                ->color('gray'),
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
            Tables\Actions\ReplicateAction::make(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }

    public static function getFormSchema(): array {
        return [
            Forms\Components\Section::make('Question Details')
                ->schema([
                    TextInput::make('question_text')->required(),
                    Select::make('question_type')
                        ->options([
                            'single_choice' => 'Single Choice',
                            'multiple_choice' => 'Multiple Choice',
                            'text_input' => 'Text Input',
                        ])
                        ->required(),
                ])
                ->columns(2),
            
            Forms\Components\Section::make('Question Answers')
                ->schema([
                    Repeater::make('options') // Using the Repeater to add multiple options
                    ->relationship()
                    ->schema([
                        TextInput::make('option_text')->required(),
                        Toggle::make('is_correct')->label('Correct Answer'),
                    ])
                    ->addActionLabel('Add Option')
                ]),
        ];
    }
}
