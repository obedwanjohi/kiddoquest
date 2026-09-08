<?php

namespace App\Http\Middleware\Api;

use App\Models\Device;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Remembers which devices a family uses, so a parent can see "last synced from
 * the living-room TV" and so push tokens have somewhere to live.
 *
 * Writing on every request would be wasteful; a device row is only touched once
 * every few minutes.
 */
class TrackDevice
{
    public function handle(Request $request, Closure $next): Response
    {
        $deviceId = Str::limit((string) $request->header('X-Device-Id'), 64, '');

        if ($deviceId !== '') {
            $request->attributes->set('device_id', $deviceId);

            $guardian = $request->user();

            if ($guardian) {
                $device = Device::find($deviceId);

                if (! $device) {
                    Device::create([
                        'id'           => $deviceId,
                        'guardian_id'  => $guardian->id,
                        'platform'     => Str::limit((string) $request->header('X-Platform'), 24, '') ?: null,
                        'model'        => Str::limit((string) $request->header('X-Device-Model'), 96, '') ?: null,
                        'app_version'  => Str::limit((string) $request->header('X-App-Version'), 24, '') ?: null,
                        'last_seen_at' => now(),
                    ]);
                } elseif ($device->last_seen_at === null || $device->last_seen_at->lt(now()->subMinutes(5))) {
                    $device->forceFill([
                        'guardian_id'  => $guardian->id,
                        'app_version'  => Str::limit((string) $request->header('X-App-Version'), 24, '') ?: $device->app_version,
                        'last_seen_at' => now(),
                    ])->save();
                }
            }
        }

        return $next($request);
    }
}
