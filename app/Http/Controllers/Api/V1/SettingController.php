<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends BaseApiController
{
    public function index(): JsonResponse
    {
        return $this->success(Setting::orderBy('key')->get());
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'settings' => ['required', 'array', 'min:1'],
            'settings.*.key' => ['required', 'string', 'max:100'],
            'settings.*.value' => ['nullable'],
            'settings.*.type' => ['required', 'in:string,number,boolean,json'],
        ]);

        foreach ($data['settings'] as $entry) {
            Setting::updateOrCreate(
                ['key' => $entry['key']],
                ['value' => is_array($entry['value']) ? json_encode($entry['value']) : (string) $entry['value'], 'type' => $entry['type']]
            );
        }

        return $this->success(Setting::orderBy('key')->get(), 'Settings updated.');
    }
}
