<?php

namespace App\Filament\Resources\Ujian\BankSoalResource\Pages;

use App\Filament\Resources\Ujian\BankSoalResource;
use App\Models\BankSoal;
use App\Models\SoalUjian;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class KelolaSoal extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = BankSoalResource::class;

    protected static string $view = 'filament.resources.ujian.bank-soal-resource.pages.kelola-soal';

    public BankSoal $record;
    public ?array $data = [];

    // Tambahkan type-hint BankSoal pada parameter $record
    public function mount(BankSoal $record): void
    {
        // Langsung masukkan ke properti class tanpa findOrFail
        $this->record = $record;

        // Load data dari database ke form (jika sudah ada)
        $soalTerdaftar = $this->record->soals()->with('opsis')->get();

        if ($soalTerdaftar->isNotEmpty()) {
            $this->form->fill([
                'soals' => $soalTerdaftar->map(function ($soal) {
                    return [
                        'text_soal' => $soal->text_soal,
                        'tipe_soal' => $soal->tipe_soal,
                        'opsis' => $soal->opsis ? $soal->opsis->map(function ($opsi) {
                            return [
                                'text_jawaban' => $opsi->text_jawaban,
                                'gambar_jawaban' => $opsi->gambar_jawaban,
                                'kunci_pasangan' => $opsi->kunci_pasangan,
                                'is_correct' => (bool) $opsi->is_correct,
                                'nilai' => (int) $opsi->nilai,
                            ];
                        })->toArray() : [],
                    ];
                })->toArray(),
            ]);
        } else {
            $this->form->fill(['soals' => []]);
        }
    }

    // 5. TAMBAHKAN method form untuk menyusun UI input soal & opsi

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Repeater::make('soals')
                    ->label('Daftar Soal Ujian') // Label utama di atas kotak repeater
                    ->itemLabel(function (array $state, $uuid, Repeater $repeater) {
                        // Ambil semua key UUID yang ada di repeater saat ini
                        $keys = array_keys($repeater->getState() ?? []);

                        // Cari posisi urutannya (ditambah 1 agar mulai dari angka 1)
                        $nomor = array_search($uuid, $keys) + 1;

                        return "Soal Nomor {$nomor}";
                    })
                    ->createItemButtonLabel('Tambah Soal Baru')
                    ->collapsible()
                    ->defaultItems(0)
                    ->schema([
                        Grid::make(2)->schema([
                            RichEditor::make('text_soal')
                                ->label('Pertanyaan / Soal')
                                ->required()
                                ->toolbarButtons([
                                    'bold',
                                    'italic',
                                    'link',
                                    'underline',
                                    'attachFiles',
                                ]),

                            Select::make('tipe_soal')
                                ->label('Tipe Soal')
                                ->options([
                                    'pilihan_ganda_tunggal' => 'Pilihan Ganda (1 Jawaban)',
                                    'pilihan_ganda_kompleks' => 'Pilihan Ganda (Banyak Jawaban)',
                                    'menjodohkan' => 'Menjodohkan',
                                    'benar_salah' => 'Benar atau Salah',
                                ])
                                ->default('pilihan_ganda_tunggal')
                                ->required()
                                ->live() // <-- Mengirimkan state ke Livewire secara realtime
                                ->afterStateUpdated(fn(Set $set) => $set('opsis', [])),
                        ]),

                        // SOLUSI UTAMA: Bungkus Repeater ke dalam Group::make() yang bersifat Live/Reactive
                        \Filament\Forms\Components\Group::make()
                            ->schema(function (Get $get) {
                                // Karena dikurung di dalam Group, sekarang path-nya naik 2 tingkat
                                $tipeSoal = $get('tipe_soal');

                                // Kita return Repeater yang dinamis di sini berdasarkan tipe soal
                                return [
                                    Repeater::make('opsis')
                                        ->label('Pilihan Jawaban')
                                        ->createItemButtonLabel('Tambah Opsi')
                                        ->schema(function () use ($tipeSoal) {
                                            if ($tipeSoal === 'menjodohkan') {
                                                return [
                                                    Grid::make(3)->schema([
                                                        RichEditor::make('text_jawaban')
                                                            ->label('Pertanyaan (Baris)')
                                                            ->toolbarButtons([
                                                                'bold',
                                                                'italic',
                                                                'link',
                                                                'underline',
                                                                'attachFiles',
                                                            ])
                                                            ->extraInputAttributes([
                                                                'style' => 'min-height: 5rem; max-height: 10rem; overflow-y: auto;',
                                                            ])
                                                            ->required(),

                                                        RichEditor::make('kunci_pasangan')
                                                            ->label('Jawaban Benar (Kolom)')
                                                            ->toolbarButtons([
                                                                'bold',
                                                                'italic',
                                                                'link',
                                                                'underline',
                                                                'attachFiles',
                                                            ])
                                                            ->extraInputAttributes([
                                                                'style' => 'min-height: 5rem; max-height: 10rem; overflow-y: auto;',
                                                            ])
                                                            ->required(),

                                                        TextInput::make('nilai')
                                                            ->label('Poin Nilai')
                                                            ->numeric()
                                                            ->default(0)
                                                            ->required(),
                                                    ]),
                                                ];
                                            }

                                            if ($tipeSoal === 'benar_salah') {
                                                return [
                                                    Grid::make(3)->schema([
                                                        TextInput::make('text_jawaban')
                                                            ->label('Pernyataan Jawaban')
                                                            ->required(),
                                                        TextInput::make('nilai')
                                                            ->label('Poin Nilai')->numeric()->default(0)->required(),
                                                        Toggle::make('is_correct')
                                                            ->label('Kunci Benar?')
                                                            ->inline(false),
                                                    ]),
                                                ];
                                            }

                                            // Default: Pilihan Ganda
                                            return [
                                                Grid::make(12)->schema([

                                                    // 1. Kolom Teks Jawaban (Lebar 5 dari 12)
                                                    RichEditor::make('text_jawaban')
                                                        ->label('Teks Jawaban')
                                                        ->placeholder('Ketik pilihan jawaban...')
                                                        ->toolbarButtons([
                                                            'bold',
                                                            'italic',
                                                            'link',
                                                            'underline',
                                                        ])
                                                        ->extraInputAttributes([
                                                            'style' => 'min-height: 5rem; max-height: 10rem; overflow-y: auto;',
                                                        ])
                                                        ->columnSpan(5),

                                                    // 2. Kolom Gambar Jawaban (Lebar 4 dari 12)
                                                    FileUpload::make('gambar_jawaban')
                                                        ->label('Gambar')
                                                        ->image()
                                                        ->directory('jawaban-gambar')
                                                        ->imagePreviewHeight('80px') // Mengecilkan kotak preview gambar agar tidak terlalu tinggi
                                                        ->columnSpan(4),

                                                    // 3. Kolom Kanan: Nilai & Kunci Benar (Lebar 3 dari 12)
                                                    \Filament\Forms\Components\Group::make([

                                                        // Input Poin Nilai di atas
                                                        TextInput::make('nilai')
                                                            ->label('Poin')
                                                            ->numeric()
                                                            ->default(0)
                                                            ->required(),

                                                        // Toggle Kunci Benar tepat di bawah Nilai
                                                        Toggle::make('is_correct')
                                                            ->label('Kunci Benar?')
                                                            ->inline(true), // Menggunakan inline(true) agar teks saklar berjejer rapi di samping

                                                    ])
                                                        ->columnSpan(3)
                                                        ->extraAttributes([
                                                            'class' => 'space-y-4' // Memberikan jarak vertikal antara input nilai dan toggle
                                                        ]),
                                                ]),
                                            ];
                                        })
                                        ->minItems($tipeSoal === 'benar_salah' ? 2 : 1)
                                        ->maxItems($tipeSoal === 'benar_salah' ? 2 : null),
                                ];
                            })
                            ->key('opsi-container'), // Memberikan key unik agar Livewire tidak salah merender komponen cache
                    ]),
            ])
            ->statePath('data');
    }

    // 6. TAMBAHKAN method simpan untuk mengeksekusi penyimpanan database
    public function simpan(): void
    {
        $formData = $this->form->getState();

        DB::transaction(function () use ($formData) {
            // Hapus data lama (Cascading otomatis menghapus opsi_soal_ujians)
            $this->record->soals()->delete();

            // Insert data baru hasil kelola
            foreach ($formData['soals'] ?? [] as $soalData) {
                $soal = SoalUjian::create([
                    'bank_soal_id' => $this->record->id,
                    'text_soal' => $soalData['text_soal'],
                    'tipe_soal' => $soalData['tipe_soal'],
                ]);

                foreach ($soalData['opsis'] ?? [] as $opsiData) {
                    $soal->opsis()->create([
                        'text_jawaban' => $opsiData['text_jawaban'] ?? null,
                        'gambar_jawaban' => $opsiData['gambar_jawaban'] ?? null,
                        'kunci_pasangan' => $opsiData['kunci_pasangan'] ?? null,
                        'is_correct' => $opsiData['is_correct'] ?? false,
                        'nilai' => $opsiData['nilai'] ?? 0,
                    ]);
                }
            }
        });

        Notification::make()->title('Soal Berhasil Disimpan!')->success()->send();
        $this->redirect(BankSoalResource::getUrl('index'));
    }
}
