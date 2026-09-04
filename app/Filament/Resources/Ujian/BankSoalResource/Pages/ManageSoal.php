<?php

namespace App\Filament\Resources\Ujian\BankSoalResource\Pages;

use App\Filament\Resources\Ujian\BankSoalResource;
use App\Imports\SoalsImport;
use App\Models\BankSoal;
use App\Models\Soal;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action as HeaderAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ManageSoal extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = BankSoalResource::class;

    protected static string $view = 'filament.resources.ujian.bank-soal-resource.pages.manage-soal';

    public BankSoal $record;

    public ?array $data = [];

    public function mount(BankSoal $record): void
    {
        $this->record = $record;

        // Ambil semua soal terkait bank soal ini
        $soals = Soal::where('bank_soal_id', $this->record->id)
            ->get()
            ->toArray();

        $this->form->fill([
            'soal_list' => $soals,
        ]);
    }

    // Di dalam ManageSoal.php (jika menggunakan custom page/record)
    public function getRecord(): BankSoal
    {
        return parent::getRecord()->load('soals');
    }

    private static function makeMediaPickerField(string $fieldName, string $label)
    {
        return TextInput::make($fieldName)
            ->label($label)
            ->placeholder('Belum ada foto dipilih')
            ->readOnly()
            ->columnSpanFull()
            ->suffixAction(
                Action::make('bukaModalMedia')
                    ->label('Pilih / Upload')
                    ->icon('heroicon-m-photo')
                    ->color('primary')
                    ->modalHeading('Pustaka Media & Pengunggah')
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalContent(function (Component $component) {
                        return view('filament.modal.media-modal', [
                            'statePath' => $component->getStatePath(),
                        ]);
                    })
            );
    }

    private static function makeViewFieldPicker(string $fieldName)
    {
        return ViewField::make($fieldName . '_preview')
            ->columnSpanFull()
            ->view('filament.modal.media-preview')
            ->viewData([
                // Mengambil state path asli dari TextInput (misal: 'data.cover_gambar_sampul')
                'targetFieldName' => $fieldName,
            ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Repeater::make('soal_list')
                    ->label('')
                    ->itemLabel(function (array $state, string $uuid, Repeater $component): string {
                        // 1. Hitung Nomor Urut berdasarkan UUID
                        $keys = array_keys($component->getState());
                        $number = array_search($uuid, $keys) + 1;

                        // 2. Mapping Label Tipe Soal
                        $mapTipeSoal = [
                            'pilihan_ganda' => 'Pilihan Ganda',
                            'pilihan_ganda_kompleks' => 'PG Kompleks',
                            'menjodohkan' => 'Menjodohkan',
                            'benar_salah' => 'Benar / Salah',
                        ];

                        // 3. Ambil label tipe soal berdasarkan state
                        $jenis = $state['jenis_soal'] ?? 'pilihan_ganda';
                        $labelTipe = $mapTipeSoal[$jenis] ?? 'Tipe Soal';

                        // 4. Hitung Total Nilai dari Repeater pilihan_jawaban
                        $pilihanJawaban = $state['pilihan_jawaban'] ?? [];
                        $totalNilai = array_sum(array_column($pilihanJawaban, 'nilai'));

                        return "Soal No. {$number} ({$labelTipe}) - (Total Nilai: {$totalNilai})";
                    })
                    ->collapsible()
                    ->cloneable()
                    ->collapsed()
                    ->schema([

                        //PILIHAN JENIS SOAL
                        Grid::make(2)->schema([
                            Select::make('jenis_soal')
                                ->label('Tipe Soal')
                                ->options([
                                    'pilihan_ganda' => 'Pilihan Ganda (Satu Jawaban)',
                                    'pilihan_ganda_kompleks' => 'Pilihan Ganda Kompleks (Banyak Jawaban)',
                                    'menjodohkan' => 'Menjodohkan',
                                    'benar_salah' => 'Benar / Salah',
                                ])
                                ->default('pilihan_ganda')
                                ->live() // Agar perubahan langsung direspon form secara komparatif/dinamis
                                ->required(),
                        ]),

                        Grid::make(2)
                            ->schema([
                                RichEditor::make('pertanyaan')
                                    ->label('Pertanyaan / Soal')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'strike',
                                        'orderedList',
                                        'bulletList',
                                        'undo',
                                        'redo',
                                    ])
                                    ->required()
                                    ->columnSpan(1),

                                // Bungkus kolom kanan dalam 1 Grid/Group tersendiri
                                Grid::make(1)
                                    ->schema([
                                        self::makeMediaPickerField('gambar_soal', 'Gambar Soal (Opsional)')
                                            ->nullable(),

                                        self::makeViewFieldPicker('gambar_soal'),
                                    ])
                                    ->columnSpan(1),
                            ]),


                        // PILIHAN JAWABAN
                        // 1. REPEATER UNTUK PILIHAN GANDA (SATU JAWABAN)
                        Section::make('')
                            ->visible(fn(Get $get) => $get('jenis_soal') === 'pilihan_ganda')
                            ->schema([
                                Repeater::make('pilihan_jawaban')
                                    ->label('Opsi Jawaban')
                                    ->itemLabel(function (array $state, string $uuid, Repeater $component): string {
                                        $keys = array_keys($component->getState());
                                        $index = array_search($uuid, $keys);
                                        $huruf = chr(65 + ($index !== false ? $index : 0));

                                        // Status Kunci Jawaban
                                        $isKunci = ! empty($state['is_active']);
                                        $badge = $isKunci ? ' - Kunci ' : '';

                                        // Ambil nilai (default 0 jika belum terisi)
                                        $nilai = $state['nilai'] ?? 0;

                                        // Format posisi informasi nilai ditaruh di bagian paling kanan string
                                        return "Pilihan {$huruf} - (Nilai: {$nilai}) {$badge}";
                                    })
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([

                                        Grid::make(2)
                                            ->schema([
                                                Textarea::make('teks')
                                                    ->label('Pilihan Jawaban')
                                                    ->autosize()
                                                    ->rows(5)
                                                    ->columnSpan(1),

                                                // Bungkus kolom kanan dalam 1 Grid/Group tersendiri
                                                Grid::make(1)
                                                    ->schema([
                                                        self::makeMediaPickerField('gambar_jawaban', 'Gambar Jawaban (Opsional)')
                                                            ->nullable()
                                                            ->columnSpan(1),

                                                        TextInput::make('nilai')
                                                            ->numeric()
                                                            ->default(0)
                                                            ->required()
                                                            ->columnSpan(1),
                                                    ])
                                                    ->columnSpan(1),
                                            ]),

                                        Toggle::make('is_active')
                                            ->label('Kunci Jawaban')
                                            ->default(false),


                                    ])
                                    ->columns(2),
                            ]),

                        // 2. REPEATER UNTUK PILIHAN GANDA KOMPLEKS (BANYAK JAWABAN)
                        Section::make('')
                            ->visible(fn(Get $get) => $get('jenis_soal') === 'pilihan_ganda_kompleks')
                            ->schema([
                                Repeater::make('pilihan_jawaban')
                                    ->label('Opsi Jawaban')
                                    ->itemLabel(function (array $state, string $uuid, Repeater $component): string {
                                        $keys = array_keys($component->getState());
                                        $index = array_search($uuid, $keys);
                                        $huruf = chr(65 + ($index !== false ? $index : 0));

                                        // Status Kunci Jawaban
                                        $isKunci = ! empty($state['is_active']);
                                        $badge = $isKunci ? ' - Kunci ' : '';

                                        // Ambil nilai (default 0 jika belum terisi)
                                        $nilai = $state['nilai'] ?? 0;

                                        // Format posisi informasi nilai ditaruh di bagian paling kanan string
                                        return "Pilihan {$huruf} - (Nilai: {$nilai}) {$badge}";
                                    })
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([

                                        Grid::make(2)
                                            ->schema([
                                                Textarea::make('teks')
                                                    ->label('Pilihan Jawaban')
                                                    ->autosize()
                                                    ->rows(5),

                                                // Bungkus kolom kanan dalam 1 Grid/Group tersendiri
                                                Grid::make(1)
                                                    ->schema([
                                                        self::makeMediaPickerField('gambar_jawaban', 'Gambar Jawaban (Opsional)')
                                                            ->nullable(),

                                                        TextInput::make('nilai')
                                                            ->label('Nilai Poin')
                                                            ->numeric()
                                                            ->default(0)
                                                            ->required(),
                                                    ])
                                                    ->columnSpan(1),
                                            ]),

                                        Toggle::make('is_active')
                                            ->label('Termasuk Kunci')
                                            ->default(false),
                                    ])
                                    ->columns(1),
                            ]),

                        // 3. REPEATER UNTUK BENAR / SALAH
                        Section::make('')
                            ->visible(fn(Get $get) => $get('jenis_soal') === 'benar_salah')
                            ->schema([
                                Repeater::make('pilihan_jawaban')
                                    ->label('Opsi Pilihan')
                                    ->itemLabel(function (array $state, string $uuid, Repeater $component): string {
                                        $keys = array_keys($component->getState());
                                        $index = array_search($uuid, $keys);
                                        $huruf = chr(65 + ($index !== false ? $index : 0));

                                        // Status Kunci Jawaban
                                        $isKunci = ! empty($state['is_active']);
                                        $badge = $isKunci ? ' - Kunci ' : '';

                                        // Ambil nilai (default 0 jika belum terisi)
                                        $nilai = $state['nilai'] ?? 0;

                                        // Format posisi informasi nilai ditaruh di bagian paling kanan string
                                        return "Pilihan {$huruf} - (Nilai: {$nilai}) {$badge}";
                                    })
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Textarea::make('teks')
                                                    ->label('Pilihan Jawaban')
                                                    ->autosize()
                                                    ->rows(5),

                                                // Bungkus kolom kanan dalam 1 Grid/Group tersendiri
                                                Grid::make(1)
                                                    ->schema([
                                                        self::makeMediaPickerField('gambar_jawaban', 'Gambar Jawaban (Opsional)')
                                                            ->nullable(),

                                                        TextInput::make('nilai')
                                                            ->numeric()
                                                            ->default(0)
                                                            ->required(),
                                                    ])
                                                    ->columnSpan(1),
                                            ]),

                                        Toggle::make('is_active')
                                            ->label('Kunci Jawaban')
                                            ->default(false),
                                    ])
                                    ->columns(1),
                            ]),

                        // 4. REPEATER UNTUK MENJODOHKAN
                        Section::make('Pasangan Menjodohkan')
                            ->visible(fn(Get $get) => $get('jenis_soal') === 'menjodohkan')
                            ->schema([
                                Repeater::make('pilihan_jawaban')
                                    ->label('Daftar Pasangan')
                                    ->itemLabel(function (array $state, string $uuid, Repeater $component): string {
                                        $keys = array_keys($component->getState());
                                        $index = array_search($uuid, $keys);
                                        $number = ($index !== false ? $index : 0) + 1;

                                        // Ambil nilai poin (default 1 jika belum terisi)
                                        $nilai = $state['nilai'] ?? 1;

                                        return "Pertanyaan {$number}  —  [Nilai: {$nilai}]";
                                    })
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        // Sisi Kiri: Teks & Gambar dibungkus dalam 1 Group (Mengambil 2 kolom)
                                        Group::make([
                                            Textarea::make('kunci')
                                                ->label('Sisi Kiri (Pernyataan / Soal)')
                                                ->placeholder('Contoh: Ibu Kota Indonesia')
                                                ->autosize()
                                                ->rows(2),

                                            self::makeMediaPickerField('kunci_gambar', 'Gambar Pernyataan (Opsional)')
                                                ->nullable(),
                                        ])
                                            ->columnSpan(2), // Group ini memakan 2 porsi grid

                                        // Sisi Kanan: Pasangan Jawaban (Mengambil 2 kolom)
                                        Textarea::make('nilai_pasangan')
                                            ->label('Sisi Kanan (Pasangan / Jawaban Benar)')
                                            ->placeholder('Contoh: Jakarta')
                                            ->autosize()
                                            ->rows(5)
                                            ->required()
                                            ->columnSpan(2), // Memakan 2 porsi grid

                                        // Nilai Poin (Mengambil 1 kolom)
                                        TextInput::make('nilai')
                                            ->label('Nilai Poin')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->columnSpan(1), // Memakan 1 porsi grid
                                    ])
                                    ->columns(5) // Total grid luar diset 5 kolom
                            ]),

                    ])
                    ->addActionLabel('Tambah Soal Baru')
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $formData = $this->form->getState();

        // Hapus seluruh soal lama pada Bank Soal ini lalu simpan ulang
        Soal::where('bank_soal_id', $this->record->id)->delete();

        if (!empty($formData['soal_list'])) {
            foreach ($formData['soal_list'] as $item) {
                $jenisSoal = $item['jenis_soal'] ?? 'pilihan_ganda';
                $pilihanJawabanRaw = $item['pilihan_jawaban'] ?? [];

                $pilihanJawaban = [];
                $kunciJawabanTeks = null;
                $bobotNilai = 0;

                if ($jenisSoal === 'pilihan_ganda') {
                    // Formatting array pilihan ganda
                    $pilihanJawaban = collect($pilihanJawabanRaw)
                        ->map(fn($jawaban) => [
                            'teks'           => $jawaban['teks'] ?? '',
                            'gambar_jawaban' => $jawaban['gambar_jawaban'] ?? null,
                            'nilai'          => (int) ($jawaban['nilai'] ?? 0),
                            'is_active'      => (bool) ($jawaban['is_active'] ?? false),
                        ])
                        ->toArray();

                    // Hanya ambil 1 kunci jawaban
                    $kunci = collect($pilihanJawaban)->firstWhere('is_active', true);
                    $kunciJawabanTeks = $kunci['teks'] ?? null;
                    $bobotNilai = (int) ($kunci['nilai'] ?? 0);
                } elseif ($jenisSoal === 'pilihan_ganda_kompleks') {
                    // Formatting array pilihan ganda kompleks
                    $pilihanJawaban = collect($pilihanJawabanRaw)
                        ->map(fn($jawaban) => [
                            'teks'           => $jawaban['teks'] ?? '',
                            'gambar_jawaban' => $jawaban['gambar_jawaban'] ?? null,
                            'nilai'          => (int) ($jawaban['nilai'] ?? 0),
                            'is_active'      => (bool) ($jawaban['is_active'] ?? false),
                        ])
                        ->toArray();

                    // Ambil SEMUA kunci jawaban
                    $kunciList = collect($pilihanJawaban)->where('is_active', true);
                    $kunciJawabanTeks = $kunciList->pluck('teks')->implode('; ');
                    $bobotNilai = (int) $kunciList->sum('nilai');
                } elseif ($jenisSoal === 'benar_salah') {
                    // Formatting array pilihan Benar / Salah persis seperti pilihan ganda
                    $pilihanJawaban = collect($pilihanJawabanRaw)
                        ->map(fn($jawaban) => [
                            'teks'           => $jawaban['teks'] ?? '',
                            'gambar_jawaban' => $jawaban['gambar_jawaban'] ?? null,
                            'nilai'          => (int) ($jawaban['nilai'] ?? 0),
                            'is_active'      => (bool) ($jawaban['is_active'] ?? false),
                        ])
                        ->toArray();

                    // Mengambil 1 opsi yang ditandai is_active sebagai kunci jawaban
                    $kunci = collect($pilihanJawaban)->firstWhere('is_active', true);
                    $kunciJawabanTeks = $kunci['teks'] ?? null;
                    $bobotNilai = (int) ($kunci['nilai'] ?? 0);
                } elseif ($jenisSoal === 'menjodohkan') {
                    // Clean & Petakan khusus struktur Menjodohkan (kunci => nilai_pasangan)
                    $pilihanJawaban = collect($pilihanJawabanRaw)
                        ->map(fn($jawaban) => [
                            'kunci'          => $jawaban['kunci'] ?? '',
                            'kunci_gambar'   => $jawaban['kunci_gambar'] ?? null,
                            'nilai_pasangan' => $jawaban['nilai_pasangan'] ?? '',
                            'nilai'          => (int) ($jawaban['nilai'] ?? 0),
                        ])
                        ->toArray();

                    // Format kunci jawaban teks (misal: "Ibu Kota Indonesia -> Jakarta; Presiden RI -> Jokowi")
                    $kunciJawabanTeks = collect($pilihanJawaban)
                        ->map(fn($jwb) => ($jwb['kunci'] ?? '') . ' -> ' . ($jwb['nilai_pasangan'] ?? ''))
                        ->implode('; ');

                    // Total bobot nilai dari seluruh pasangan
                    $bobotNilai = (int) collect($pilihanJawaban)->sum('nilai');
                }

                Soal::create([
                    'bank_soal_id'    => $this->record->id,
                    'jenis_soal'      => $jenisSoal,
                    'pertanyaan'      => $item['pertanyaan'],
                    'gambar_soal'     => $item['gambar_soal'] ?? null,
                    'pilihan_jawaban' => $pilihanJawaban,
                    'kunci_jawaban'   => $kunciJawabanTeks,
                    'bobot_nilai'     => $bobotNilai,
                ]);
            }
        }

        Notification::make()
            ->title('Berhasil')
            ->body('Seluruh soal berhasil disimpan.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            HeaderAction::make('importExcel')
                ->label('Import Excel')
                ->icon('heroicon-m-arrow-up-tray')
                ->color('success')
                ->form([
                    FileUpload::make('file_excel')
                        ->label('Pilih File Excel (.xlsx / .xls)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    // Ambil path file secara dinamis dari disk storage Filament
                    $filePath = Storage::disk('public')->path($data['file_excel']);

                    // Jika file tidak ditemukan di disk public, coba path relatif langsung
                    if (!file_exists($filePath)) {
                        $filePath = storage_path('app/public/' . $data['file_excel']);
                    }

                    $import = new SoalsImport();
                    Excel::import($import, $filePath);

                    $importedSoal = $import->getImportedData();

                    if (empty($importedSoal)) {
                        Notification::make()
                            ->title('Import Gagal')
                            ->body('File terbaca, namun tidak ada data soal yang sesuai. Pastikan baris ke-2 (Baris Data) terisi dengan benar.')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Merge ke state form repeater
                    $currentSoal = $this->data['soal_list'] ?? [];

                    // Gabungkan array
                    $this->form->fill([
                        'soal_list' => array_merge($currentSoal, $importedSoal),
                    ]);

                    Notification::make()
                        ->title('Import Berhasil')
                        ->body(count($importedSoal) . ' soal berhasil dimasukkan ke form.')
                        ->success()
                        ->send();
                }),

            HeaderAction::make('viewSoal')
                ->label('View Soal')
                ->icon('heroicon-m-eye')
                ->color('info')
                ->url(fn() => BankSoalResource::getUrl('view-soal', ['record' => $this->record->id])),
        ];
    }
}
