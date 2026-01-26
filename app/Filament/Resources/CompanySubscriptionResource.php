<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanySubscriptionResource\Pages;
use App\Models\CompanySubscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompanySubscriptionResource extends Resource
{
    protected static ?string $model = CompanySubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
                Forms\Components\Select::make('subscription_id')
                    ->relationship('subscription', 'name')
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->required(),
                Forms\Components\DateTimePicker::make('ends_at'),
                Forms\Components\TextInput::make('stripe_subscription_id')
                    ->maxLength(255),
                Forms\Components\TextInput::make('stripe_invoice_id')
                    ->maxLength(255),
                Forms\Components\TextInput::make('invoice_url')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscription.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('stripe_subscription_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stripe_invoice_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoice_url')
                    ->searchable(),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanySubscriptions::route('/'),
            'create' => Pages\CreateCompanySubscription::route('/create'),
            'edit' => Pages\EditCompanySubscription::route('/{record}/edit'),
        ];
    }
}
