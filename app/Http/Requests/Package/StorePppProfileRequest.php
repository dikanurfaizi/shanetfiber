<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;

class StorePppProfileRequest extends FormRequest
{
    /**
     * Izinkan akses request.
     */
    public function authorize(): bool
    {
        return true; // Diubah dari false menjadi true
    }

    /**
     * Aturan validasi input form.
     */
    public function rules(): array
    {
        return [
            'profile_name'        => 'required|string|unique:ppp_profiles,profile_name',
            'use_encryption'      => 'required|string',
            'max_upload'          => 'required_if:rate_limit,true',
            'max_download'        => 'required_if:rate_limit,true',
            'rasio_cir'           => 'required|numeric',
            'priority'            => 'required|integer|between:1,8',
            'dns_server'          => 'nullable|string',
            'local_address'       => 'nullable|string',
            'remote_address'      => 'nullable|string',
            'parent_queue'        => 'nullable|string',
            'queue_type'          => 'nullable|string',
            'insert_before'       => 'nullable|string',
            'on_up'               => 'nullable|string',
            'on_down'             => 'nullable|string',
            'change_tcp_mss'      => 'nullable|string',
            'use_upnp'            => 'nullable|string',
            'comment'             => 'nullable|string',
            'only_one'            => 'nullable|string',
            'burst_upload'        => 'nullable|string',
            'burst_download'      => 'nullable|string',
            'threshold_upload'    => 'nullable|string',
            'threshold_download'  => 'nullable|string',
            'burst_time_upload'   => 'nullable|string',
            'burst_time_download' => 'nullable|string',
            'limit_at_upload'     => 'nullable|string',
            'limit_at_download'   => 'nullable|string',
        ];
    }

    /**
     * Custom pesan error validasi.
     */
    public function messages(): array
    {
        return [
            'profile_name.required'   => 'Profile name is required.',
            'profile_name.unique'     => 'Profile name already exists.',
            'max_upload.required_if'   => 'Maximum upload is required when rate limit is enabled.',
            'max_download.required_if' => 'Maximum download is required when rate limit is enabled.',
            'use_encryption.required' => 'Encryption setting is required.',
            'priority.required'       => 'Priority is required.',
            'rasio_cir.required'      => 'CIR ratio is required.',
        ];
    }
}
