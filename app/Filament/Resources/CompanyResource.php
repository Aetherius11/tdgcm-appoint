<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationGroup = 'System Maintenance';

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(100),
                FileUpload::make('branding_logo')
                    ->label('Branding Logo')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('branding')
                    ->visibility('public')
                    ->imagePreviewHeight('120')
                    ->maxSize(2048)
                    ->helperText('PNG, JPG, SVG — max 2 MB. Appears in all page headers.'),
                Forms\Components\Textarea::make('privacy_policy'),
                Forms\Components\Toggle::make('is_active')
                    ->required(),

                Forms\Components\Section::make('Landing Page Content')
                    ->description('Customize the text and badge image shown on the public booking page.')
                    ->schema([
                        Forms\Components\TextInput::make('landing_headline')
                            ->label('Headline')
                            ->placeholder("You're just one click away from securing your appointment...")
                            ->maxLength(500),
                        FileUpload::make('landing_badge_image')
                            ->label('Badge / Seal Image')
                            ->image()
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->imagePreviewHeight('120')
                            ->maxSize(2048)
                            ->helperText('Replaces the DPO/DPS circle badge. PNG or JPG, max 2 MB.'),
                        Forms\Components\RichEditor::make('landing_body')
                            ->label('Body Text')
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'undo', 'redo'])
                            ->placeholder('Upon registration, the personal information...'),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('branding_logo')
                    ->label('Logo')
                    ->disk('public')
                    ->height(40),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
              ->bulkActions([
               Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
