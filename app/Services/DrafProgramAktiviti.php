<?php

namespace App\Services;

use App\Audit\PeristiwaAudit;
use App\Enums\Peranan;
use App\Enums\StatusBidaan;
use App\Models\Bidaan;
use App\Models\ProgramAktiviti;
use App\Models\TerasStrategik;
use App\Models\StrategiRpm;
use App\Models\PrakarsaRpm;
use App\Models\TempatBertugas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Internal programme metadata drafts only; no financial associations or recalculation. */
class DrafProgramAktiviti
{
    public const JENIS = ['Umum','Sukan','Kokurikulum'];
    public const KATEGORI = ['Kursus/ Bengkel','Taklimat','Mesyuarat','Perjumpaan','Pemantauan'];
    public const PERINGKAT = ['Kebangsaan','Negeri','Daerah'];
    public const KAEDAH = ['Pakej','Bukan Pakej'];
    public const KOMPONEN = ['Penginapan','Makan Minum','Sewaan Dewan','Sewaan Peralatan'];
    private const FIELDS = ['teras_strategik_id','strategi_id','prakarsa_id','nama','jenis','kategori','peringkat','kaedah','tarikh_mula','tarikh_tamat'];

    public function __construct(private JejakAudit $audit, private KawalanPenyediaanKewangan $guard) {}

    public function simpan(int $bidId, ?int $programId, array $input): ProgramAktiviti
    {
        return DB::transaction(function () use ($bidId, $programId, $input) {
            $actor = $this->guard->actor();
            $cycleId = Bidaan::whereKey($bidId)->value('kitaran_id');
            abort_if($cycleId === null, 404);
            $cycle = $this->guard->prepareLocks($cycleId);
            $bid = Bidaan::whereKey($bidId)->where('kitaran_id', $cycle->id)->lockForUpdate()->firstOrFail();
            $program = $programId === null ? null : $bid->programAktiviti()->whereKey($programId)->lockForUpdate()->firstOrFail();
            $data = $this->validate($input, $program !== null, $cycle->tahun);
            $place = $data['tempat_bertugas_id'];
            $authority = $this->guard->authorise($actor, Peranan::PenyediaAnggaran, $cycle->id,
                $bid->ptj_id, $bid->aktiviti_kewangan_id, null, $data['reason'] ?? null, $place);
            if ($program && $program->tempat_bertugas_id !== $place) {
                $this->guard->authorise($actor, Peranan::PenyediaAnggaran, $cycle->id,
                    $bid->ptj_id, $bid->aktiviti_kewangan_id, null, $data['reason'] ?? null, $program->tempat_bertugas_id);
            }
            $this->guard->current($bid, (int) $data['semakan_bidaan'],
                [StatusBidaan::DalamPenyediaan, StatusBidaan::Dikembalikan]);
            abort_unless($bid->diaktifkan, 409, 'Bidaan mesti aktif.');
            abort_if($program && $program->semakan !== (int) $data['semakan_program'], 409, 'Program/ Aktiviti telah berubah. Muat semula.');
            TempatBertugas::whereKey($place)->where('ptj_id', $bid->ptj_id)->sharedLock()->firstOrFail();
            $metadata = $data['maklumat'];
            $before = $program ? $this->snapshot($program) : ['exists' => false];
            if ($program && $this->values($program) === $metadata && $program->tempat_bertugas_id === $place) {
                return $program;
            }
            $this->audit->jalankan(Peranan::PenyediaAnggaran, function () use (&$program, $actor, $bid, $cycle, $place, $metadata, $before, $authority) {
                $created = $program === null;
                if ($created) {
                    $program = new ProgramAktiviti;
                    $program->forceFill(['bidaan_id' => $bid->id, 'ptj_id' => $bid->ptj_id,
                        'kitaran_id' => $cycle->id, 'tahun_kitaran' => $cycle->tahun, 'created_by' => $actor->id, 'semakan' => 0]);
                }
                $fields = array_intersect_key($metadata, array_flip(self::FIELDS));
                $program->forceFill($fields + ['tempat_bertugas_id' => $place]);
                $program->semakan++;
                $program->save();
                $this->saveChildren($program, $metadata);
                $bid->semakan++;
                $bid->save();
                return new PeristiwaAudit($created ? 'program.draft_created' : 'program.draft_updated',
                    ProgramAktiviti::class, (string) $program->id, $before,
                    $this->snapshot($program->fresh()) + ['semakan_bidaan' => $bid->semakan, 'pintasan' => $authority['override']],
                    $authority['reason'] ?: ($created ? 'Simpan draf Program/ Aktiviti.' : 'Pinda draf Program/ Aktiviti.'),
                    ptjId: $bid->ptj_id, aktivitiId: $bid->aktiviti_kewangan_id,
                    tempatBertugasId: $place, kitaranId: $cycle->id);
            });
            return $program->fresh();
        });
    }

    private function validate(array $input, bool $editing, int $year): array
    {
        if (isset($input['maklumat']) && is_array($input['maklumat'])) {
            foreach (['nama','jenis','kategori','peringkat','kaedah','tarikh_mula','tarikh_tamat'] as $field) {
                if (isset($input['maklumat'][$field]) && is_string($input['maklumat'][$field])) {
                    $text = trim($input['maklumat'][$field]);
                    $input['maklumat'][$field] = $text === '' ? null : $text;
                }
            }
        }
        $rules = [
            'rekod' => 'required|array:tempat_bertugas_id,semakan_bidaan,semakan_program,maklumat,reason',
            'rekod.tempat_bertugas_id' => ['bail','required','integer','min:1',$this->integerTypeRule()],
            'rekod.semakan_bidaan' => ['bail','required','integer','min:0',$this->integerTypeRule()],
            'rekod.semakan_program' => $editing ? ['bail','required','integer','min:0',$this->integerTypeRule()] : 'prohibited',
            'rekod.reason' => 'bail|nullable|string|max:1000',
            'rekod.maklumat' => 'required|array:teras_strategik_id,strategi_id,prakarsa_id,nama,jenis,kategori,peringkat,kaedah,tarikh_mula,tarikh_tamat,komponen,jemputan,peserta',
            'rekod.maklumat.teras_strategik_id' => ['bail','required','integer','min:1',$this->integerTypeRule(),'exists:teras_strategik,id'],
            'rekod.maklumat.strategi_id' => ['bail','required','integer','min:1',$this->integerTypeRule(),'exists:strategi_rpm,id'],
            'rekod.maklumat.prakarsa_id' => ['bail','required','integer','min:1',$this->integerTypeRule(),'exists:prakarsa_rpm,id'],
            'rekod.maklumat.nama' => 'nullable|string',
            'rekod.maklumat.jenis' => ['bail','nullable','string',Rule::in(self::JENIS)],
            'rekod.maklumat.kategori' => ['bail','nullable','string',Rule::in(self::KATEGORI)],
            'rekod.maklumat.peringkat' => ['bail','nullable','string',Rule::in(self::PERINGKAT)],
            'rekod.maklumat.kaedah' => ['bail','nullable','string',Rule::in(self::KAEDAH)],
            'rekod.maklumat.tarikh_mula' => 'bail|nullable|string|date_format:Y-m-d',
            'rekod.maklumat.tarikh_tamat' => 'bail|nullable|string|date_format:Y-m-d',
            'rekod.maklumat.komponen' => ['sometimes','array', $this->listRule()],
            'rekod.maklumat.komponen.*' => ['bail','required','string','distinct',Rule::in(self::KOMPONEN)],
        ];
        foreach (['jemputan','peserta'] as $group) {
            $path = 'rekod.maklumat.'.$group;
            $rules[$path] = ['sometimes','array',$this->listRule()];
            $rules[$path.'.*'] = 'required|array:kategori,klasifikasi,catatan,bilangan';
            $rules[$path.'.*.kategori'] = ['bail','required','string', Rule::in($group === 'jemputan' ? ['Pegawai','AKP'] :
                ['Pegawai','Guru','AKP','Murid Rendah','Murid Menengah','Lain-lain'])];
            $rules[$path.'.*.klasifikasi'] = ['bail','nullable','integer','min:1', $this->integerTypeRule()];
            $rules[$path.'.*.catatan'] = 'nullable|string';
            $rules[$path.'.*.bilangan'] = ['bail','required','integer','min:1', $this->integerTypeRule()];
        }
        $attributes = ['rekod' => 'Rekod Program/ Aktiviti', 'rekod.maklumat' => 'Maklumat Program/ Aktiviti',
            'rekod.tempat_bertugas_id' => 'Tempat Bertugas', 'rekod.semakan_bidaan' => 'Semakan Bidaan',
            'rekod.semakan_program' => 'Semakan Program/ Aktiviti', 'rekod.reason' => 'Alasan pintasan'];
        foreach (['teras_strategik_id' => 'Teras Strategik', 'strategi_id' => 'Strategi', 'prakarsa_id' => 'Prakarsa',
            'nama' => 'Nama Program/ Aktiviti', 'jenis' => 'Jenis Program/ Aktiviti', 'kategori' => 'Kategori Program/ Aktiviti',
            'peringkat' => 'Peringkat', 'kaedah' => 'Kaedah Pelaksanaan', 'tarikh_mula' => 'Tarikh Mula',
            'tarikh_tamat' => 'Tarikh Tamat', 'komponen' => 'Komponen Pakej'] as $field => $label) {
            $attributes['rekod.maklumat.'.$field] = $label;
            $attributes['rekod.maklumat.'.$field.'.*'] = $label;
        }
        foreach (['jemputan' => 'Jemputan/ Urus Setia', 'peserta' => 'Peserta'] as $group => $label) {
            $attributes['rekod.maklumat.'.$group] = $label;
            $attributes['rekod.maklumat.'.$group.'.*'] = 'Baris '.$label;
            foreach (['kategori'=>'Kategori','klasifikasi'=>'Gred/ Tahun/ Tingkatan','catatan'=>'Catatan','bilangan'=>'Bilangan'] as $field=>$name) {
                $attributes['rekod.maklumat.'.$group.'.*.'.$field] = $name.' '.$label.' (baris :position)';
            }
        }
        $data = Validator::make(['rekod' => $input], $rules, [
            'required' => ':attribute diperlukan.', 'integer' => ':attribute mesti nombor bulat.',
            'min.numeric' => ':attribute mesti sekurang-kurangnya :min.',
            'max.string' => ':attribute tidak boleh melebihi :max aksara.',
            'exists' => 'Pilih :attribute yang sah.', 'in' => 'Pilih :attribute yang sah.',
            'string' => ':attribute mesti berbentuk teks.', 'array' => 'Struktur :attribute tidak sah.',
            'distinct' => ':attribute tidak boleh berulang.', 'prohibited' => ':attribute tidak dibenarkan.',
            'date_format' => ':attribute mesti tarikh yang sah.',
        ], $attributes)->validate()['rekod'];
        $m = $data['maklumat'];
        $error = fn ($field, $message) => throw ValidationException::withMessages(['rekod.maklumat.'.$field => $message]);
        TerasStrategik::whereKey($m['teras_strategik_id'])->sharedLock()->firstOrFail();
        if (! StrategiRpm::whereKey($m['strategi_id'])->where('teras_strategik_id', $m['teras_strategik_id'])->sharedLock()->first()) {
            $error('strategi_id', 'Strategi mesti di bawah Teras Strategik yang dipilih.');
        }
        if (! PrakarsaRpm::whereKey($m['prakarsa_id'])->where('strategi_id', $m['strategi_id'])->sharedLock()->first()) {
            $error('prakarsa_id', 'Prakarsa mesti di bawah Strategi yang dipilih.');
        }
        $normalized = [];
        foreach (self::FIELDS as $field) {
            $normalized[$field] = str_ends_with($field, '_id') ? (int) $m[$field] : ($m[$field] ?? null);
        }
        foreach (['tarikh_mula','tarikh_tamat'] as $field) {
            if ($normalized[$field] !== null && (int) substr($normalized[$field], 0, 4) !== $year) {
                $error($field, 'Tarikh Program/ Aktiviti mesti dalam tahun Kitaran '.$year.'.');
            }
        }
        if ($normalized['tarikh_mula'] !== null && $normalized['tarikh_tamat'] !== null &&
            $normalized['tarikh_tamat'] < $normalized['tarikh_mula']) {
            $error('tarikh_tamat', 'Tarikh Tamat mesti pada atau selepas Tarikh Mula.');
        }
        $components = $m['komponen'] ?? [];
        if ($normalized['kaedah'] === 'Pakej' && ! $components) { $error('komponen', 'Pilih sekurang-kurangnya satu Komponen Pakej.'); }
        if ($normalized['kaedah'] !== 'Pakej' && $components) { $error('komponen', 'Komponen Pakej hanya untuk kaedah Pakej.'); }
        $normalized['komponen'] = array_values(array_intersect(self::KOMPONEN, $components));
        foreach (['jemputan','peserta'] as $group) {
            $normalized[$group] = [];
            foreach ($m[$group] ?? [] as $index => $row) {
                $category = $row['kategori'];
                $classification = isset($row['klasifikasi']) ? (int) $row['klasifikasi'] : null;
                $note = isset($row['catatan']) ? trim($row['catatan']) : null;
                if ($note === '') { $note = null; }
                $valid = match ($category) {
                    'Pegawai','Guru' => $classification !== null && $classification >= 9 && $note === null,
                    'AKP' => $classification !== null && $classification >= 1 && $classification <= 8 && $note === null,
                    'Murid Rendah','Murid Menengah' => $classification !== null && $classification >= 1 && $classification <= 6 && $note === null,
                    'Lain-lain' => $classification === null && $note !== null,
                };
                if (! $valid) { $error($group.'.'.$index, 'Lengkapkan klasifikasi yang sah bagi kategori peserta ini.'); }
                $normalized[$group][] = ['kategori' => $category, 'klasifikasi' => $classification,
                    'catatan' => $note, 'bilangan' => (int) $row['bilangan']];
            }
        }
        $data['maklumat'] = $normalized;
        $data['tempat_bertugas_id'] = (int) $data['tempat_bertugas_id'];
        return $data;
    }

    private function integerTypeRule(): \Closure
    {
        return function ($attribute, $value, $fail) {
            if (! is_int($value) && ! is_string($value)) { $fail('Gunakan nombor bulat yang sah.'); }
        };
    }

    private function listRule(): \Closure
    {
        return function ($attribute, $value, $fail) {
            if (! is_array($value) || ! array_is_list($value)) { $fail('Gunakan senarai baris yang tersusun.'); }
        };
    }

    private function values(ProgramAktiviti $program): array
    {
        $values = [];
        foreach (self::FIELDS as $field) {
            $value = $program->{$field};
            $values[$field] = in_array($field, ['tarikh_mula','tarikh_tamat'], true) ? $value?->toDateString() : $value;
        }
        $existing = $program->komponen()->pluck('komponen')->all();
        $values['komponen'] = array_values(array_intersect(self::KOMPONEN, $existing));
        foreach (['jemputan','peserta'] as $group) {
            $values[$group] = $program->bilangan()->where('kumpulan', $group)->orderBy('turutan')->get()
                ->map(fn ($row) => ['kategori' => $row->kategori, 'klasifikasi' => $row->klasifikasi,
                    'catatan' => $row->catatan, 'bilangan' => $row->bilangan])->all();
        }
        return $values;
    }

    private function snapshot(ProgramAktiviti $program): array
    {
        return ['program_id' => $program->id, 'bidaan_id' => $program->bidaan_id, 'ptj_id' => $program->ptj_id,
            'kitaran_id' => $program->kitaran_id, 'tempat_bertugas_id' => $program->tempat_bertugas_id,
            'created_by' => $program->created_by, 'semakan_program' => $program->semakan, 'maklumat' => $this->values($program)];
    }

    private function saveChildren(ProgramAktiviti $program, array $metadata): void
    {
        $program->komponen()->whereNotIn('komponen', $metadata['komponen'])->delete();
        foreach ($metadata['komponen'] as $component) {
            if (! $program->komponen()->where('komponen', $component)->exists()) {
                $row = new \App\Models\KomponenProgram;
                $row->forceFill(['program_aktiviti_id' => $program->id, 'komponen' => $component])->save();
            }
        }
        foreach (['jemputan','peserta'] as $group) {
            $program->bilangan()->where('kumpulan', $group)->where('turutan', '>', count($metadata[$group]))->delete();
            foreach ($metadata[$group] as $index => $data) {
                $row = $program->bilangan()->where('kumpulan', $group)->where('turutan', $index + 1)->first()
                    ?? new \App\Models\BilanganProgram;
                $row->forceFill($data + ['program_aktiviti_id' => $program->id, 'kumpulan' => $group, 'turutan' => $index + 1]);
                if ($row->isDirty()) { $row->save(); }
            }
        }
    }
}
