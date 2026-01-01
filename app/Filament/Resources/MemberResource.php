<?php

namespace App\Filament\Resources;

use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action as TableAction;
use App\Filament\Resources\MemberResource\Pages\ListMembers;
use App\Filament\Resources\MemberResource\Pages\CreateMember;
use App\Filament\Resources\MemberResource\Pages\EditMember;
use App\Filament\Resources\MemberResource\Pages;
use App\Models\Member;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MembersExport;
use App\Exports\MembersTemplateExport;
use App\Imports\MembersImport;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static string | \UnitEnum | null $navigationGroup = 'Kepegawaian';
    
    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Pegawai';
    protected static ?string $pluralModelLabel = 'Pegawai';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->can('view members');
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->can('view members');
    }

    public static function canView(Model $record): bool
    {
        return auth()->check() && auth()->user()->can('view members');
    }

    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->can('create members');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->check() && auth()->user()->can('edit members');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->check() && auth()->user()->can('delete members');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->check() && auth()->user()->can('delete members');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Data Pribadi')
                            ->icon('heroicon-o-user')
                            ->schema([
                                FileUpload::make('foto')
                                    ->image()
                                    ->avatar()
                                    ->imageEditor()
                                    ->directory('pegawai')
                                    ->columnSpanFull(),

                                TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('nik')
                                    ->label('NIK')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(16)
                                    ->minLength(16),

                                DatePicker::make('tanggal_lahir')
                                    ->label('Tanggal Lahir')
                                    ->native(false)
                                    ->displayFormat('d F Y'),

                                ToggleButtons::make('jenis_kelamin')
                                    ->label('Jenis Kelamin')
                                    ->options([
                                        'L' => 'Laki-laki',
                                        'P' => 'Perempuan',
                                    ])
                                    ->icons([
                                        'L' => 'heroicon-o-user',
                                        'P' => 'heroicon-o-user',
                                    ])
                                    ->inline(),

                                Textarea::make('alamat')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Tabs\Tab::make('Kontak')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                TextInput::make('email')
                                    ->email()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),

                                TextInput::make('whatsapp')
                                    ->label('No. WhatsApp')
                                    ->tel()
                                    ->maxLength(20),
                            ])->columns(2),

                        Tabs\Tab::make('Data Kepegawaian')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                TextInput::make('no_karyawan')
                                    ->label('No. Karyawan')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50),

                                Select::make('instansi_id')
                                    ->label('Instansi')
                                    ->relationship('instansi', 'nama')
                                    ->searchable()
                                    ->preload(),

                                Select::make('posisi_id')
                                    ->label('Posisi')
                                    ->relationship('posisi', 'nama')
                                    ->searchable()
                                    ->preload(),

                                DatePicker::make('tanggal_masuk')
                                    ->label('Tanggal Masuk')
                                    ->native(false)
                                    ->displayFormat('d F Y'),

                                DatePicker::make('tanggal_kontrak_berakhir')
                                    ->label('Kontrak Berakhir')
                                    ->native(false)
                                    ->displayFormat('d F Y'),

                                ToggleButtons::make('status_kepegawaian')
                                    ->label('Status Kepegawaian')
                                    ->options([
                                        'aktif' => 'Aktif',
                                        'nonaktif' => 'Nonaktif',
                                        'cuti' => 'Cuti',
                                        'resign' => 'Resign',
                                    ])
                                    ->icons([
                                        'aktif' => 'heroicon-o-check-circle',
                                        'nonaktif' => 'heroicon-o-x-circle',
                                        'cuti' => 'heroicon-o-pause-circle',
                                        'resign' => 'heroicon-o-arrow-right-on-rectangle',
                                    ])
                                    ->colors([
                                        'aktif' => 'success',
                                        'nonaktif' => 'danger',
                                        'cuti' => 'warning',
                                        'resign' => 'gray',
                                    ])
                                    ->inline()
                                    ->default('aktif'),
                            ])->columns(2),

                        Tabs\Tab::make('Akun')
                            ->icon('heroicon-o-key')
                            ->schema([
                                TextInput::make('password')
                                    ->password()
                                    ->revealable()
                                    ->minLength(8)
                                    ->confirmed()
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->placeholder(fn (string $operation): string => 
                                        $operation === 'edit' ? 'Kosongkan jika tidak ingin mengubah password' : ''
                                    ),

                                TextInput::make('password_confirmation')
                                    ->password()
                                    ->revealable()
                                    ->label('Konfirmasi Password')
                                    ->dehydrated(false)
                                    ->placeholder(fn (string $operation): string => 
                                        $operation === 'edit' ? 'Kosongkan jika tidak ingin mengubah password' : ''
                                    ),

                                Toggle::make('status')
                                    ->label('Status Akun Aktif')
                                    ->default(true),
                            ])->columns(2),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF'),

                TextColumn::make('no_karyawan')
                    ->label('No. Karyawan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('instansi.nama')
                    ->label('Instansi')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('posisi.nama')
                    ->label('Posisi')
                    ->badge()
                    ->color('info'),

                TextColumn::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-phone')
                    ->toggleable(),

                // TextColumn::make('tanggal_kontrak_berakhir')
                //     ->label('Kontrak Berakhir')
                //     ->date('d M Y')
                //     ->sortable()
                //     ->color(fn ($record) => $record->isKontrakAkanBerakhir(30) ? 'danger' : null)
                //     ->icon(fn ($record) => $record->isKontrakAkanBerakhir(30) ? 'heroicon-o-exclamation-triangle' : null),

                TextColumn::make('status_kepegawaian')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'aktif' => 'success',
                        'nonaktif' => 'danger',
                        'cuti' => 'warning',
                        'resign' => 'gray',
                        default => 'gray',
                    }),

                IconColumn::make('status')
                    ->label('Akun')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->trueColor('success')
                    ->falseIcon('heroicon-o-x-circle')
                    ->falseColor('danger')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('instansi_id')
                    ->label('Instansi')
                    ->relationship('instansi', 'nama')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('posisi_id')
                    ->label('Posisi')
                    ->relationship('posisi', 'nama')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status_kepegawaian')
                    ->label('Status')
                    ->options([
                        'aktif' => 'Aktif',
                        'nonaktif' => 'Nonaktif',
                        'cuti' => 'Cuti',
                        'resign' => 'Resign',
                    ]),

                Filter::make('kontrak_hampir_berakhir')
                    ->label('Kontrak Hampir Berakhir')
                    ->query(fn (Builder $query): Builder => $query->kontrakHampirBerakhir(30))
                    ->toggle(),

                TrashedFilter::make(),
            ])
            // ->headerActions([
            //     TableAction::make('export')
            //         ->label('Export Excel')
            //         ->icon('heroicon-o-arrow-down-tray')
            //         ->color('success')
            //         ->action(function () {
            //             return Excel::download(new MembersExport, 'pegawai-' . date('Y-m-d') . '.xlsx');
            //         }),

            //     TableAction::make('downloadTemplate')
            //         ->label('Download Template')
            //         ->icon('heroicon-o-document-arrow-down')
            //         ->color('warning')
            //         ->action(function () {
            //             return Excel::download(new MembersTemplateExport, 'template-import-pegawai.xlsx');
            //         }),

            //     TableAction::make('import')
            //         ->label('Import Excel')
            //         ->icon('heroicon-o-arrow-up-tray')
            //         ->color('info')
            //         ->form([
            //             FileUpload::make('file')
            //                 ->label('File Excel')
            //                 ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
            //                 ->required()
            //                 ->directory('imports'),
            //         ])
            //         ->action(function (array $data) {
            //             try {
            //                 Excel::import(new MembersImport, storage_path('app/public/' . $data['file']));
                            
            //                 Notification::make()
            //                     ->title('Import Berhasil')
            //                     ->body('Data pegawai berhasil diimport.')
            //                     ->success()
            //                     ->send();
            //             } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            //                 $failures = $e->failures();
            //                 $errorMessages = [];
                            
            //                 foreach ($failures as $failure) {
            //                     $errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            //                 }
                            
            //                 Notification::make()
            //                     ->title('Import Gagal')
            //                     ->body(implode("\n", array_slice($errorMessages, 0, 5)))
            //                     ->danger()
            //                     ->persistent()
            //                     ->send();
            //             } catch (\Exception $e) {
            //                 Notification::make()
            //                     ->title('Import Gagal')
            //                     ->body('Terjadi kesalahan: ' . $e->getMessage())
            //                     ->danger()
            //                     ->send();
            //             }
            //         }),
            // ])
            ->headerActions([
                TableAction::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        return Excel::download(new MembersExport, 'pegawai-' . date('Y-m-d') . '.xlsx');
                    }),

                TableAction::make('downloadTemplate')
                    ->label('Download Template')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('warning')
                    ->action(function () {
                        return Excel::download(new MembersTemplateExport, 'template-import-pegawai.xlsx');
                    }),

                TableAction::make('import')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('info')
                    ->form([
                        FileUpload::make('file')
                            ->label('File Excel')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                                'application/vnd.ms-excel',
                            ])
                            ->required()
                            ->disk('local')
                            ->directory('imports'),
                    ])
                    ->action(function (array $data) {
                        try {
                            $filePath = \Illuminate\Support\Facades\Storage::disk('local')->path($data['file']);

                            $import = new MembersImport;
                            Excel::import($import, $filePath);
                            
                            // Hapus file setelah import
                            \Illuminate\Support\Facades\Storage::disk('local')->delete($data['file']);
                            
                            // Tampilkan hasil
                            if ($import->imported > 0) {
                                Notification::make()
                                    ->title('Import Berhasil')
                                    ->body("Berhasil import {$import->imported} data. Dilewati: {$import->skipped}")
                                    ->success()
                                    ->send();
                            } else {
                                $errorMsg = count($import->errors) > 0 
                                    ? implode("\n", array_slice($import->errors, 0, 5))
                                    : 'Tidak ada data yang diimport. Pastikan format Excel sesuai template.';
                                    
                                Notification::make()
                                    ->title('Import Gagal')
                                    ->body($errorMsg)
                                    ->warning()
                                    ->persistent()
                                    ->send();
                            }
                                
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Import Gagal')
                                ->body('Terjadi kesalahan: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListMembers::route('/'),
            'create' => CreateMember::route('/create'),
            'edit' => EditMember::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status_kepegawaian', 'aktif')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}