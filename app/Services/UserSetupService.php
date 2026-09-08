<?php

namespace App\Services;

use App\Models\User;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;

class UserSetupService
{
    public function createDefaults(User $user): void
    {
        $user->profile()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'display_name' => $user->name,
            ]
        );

        $user->wallet()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'balance' => 0,
                'total_earned' => 0,
                'total_withdrawn' => 0,
                'currency' => 'MMK',
            ]
        );

        if (!$user->agreement) {
            $this->generateAgreementFromTemplate($user);
        }
    }

    public function regenerateAgreement(User $user): void
    {
        $oldAgreement = $user->agreement;

        if ($oldAgreement && $oldAgreement->pdf_path) {
            Storage::disk('public')->delete($oldAgreement->pdf_path);
            $oldAgreement->delete();
        }

        $this->generateAgreementFromTemplate($user);
    }

    private function generateAgreementFromTemplate(User $user): void
    {
        $templatePath = storage_path('app/templates/music_publishing_agreement_template.pdf');

        if (!file_exists($templatePath)) {
            throw new \Exception('Agreement template PDF not found at: ' . $templatePath);
        }

        $outputDirectory = storage_path('app/public/agreements');

        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $filename = 'agreement_user_' . $user->id . '_' . now()->format('YmdHis') . '.pdf';
        $outputPath = $outputDirectory . '/' . $filename;

        $pdf = new Fpdi();

        $pageCount = $pdf->setSourceFile($templatePath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            $orientation = $size['width'] > $size['height'] ? 'L' : 'P';

            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            if ($pageNo === 2) {
                $pdf->SetTextColor(20, 20, 20);

                /*
                 | Artist signature box position
                 | Name/Date ကို Artist box ထဲထည့်ထားတာပါ။
                 | အပေါ်/အောက်မကိုက်ရင် Y value ကိုပြင်ပါ။
                 */

                $pdf->SetFont('Helvetica', 'B', 16);
                $pdf->SetXY(42, 140);
                $pdf->Write(8, $user->name);

                $pdf->SetFont('Helvetica', '', 12);
                $pdf->SetXY(46, 150);
                $pdf->Write(8, now()->format('d.m.Y'));
            }
        }

        $pdf->Output($outputPath, 'F');

        $user->agreement()->create([
            'agreement_name' => 'Music Publishing & Administration Agreement',
            'signed_name' => $user->name,
            'signed_date' => now()->toDateString(),
            'pdf_path' => 'agreements/' . $filename,
        ]);
    }
}