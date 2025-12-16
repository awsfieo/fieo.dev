<?php

namespace App\Filament\Resources\RcmcRawReceipts\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;

class RcmcRawReceiptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Receipt Details')
                    ->schema([
                        TextInput::make('receipt_number')
                            ->label('Receipt Number')
                            ->required(),
                        
                        TextInput::make('receipt_date')
                            ->label('Receipt Date'), // Kept as text input as per migration string type, or use DatePicker if you cast it
                            
                        TextInput::make('company_name')
                            ->label('Company Name')
                            ->columnSpanFull(),
                            
                        TextInput::make('gstin')
                            ->label('GSTIN'),
                            
                        TextInput::make('total_receipt_value')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('₹'),
                    ])->columns(2),
            ]);
    }
}
