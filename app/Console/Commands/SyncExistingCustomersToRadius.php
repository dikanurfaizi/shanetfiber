<?php

namespace App\Console\Commands;

use App\Events\Radius\RadiusUserProvisionRequested;
use App\Models\Customers\Customer;
use App\Models\Packages\PppProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Exception;

class SyncExistingCustomersToRadius extends Command
{
    protected $signature = 'radius:sync-customers {--active-only : Hanya sync customer dengan status aktif}';
    protected $description = 'Sinkronisasi massal seluruh data customer yang ada di ShanetFiber ke database FreeRADIUS';

    public function handle(): int
    {
        $this->info('Memulai sinkronisasi massal customer ke FreeRADIUS...');

        $traceId = (string) Str::uuid();

        // 1. Cache seluruh PppProfile di awal untuk menghindari N+1 Query
        $profilesMap = PppProfile::all()->keyBy('profile_name');

        // 2. Query Eager Loading
        $query = Customer::query()->with(['activeSubscription.package']);

        if ($this->option('active-only')) {
            $query->where('is_active', true);
        }

        $totalCustomers = $query->count();

        if ($totalCustomers === 0) {
            $this->warn('Tidak ada data customer yang ditemukan.');
            return Command::SUCCESS;
        }

        $progressBar = $this->output->createProgressBar($totalCustomers);
        $progressBar->start();

        $successCount = 0;
        $failCount = 0;

        // 3. Gunakan Chunk untuk efisiensi memori
        $query->chunk(100, function ($customers) use ($profilesMap, $traceId, $progressBar, &$successCount, &$failCount) {
            foreach ($customers as $customer) {
                try {
                    $pppProfile = null;
                    $subscription = $customer->activeSubscription;

                    if ($subscription?->package?->profile) {
                        $profileName = $subscription->package->profile;
                        $pppProfile = $profilesMap->get($profileName);
                    }

                    event(new RadiusUserProvisionRequested(
                        $customer,
                        $pppProfile,
                        $traceId
                    ));

                    $successCount++;
                } catch (Exception $e) {
                    $failCount++;
                    $this->error("\nGagal sync customer ID {$customer->id} ({$customer->name}): " . $e->getMessage());
                }

                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Sinkronisasi Selesai!");
        $this->table(
            ['Total Processed', 'Success', 'Failed'],
            [[$totalCustomers, $successCount, $failCount]]
        );

        return Command::SUCCESS;
    }
}
