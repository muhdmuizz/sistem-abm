<?php

namespace App\Services;

use App\Audit\PeristiwaAudit;
use App\Enums\Peranan;
use App\Models\PenugasanPeranan;
use App\Models\RekodAudit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/** Atomic audit boundary, not a substitute for business authorisation inside the callback. */
class JejakAudit
{
    /**
     * The server callback must authorise its operation, use the default database
     * connection and return explicit audit metadata. External side effects are not atomic.
     *
     * @param Closure(): PeristiwaAudit $operation
     */
    public function jalankan(Peranan $role, Closure $operation): RekodAudit
    {
        $actorId = Auth::id();
        if ($actorId === null) {
            throw new AuthorizationException('Authenticated actor required.');
        }

        return DB::transaction(function () use ($actorId, $role, $operation): RekodAudit {
            $actor = User::with('ptj')->whereKey($actorId)->lockForUpdate()->first();
            if ($actor === null || ! $actor->is_active || $actor->ptj === null) {
                throw new AuthorizationException('Active registered actor required.');
            }
            $assignments = PenugasanPeranan::where('user_id', $actorId)->lockForUpdate()->get();
            $effectiveRole = $assignments->contains('peranan', Peranan::Superadmin) ? Peranan::Superadmin : $role;
            $effectiveAssignments = $assignments->where('peranan', $effectiveRole);
            if ($effectiveAssignments->isEmpty() ||
                ($effectiveRole === Peranan::KetuaBidaan && $actor->ptj->kategori !== 'JPNT')) {
                throw new AuthorizationException('Assigned effective role required.');
            }
            $scope = [
                'ptj_id' => $actor->ptj_id,
                'tempat_bertugas_id' => $actor->tempat_bertugas_id,
                'penugasan' => $effectiveAssignments->map(fn ($assignment) => [
                    'id' => $assignment->id, 'ptj_id' => $assignment->ptj_id,
                    'aktiviti_kewangan_id' => $assignment->aktiviti_kewangan_id,
                    'tempat_bertugas_id' => $assignment->tempat_bertugas_id,
                ])->values()->all(),
            ];
            $event = $operation();
            if (! $event instanceof PeristiwaAudit) {
                throw new InvalidArgumentException('Explicit audit event required.');
            }
            foreach ([$event->tindakan, $event->jenisEntiti, $event->idEntiti] as $identity) {
                if (trim($identity) === '') {
                    throw new InvalidArgumentException('Non-empty audit identity required.');
                }
            }
            if ($event->wajibAlasan && trim($event->alasan ?? '') === '') {
                throw new InvalidArgumentException('Audit reason required.');
            }
            $this->validateChanges($event->sebelum);
            $this->validateChanges($event->selepas);
            $id = DB::table('rekod_audit')->insertGetId([
                'pelaku_id' => $actorId, 'peranan' => $effectiveRole->value,
                'skop_pelaku' => json_encode($scope, JSON_THROW_ON_ERROR),
                'tindakan' => $event->tindakan, 'jenis_entiti' => $event->jenisEntiti, 'id_entiti' => $event->idEntiti,
                'sebelum' => json_encode($event->sebelum, JSON_THROW_ON_ERROR),
                'selepas' => json_encode($event->selepas, JSON_THROW_ON_ERROR),
                'alasan' => $event->alasan, 'ptj_id' => $event->ptjId,
                'aktiviti_kewangan_id' => $event->aktivitiId, 'tempat_bertugas_id' => $event->tempatBertugasId,
                'kitaran_id' => $event->kitaranId, 'fasa_rujukan' => $event->fasaRujukan,
                'versi_penghantaran' => $event->versiPenghantaran,
                'berlaku_pada' => CarbonImmutable::now('UTC')->format('Y-m-d H:i:s.uP'),
            ]);

            return RekodAudit::findOrFail($id);
        });
    }

    private function validateChanges(array $changes): void
    {
        foreach ($changes as $key => $value) {
            $name = strtolower((string) preg_replace('/([a-z])([A-Z])/', '$1_$2', (string) $key));
            if (preg_match('/password|passwd|secret|token|authorization|cookie|api_key|app_key|credential/', $name)) {
                throw new InvalidArgumentException('Sensitive fields are not permitted in audit changes.');
            }
            if (is_array($value)) {
                $this->validateChanges($value);
            } elseif (! is_scalar($value) && $value !== null) {
                throw new InvalidArgumentException('Audit changes must contain explicit scalar values.');
            }
        }
    }
}
