<?php

namespace App\Console\Commands;

use App\Audit\PeristiwaAudit;
use App\Enums\Peranan;
use App\Models\PenugasanPeranan;
use App\Models\Ptj;
use App\Models\TempatBertugas;
use App\Models\User;
use App\Services\JejakAudit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Trusted local-console initialisation only; never authenticates a web session. */
class BootstrapLocalSuperadmin extends Command
{
    protected $signature = 'abm:bootstrap-local-superadmin
        {--email= : Verified operator contact and initial account email}
        {--name= : Initial account name}
        {--ptj-code= : Existing JPNT PTJ code}
        {--workplace= : Exact existing JPNT Sektor name}
        {--reason= : Reason for this local initialisation}
        {--confirm : Declare that the account is the real local operator}';

    protected $description = 'Register the first local Superadmin with an explicit operator-declared audit record';

    public function handle(JejakAudit $audit): int
    {
        $testing = app()->runningUnitTests() && app()->environment('testing');
        if (! app()->environment('local') && ! $testing) {
            $this->error('This command is available only for local development.');
            return self::FAILURE;
        }
        $db = DB::connection();
        if ($db->getDriverName() !== 'pgsql' ||
            $db->selectOne('select current_database() as name')->name !== ($testing ? 'sistem_abm_testing' : 'sistem_abm')) {
            $this->error('Unexpected development database.');
            return self::FAILURE;
        }
        $email = strtolower(trim((string) $this->option('email')));
        $name = trim((string) $this->option('name'));
        $reason = trim((string) $this->option('reason'));
        $workplace = trim((string) $this->option('workplace'));
        $ptjCode = trim((string) $this->option('ptj-code'));
        if (! $this->option('confirm') || ! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255 ||
            $name === '' || mb_strlen($name) > 255 || $reason === '' || mb_strlen($reason) > 1000 ||
            $workplace === '' || $ptjCode === '') {
            $this->error('Supply valid identity, organisation, reason and --confirm.');
            return self::FAILURE;
        }
        $guard = Auth::guard();
        $previous = $guard->user();
        try {
            DB::transaction(function () use ($audit, $guard, $email, $name, $reason, $workplace, $ptjCode) {
                // Serialise initialisation attempts, including when the role table is empty.
                DB::select('select pg_advisory_xact_lock(41620101, 8008)');
                if (PenugasanPeranan::where('peranan', Peranan::Superadmin->value)->exists()) {
                    throw new RuntimeException('A Superadmin already exists; initialisation is closed.');
                }
                if (User::whereRaw('lower(email) = ?', [$email])->exists()) {
                    throw new RuntimeException('This account already exists; no existing account was changed.');
                }
                $ptjs = Ptj::where('kod_ptj', $ptjCode)->where('kategori', 'JPNT')->get();
                if ($ptjs->count() !== 1) {
                    throw new RuntimeException('An exact existing JPNT PTJ is required.');
                }
                $ptj = $ptjs->first();
                $places = TempatBertugas::where('ptj_id', $ptj->id)->where('jenis', 'sektor')->where('nama', $workplace)->get();
                if ($places->count() !== 1) {
                    throw new RuntimeException('An exact existing Sektor under that JPNT is required.');
                }
                $place = $places->first();
                $user = User::create(['name' => $name, 'email' => $email]);
                $user->forceFill(['is_active' => true, 'ptj_id' => $ptj->id, 'tempat_bertugas_id' => $place->id])->save();
                PenugasanPeranan::create(['user_id' => $user->id, 'peranan' => Peranan::Superadmin]);
                // The operator explicitly declares their identity at the trusted console.
                // This is not Google verification and creates no browser/session login.
                $guard->setUser($user);
                $audit->jalankan(Peranan::Superadmin, fn () => new PeristiwaAudit(
                    'access.local_superadmin_bootstrapped', User::class, (string) $user->id,
                    ['account_exists' => false],
                    ['name' => $name, 'email' => $email, 'is_active' => true,
                        'ptj_id' => $ptj->id, 'tempat_bertugas_id' => $place->id,
                        'roles' => [Peranan::Superadmin->value],
                        'identity_assurance' => 'local_console_operator_declaration',
                        'google_verified' => false],
                    $reason, true, ptjId: $ptj->id, tempatBertugasId: $place->id,
                ));
            });
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        } finally {
            $previous === null ? $guard->forgetUser() : $guard->setUser($previous);
        }
        $this->info('Initial local Superadmin registered and audited. Sign in through Google to continue.');
        return self::SUCCESS;
    }
}
