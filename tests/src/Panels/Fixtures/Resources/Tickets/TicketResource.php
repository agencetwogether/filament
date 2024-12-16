<?php

namespace Filament\Tests\Panels\Fixtures\Resources\Tickets;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tests\Models\Ticket;
use Filament\Tests\Panels\Fixtures\Resources\Tickets\Pages\CreateTicket;
use Filament\Tests\Panels\Fixtures\Resources\Tickets\Pages\EditTicket;
use Filament\Tests\Panels\Fixtures\Resources\Tickets\Pages\ListTickets;
use Filament\Tests\Panels\Fixtures\Resources\Tickets\Pages\ViewTicket;
use Filament\Tests\Panels\Fixtures\Resources\Tickets\Schemas\TicketForm;
use Filament\Tests\Panels\Fixtures\Resources\Tickets\Schemas\TicketInfolist;
use Filament\Tests\Panels\Fixtures\Resources\Tickets\Tables\TicketsTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return TicketForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TicketInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketsTable::configure($table);
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
            'index' => ListTickets::route('/'),
            'create' => CreateTicket::route('/create'),
            'view' => ViewTicket::route('/{record}'),
            'edit' => EditTicket::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
