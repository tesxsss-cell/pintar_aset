<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class QrScanTracker
{
    public function record(Request $request, Asset $asset): AssetScan
    {
        if ($request->user()) {
            // Akun yang sama tetap satu identitas, termasuk lintas perangkat.
            $identity = 'user:'.$request->user()->getAuthIdentifier();
        } else {
            $cookieName = config('qr_tracking.cookie_name');
            $visitorId = $request->cookie($cookieName);

            if (! is_string($visitorId) || ! Str::isUuid($visitorId)) {
                $visitorId = (string) Str::uuid();
            }

            // Dienkripsi dan ditandatangani oleh middleware EncryptCookies Laravel.
            Cookie::queue(Cookie::make(
                $cookieName, $visitorId, 60 * 24 * 365, '/', null,
                $request->isSecure(), true, false, 'lax',
            ));
            $identity = 'browser:'.$visitorId;
        }

        $now = now(config('qr_tracking.timezone'));

        // createOrFirst + UNIQUE di DB menangani request bersamaan yang sudah
        // membawa identitas sama; bukan pola exists() lalu create() yang rawan race.
        return $asset->scanEvents()->createOrFirst([
            'visitor_hash' => hash_hmac('sha256', $identity, (string) config('app.key')),
            'scan_date' => $now->toDateString(),
        ], [
            'ip_hash' => $request->ip()
                ? hash_hmac('sha256', $request->ip(), (string) config('app.key'))
                : null,
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            'scanned_at' => $now->copy()->setTimezone(config('app.timezone')),
        ]);
    }
}
