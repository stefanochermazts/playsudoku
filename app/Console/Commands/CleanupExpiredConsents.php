<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ConsentService;
use App\Models\UserConsent;
use Illuminate\Support\Str;

class CleanupExpiredConsents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consent:cleanup {--dry-run : Show what would be cleaned without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup expired user consents for GDPR compliance';

    public function __construct(private ConsentService $consentService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧹 Starting GDPR consent cleanup...');

        // Get expired consents
        $expiredConsents = UserConsent::expired()->get();
        
        if ($expiredConsents->isEmpty()) {
            $this->info('✅ No expired consents found.');
            return self::SUCCESS;
        }

        $this->table(
            ['Type', 'User', 'Expired On', 'Status'],
            $expiredConsents->map(function ($consent) {
                return [
                    $consent->consent_type,
                    $consent->user ? $consent->user->email : 'Guest (' . Str::limit($consent->session_id, 8) . ')',
                    $consent->expires_at?->format('Y-m-d H:i:s'),
                    $consent->isWithdrawn() ? 'Already Withdrawn' : 'Active (Expired)'
                ];
            })->toArray()
        );

        if ($this->option('dry-run')) {
            $this->warn('🔍 DRY RUN: No changes will be made.');
            $this->info("Would clean up {$expiredConsents->count()} expired consents.");
            return self::SUCCESS;
        }

        if (!$this->confirm('Do you want to proceed with the cleanup?', true)) {
            $this->info('❌ Cleanup cancelled.');
            return self::SUCCESS;
        }

        // Perform cleanup
        $cleaned = $this->consentService->cleanupExpiredConsents();

        // Generate statistics
        $stats = $this->consentService->getConsentStatistics();
        
        $this->info("✅ Cleaned up {$cleaned} expired consents.");
        $this->info("📊 Current statistics:");
        $this->info("   - Total consents: {$stats['total']}");
        $this->info("   - Active consents: {$stats['active']}");
        $this->info("   - Withdrawn consents: {$stats['withdrawn']}");
        $this->info("   - Compliance rate: {$stats['compliance_rate']}%");

        // Log by type
        foreach ($stats['by_type'] as $type => $counts) {
            $granted = $counts[1] ?? 0;
            $denied = $counts[0] ?? 0;
            $this->info("   - {$type}: {$granted} granted, {$denied} denied");
        }

        $this->info('🎉 Cleanup completed successfully!');

        return self::SUCCESS;
    }
}
