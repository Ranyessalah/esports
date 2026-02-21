<?php

namespace App\Service;

use App\Entity\User;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\SvgWriter;
use OTPHP\TOTP;

class TotpService
{
    /**
     * Generate a new TOTP secret for a user.
     */
    public function generateSecret(): string
    {
        $totp = TOTP::generate();
        return $totp->getSecret();
    }

    /**
     * Get the otpauth:// provisioning URI for Google Authenticator.
     */
    public function getProvisioningUri(User $user, string $secret): string
    {
        $totp = TOTP::createFromSecret($secret);
        $totp->setLabel($user->getEmail());
        $totp->setIssuer('ClutchX');

        return $totp->getProvisioningUri();
    }

    /**
     * Generate a QR code image (base64 PNG) for the provisioning URI.
     */
    public function getQrCodeDataUri(string $provisioningUri): string
    {
        $result = Builder::create()
            ->writer(new SvgWriter())
            ->data($provisioningUri)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(250)
            ->margin(10)
            ->build();

        return $result->getDataUri();
    }

    /**
     * Verify a 6-digit TOTP code against a secret.
     */
    public function verifyCode(string $secret, string $code): bool
    {
        $totp = TOTP::createFromSecret($secret);

        return $totp->verify($code, null, 1); // window=1 allows 30s drift
    }
}
