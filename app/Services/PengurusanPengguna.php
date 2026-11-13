<?php

namespace App\Services;

use App\Audit\PeristiwaAudit;
use App\Enums\Peranan;
use App\Models\AktivitiKewangan;
use App\Models\PenugasanPeranan;
use App\Models\Ptj;
use App\Models\TempatBertugas;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PengurusanPengguna
{
    public function __construct(private JejakAudit $audit) {}

    public function bolehUrus(User $user): bool
    {
        return $user->is_active && $user->ptj_id !== null &&
            $user->penugasanPeranan()->where('peranan', Peranan::Superadmin->value)->exists();
    }

    /** Recheck the real actor under a row lock, including session revocation. */
    private function actor(bool $admin): User
    {
        $actor = User::whereKey(Auth::id())->lockForUpdate()->first();
        abort_unless($actor && $actor->is_active && $actor->ptj_id !== null &&
            $actor->google_subject !== null && $actor->penugasanPeranan()->exists() &&
            session('abm_access_revision') === $actor->access_revision, 403);
        abort_if($admin && ! $this->bolehUrus($actor), 403);
        return $actor;
    }

    private function validate(array $input, ?User $user): array
    {
        $roleKeys = implode(',', array_column(Peranan::cases(), 'value'));
        $data = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'ptj_id' => ['required', 'integer', 'exists:ptjs,id'],
            'tempat_bertugas_id' => ['nullable', 'integer', 'exists:tempat_bertugas,id'],
            'access_revision' => [$user ? 'required' : 'nullable', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:1000'],
            'roles' => ['present', 'array:'.$roleKeys],
            'roles.*' => ['array:enabled,rows'],
            'roles.*.enabled' => ['sometimes', 'boolean'],
            'roles.*.rows' => ['sometimes', 'array'],
            'roles.*.rows.*' => ['array:selected,ptj_id,aktiviti_kewangan_id,tempat_bertugas_id'],
            'roles.*.rows.*.selected' => ['sometimes', 'boolean'],
            'roles.*.rows.*.ptj_id' => ['nullable', 'integer'],
            'roles.*.rows.*.aktiviti_kewangan_id' => ['nullable', 'integer'],
            'roles.*.rows.*.tempat_bertugas_id' => ['nullable', 'integer'],
        ])->validate();
        $data['email'] = strtolower(trim($data['email']));
        $data['name'] = trim($data['name']);
        $data['reason'] = trim($data['reason']);
        if ($data['name'] === '' || $data['reason'] === '') {
            throw ValidationException::withMessages(['name' => 'Nama dan alasan tidak boleh kosong.']);
        }
        if (User::whereRaw('lower(email) = ?', [$data['email']])->when($user, fn ($q) => $q->whereKeyNot($user->id))->exists()) {
            throw ValidationException::withMessages(['email' => 'E-mel ini telah didaftarkan.']);
        }
        $ptj = Ptj::findOrFail($data['ptj_id']);
        $workplaceId = filled($data['tempat_bertugas_id'] ?? null) ? (int) $data['tempat_bertugas_id'] : null;
        if ($workplaceId !== null && ! TempatBertugas::whereKey($workplaceId)->where('ptj_id', $ptj->id)->exists()) {
            throw ValidationException::withMessages(['tempat_bertugas_id' => 'Tempat Bertugas mesti di bawah PTJ pengguna.']);
        }
        $assignments = [];
        foreach ($data['roles'] as $key => $group) {
            if (! ($group['enabled'] ?? false)) { continue; }
            $role = Peranan::from($key);
            if ($role === Peranan::Superadmin) {
                if (! empty($group['rows'])) {
                    throw ValidationException::withMessages(['roles' => 'Superadmin mempunyai skop seluruh sistem.']);
                }
                $assignments[] = ['peranan' => $key, 'ptj_id' => null, 'aktiviti_kewangan_id' => null, 'tempat_bertugas_id' => null];
                continue;
            }
            $selected = 0;
            foreach ($group['rows'] ?? [] as $row) {
                if (! ($row['selected'] ?? false)) { continue; }
                $p = filled($row['ptj_id'] ?? null) ? (int) $row['ptj_id'] : null;
                $a = filled($row['aktiviti_kewangan_id'] ?? null) ? (int) $row['aktiviti_kewangan_id'] : null;
                $w = filled($row['tempat_bertugas_id'] ?? null) ? (int) $row['tempat_bertugas_id'] : null;
                $references = ($p === null || Ptj::whereKey($p)->exists()) &&
                    ($a === null || AktivitiKewangan::whereKey($a)->exists()) &&
                    ($w === null || ($p !== null && TempatBertugas::whereKey($w)->where('ptj_id', $p)->exists()));
                $valid = match ($role) {
                    Peranan::KetuaBidaan => $ptj->kategori === 'JPNT' && $p === null && $a !== null && $w === null,
                    Peranan::PenyeliaBidaan => $p === null && $a !== null && $w === null,
                    Peranan::PenyediaBidaan => $p !== null && $a !== null && $w === null,
                    Peranan::PenyediaAnggaran => $p !== null && $a !== null &&
                        ($w === null || ($w === $workplaceId && $p === $ptj->id)),
                    Peranan::PemantauBidaan => $w === null && ($p === null || $a === null),
                    default => false,
                };
                if (! $valid || ! $references) {
                    throw ValidationException::withMessages(['roles' => 'Skop '.$role->value.' tidak sah. Semak PTJ, Aktiviti Kewangan dan Tempat Bertugas.']);
                }
                $assignments[] = ['peranan' => $key, 'ptj_id' => $p, 'aktiviti_kewangan_id' => $a, 'tempat_bertugas_id' => $w];
                $selected++;
            }
            if ($selected === 0) {
                throw ValidationException::withMessages(['roles' => 'Pilih sekurang-kurangnya satu penugasan untuk peranan yang ditandakan.']);
            }
        }
        $keys = array_map(fn ($row) => json_encode($row), $assignments);
        if (count($keys) !== count(array_unique($keys))) {
            throw ValidationException::withMessages(['roles' => 'Penugasan yang sama tidak boleh berulang.']);
        }
        $data['assignments'] = $assignments;
        $data['ptj_id'] = $ptj->id;
        $data['tempat_bertugas_id'] = $workplaceId;
        return $data;
    }

    private function snapshot(User $user): array
    {
        return [
            'name' => $user->name, 'email' => $user->email, 'is_active' => $user->is_active,
            'ptj_id' => $user->ptj_id, 'tempat_bertugas_id' => $user->tempat_bertugas_id,
            'access_revision' => $user->access_revision,
            'assignments' => $user->penugasanPeranan()->orderBy('id')->get()->map(fn ($row) => [
                'id' => $row->id, 'peranan' => $row->peranan->value, 'ptj_id' => $row->ptj_id,
                'aktiviti_kewangan_id' => $row->aktiviti_kewangan_id, 'tempat_bertugas_id' => $row->tempat_bertugas_id,
            ])->all(),
        ];
    }

    /** No automatic grants, passwords, identity rebinding or central OS-permission changes. */
    public function simpan(?int $id, array $input): User
    {
        return DB::transaction(function () use ($id, $input) {
            // All access mutations lock actor/target in ID order to avoid cross-admin deadlocks.
            User::whereIn('id', array_filter([Auth::id(), $id]))->orderBy('id')->lockForUpdate()->get();
            $this->actor(true);
            $user = $id === null ? null : User::findOrFail($id);
            $data = $this->validate($input, $user);
            abort_if($user && $user->access_revision !== (int) $data['access_revision'], 409, 'Rekod telah berubah. Muat semula sebelum menyimpan.');
            $before = $user ? $this->snapshot($user) : ['account_exists' => false];
            $this->audit->jalankan(Peranan::Superadmin, function () use (&$user, $data, $before) {
                $created = $user === null;
                $user ??= new User;
                $user->forceFill(array_intersect_key($data, array_flip(['name', 'email', 'is_active', 'ptj_id', 'tempat_bertugas_id'])))->save();
                $wanted = collect($data['assignments'])->keyBy(fn ($row) => json_encode($row));
                foreach ($user->penugasanPeranan()->get() as $existing) {
                    $tuple = ['peranan' => $existing->peranan->value, 'ptj_id' => $existing->ptj_id,
                        'aktiviti_kewangan_id' => $existing->aktiviti_kewangan_id, 'tempat_bertugas_id' => $existing->tempat_bertugas_id];
                    $key = json_encode($tuple);
                    if ($wanted->has($key)) { $wanted->forget($key); } else { $existing->delete(); }
                }
                foreach ($wanted as $tuple) { $user->penugasanPeranan()->create($tuple); }
                // Every deliberate account save revokes previous sessions, including across drivers/devices.
                if (! $created) { $user->increment('access_revision'); }
                DB::table('sessions')->where('user_id', $user->id)->delete();
                return new PeristiwaAudit($created ? 'access.user_registered' : 'access.user_updated', User::class, (string) $user->id,
                    $before, $this->snapshot($user->fresh()), $data['reason'], true,
                    ptjId: $user->ptj_id, tempatBertugasId: $user->tempat_bertugas_id);
            });
            return $user->fresh();
        });
    }

    public function tamatkanSesi(int $id, int $revision, string $reason, bool $own = false): void
    {
        Validator::make(['reason' => $reason], ['reason' => ['required', 'string', 'max:1000']])->validate();
        abort_if(trim($reason) === '', 422, 'Alasan diperlukan.');
        DB::transaction(function () use ($id, $revision, $reason, $own) {
            User::whereIn('id', [Auth::id(), $id])->orderBy('id')->lockForUpdate()->get();
            $actor = $this->actor(! $own);
            abort_if($own && $actor->id !== $id, 403);
            $target = User::findOrFail($id);
            abort_if($target->access_revision !== $revision, 409, 'Rekod telah berubah. Muat semula.');
            $role = $this->bolehUrus($actor) ? Peranan::Superadmin : $actor->penugasanPeranan()->orderBy('id')->firstOrFail()->peranan;
            $this->audit->jalankan($role, function () use ($target, $reason, $own) {
                $before = ['access_revision' => $target->access_revision];
                $target->increment('access_revision');
                DB::table('sessions')->where('user_id', $target->id)->delete();
                return new PeristiwaAudit($own ? 'auth.all_devices_signed_out' : 'access.sessions_revoked', User::class, (string) $target->id,
                    $before, ['access_revision' => $target->access_revision], $reason, true, ptjId: $target->ptj_id,
                    tempatBertugasId: $target->tempat_bertugas_id);
            });
        });
    }
}
