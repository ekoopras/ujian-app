<?php

namespace App\Filament\Resources\Ujian\BankSoalResource\Pages;

use App\Filament\Resources\Ujian\BankSoalResource;
use App\Models\BankSoal;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class SoalUjian extends Page implements HasForms
{
    protected static string $resource = BankSoalResource::class;

    protected static string $view = 'filament.resources.ujian.bank-soal-resource.pages.soal-ujian';

    public BankSoal $record;
    public ?array $data = [];

    public function mount($record): void
    {
        $this->record = BankSoal::findOrFail($record);

        // Mengambil data dari 2 tabel terpisah untuk ditampilkan kembali ke Form Repeater
        $this->form->fill([
            'soals' => $this->record->soals()->with('opsis')->get()->map(function ($soal) {
                return [
                    'text_soal' => $soal->text_soal,
                    'gambar_soal' => $soal->gambar_soal,
                    'tipe_soal' => $soal->tipe_soal,
                    'opsis' => $soal->opsis->map(function ($opsi) {
                        return [
                            'text_jawaban' => $opsi->text_jawaban,
                            'gambar_jawaban' => $opsi->gambar_jawaban,
                            'kunci_pasangan' => $opsi->kunci_pasangan,
                            'is_correct' => $opsi->is_correct,
                            'nilai' => $opsi->nilai,
                        ];
                    })->toArray(),
                ];
            })->toArray(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Repeater::make('soals')
                    ->label('Daftar Soal Ujian')
                    ->createItemButtonLabel('Tambah Soal')
                    ->collapsible()
                    ->defaultItems(1)
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('tipe_soal')
                                ->label('Tipe Soal')
                                ->options([
                                    'pilihan_ganda_tunggal' => 'Pilihan Ganda (1 Jawaban)',
                                    'pilihan_ganda_kompleks' => 'Pilihan Ganda (Banyak Jawaban)',
                                    'menjodohkan' => 'Menjodohkan',
                                    'benar_salah' => 'Benar atau Salah',
                                ])
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn($set) => $set('opsis', [])),

                            FileUpload::make('gambar_soal')
                                ->label('Gambar Soal (Opsional)')
                                ->image()
                                ->directory('soal-gambar'),
                        ]),

                        Textarea::make('text_soal')->label('Pertanyaan')->required()->rows(3),

                        // --- SUB-REPEATER JAWABAN (Menyimpan data ke tabel opsi_soal_ujians) ---
                        Repeater::make('opsis')
                            ->label('Pilihan Jawaban / Opsi')
                            ->createItemButtonLabel('Tambah Opsi Jawaban')
                            ->schema(function (callable $get) {
                                $tipeSoal = $get('../tipe_soal');

                                // Jika Tipe Menjodohkan
                                if ($tipeSoal === 'menjodohkan') {
                                    return [
                                        Grid::make(3)->schema([
                                            TextInput::make('text_jawaban')->label('Sisi Kiri (Soal)')->required(),
                                            TextInput::make('kunci_pasangan')->label('Sisi Kanan (Pasangan Benar)')->required(),
                                            TextInput::make('nilai')->label('Nilai Poin')->numeric()->default(0)->required(),
                                        ]),
                                    ];
                                }

                                // Jika Tipe Benar / Salah
                                if ($tipeSoal === 'benar_salah') {
                                    return [
                                        Grid::make(3)->schema([
                                            TextInput::make('text_jawaban')->label('Pernyataan Jawaban')->required(),
                                            Toggle::make('is_correct')->label('Kunci Benar?')->inline(false),
                                            TextInput::make('nilai')->label('Nilai Poin')->numeric()->default(0)->required(),
                                        ]),
                                    ];
                                }

                                // Default Tipe Pilihan Ganda (Tunggal & Kompleks)
                                return [
                                    Grid::make(4)->schema([
                                        TextInput::make('text_jawaban')->label('Teks Pilihan'),
                                        FileUpload::make('gambar_jawaban')->label('Gambar Opsi')->image()->directory('jawaban-gambar'),
                                        Toggle::make('is_correct')->label('Kunci?')->inline(false),
                                        TextInput::make('nilai')->label('Nilai Poin')->numeric()->default(0)->required(),
                                    ]),
                                ];
                            })
                            ->minItems(fn($get) => $get('../tipe_soal') === 'benar_salah' ? 2 : 1)
                            ->maxItems(fn($get) => $get('../tipe_soal') === 'benar_salah' ? 2 : null),
                    ]),
            ])
            ->statePath('data');
    }

    public function simpan(): void
    {
        $formData = $this->form->getState();

        DB::transaction(function () use ($formData) {
            // Hapus data lama di kedua tabel secara cascading otomatis
            $this->record->soals()->delete();

            // Insert ulang semua soal baru dari repeater ke database masing-masing tabel
            foreach ($formData['soals'] ?? [] as $soalData) {
                $soal = SoalUjian::create([
                    'bank_soal_id' => $this->record->id,
                    'text_soal' => $soalData['text_soal'],
                    'gambar_soal' => $soalData['gambar_soal'],
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

        Notification::make()->title('Soal Ujian Berhasil Disimpan!')->success()->send();
        $this->redirect(BankSoalResource::getUrl('index'));
    }
}
