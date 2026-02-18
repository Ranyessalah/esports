<?php

namespace App\Service;

class RecaptchaService
{
    private string $secretKey;

    public function __construct(string $recaptchaSecretKey)
    {
        $this->secretKey = $recaptchaSecretKey;
    }

    /**
     * Verify the reCAPTCHA v2 response token.
     */
    public function verify(?string $recaptchaResponse): bool
    {
        if (empty($recaptchaResponse)) {
            return false;
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => $this->secretKey,
            'response' => $recaptchaResponse,
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
            ],
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === false) {
            return false;
        }

        $json = json_decode($result, true);

        return isset($json['success']) && $json['success'] === true;
    }
}
