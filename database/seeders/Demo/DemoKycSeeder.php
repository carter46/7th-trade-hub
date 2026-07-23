<?php

namespace Database\Seeders\Demo;

use App\Models\KycSubmission;
use Database\Seeders\Demo\Support\DemoContext;
use Database\Seeders\Demo\Support\DemoPersonaCatalog;
use Database\Seeders\Demo\Support\DemoTimeline;
use Illuminate\Database\Seeder;

class DemoKycSeeder extends Seeder
{
    public function run(DemoContext $ctx, DemoTimeline $timeline): void
    {
        $compliance = $ctx->admin('compliance');
        $count = 0;

        foreach (DemoPersonaCatalog::allMembers() as $row) {
            $status = $row['kyc_status'] ?? 'none';
            if ($status === 'none') {
                continue;
            }

            $user = $ctx->member($row['key']);
            $submitted = $timeline->monthsAgo(max(0, (int) $row['months_ago'] - 1), 14, 11);
            $reviewed = $submitted->copy()->addDays(2);

            if ($row['key'] === 'john') {
                // Rejected then appealed (second pending).
                $first = KycSubmission::query()->firstOrCreate(
                    ['user_id' => $user->id, 'level_requested' => 2, 'status' => 'rejected'],
                    [
                        'documents' => [
                            'id_front' => 'demo/kyc/john-id-blurry.jpg',
                            'selfie' => 'demo/kyc/john-selfie.jpg',
                        ],
                        'reviewed_by' => $compliance->id,
                        'reviewed_at' => $reviewed,
                        'notes' => 'ID photo too blurry around the edges. Please re-upload a clearer scan.',
                        'level_granted' => null,
                    ]
                );
                $ctx->stamp($first, $submitted, [
                    'reviewed_at' => $reviewed,
                ]);
                $count++;

                $appealAt = $timeline->monthsAgo(max(0, (int) $row['months_ago'] - 3), 20, 12);
                $appeal = KycSubmission::query()->firstOrCreate(
                    ['user_id' => $user->id, 'level_requested' => 2, 'status' => 'pending'],
                    [
                        'documents' => [
                            'id_front' => 'demo/kyc/john-id-clear.jpg',
                            'selfie' => 'demo/kyc/john-selfie-2.jpg',
                        ],
                        'notes' => 'Appeal: clearer document uploaded.',
                        'level_granted' => null,
                    ]
                );
                $ctx->stamp($appeal, $appealAt);
                $count++;

                continue;
            }

            $level = max(1, (int) ($row['kyc_level'] ?: 1));
            if ($status === 'pending' && ($row['role'] ?? '') === 'seller') {
                $level = 2;
            }

            $isIncomplete = $status === 'pending' && str_ends_with($row['key'], '0');

            $submission = KycSubmission::query()->updateOrCreate(
                ['user_id' => $user->id, 'level_requested' => $level],
                [
                    'status' => $status === 'approved' ? 'approved' : ($status === 'rejected' ? 'rejected' : 'pending'),
                    'documents' => $isIncomplete
                        ? ['id_front' => 'demo/kyc/partial.jpg']
                        : [
                            'id_front' => 'demo/kyc/'.$row['key'].'-id.jpg',
                            'proof_of_address' => 'demo/kyc/'.$row['key'].'-poa.pdf',
                            'selfie' => 'demo/kyc/'.$row['key'].'-selfie.jpg',
                        ],
                    'reviewed_by' => in_array($status, ['approved', 'rejected'], true) ? $compliance->id : null,
                    'reviewed_at' => in_array($status, ['approved', 'rejected'], true) ? $reviewed : null,
                    'notes' => match ($status) {
                        'approved' => 'Documents verified. Level '.$level.' granted.',
                        'rejected' => 'Document quality insufficient. Please resubmit.',
                        default => $isIncomplete ? 'Awaiting remaining documents.' : 'Queued for compliance review.',
                    },
                    'level_granted' => $status === 'approved' ? $level : null,
                ]
            );

            $ctx->stamp($submission, $submitted, [
                'reviewed_at' => $submission->reviewed_at,
            ]);

            if ($status === 'approved') {
                $user->forceFill(['kyc_level' => $level])->save();
            }

            $count++;
        }

        $ctx->kycCount = $count;
        $ctx->note('✓ KYC submissions created ('.$count.')');
    }
}
