<?php

namespace App\Filament\Resources\MemberResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use App\Filament\Resources\MemberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resetDevice')
                ->label('Reset Device')
                ->icon('heroicon-o-device-phone-mobile')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Reset Device')
                ->modalDescription(fn () => $this->record->identity_name 
                    ? "Yakin ingin reset device \"{$this->record->identity_name}\" dari akun {$this->record->name}?" 
                    : "Akun ini belum terdaftar di device manapun."
                )
                ->modalSubmitActionLabel('Ya, Reset')
                ->visible(fn () => $this->record->identity !== null)
                ->action(function () {
                    $this->record->update([
                        'identity' => null,
                        'identity_name' => null,
                        'device_name' => null,
                    ]);

                    Notification::make()
                        ->title('Device Berhasil Direset')
                        ->body("Device untuk {$this->record->name} telah direset. Pegawai dapat login dari perangkat baru.")
                        ->success()
                        ->send();
                }),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle password - jika kosong, hapus dari data
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = bcrypt($data['password']);
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}