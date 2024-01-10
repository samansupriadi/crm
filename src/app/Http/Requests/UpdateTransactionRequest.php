<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'metode_pembayaran'         => 'required|exists:payment_methods,ulid',
            'pembayaran_via'            => 'required|exists:account_payments,ulid',
            'bukti_donasi[].*'          => 'nullable|mimes:pdf,jpg,png,doc,docx|max:10240',
            'details.*.program_id'      => 'required|exists:programs,ulid',
            'details.*.nominal'         => 'required',
            // 'kuitansi'                  => 'required|string|max:20|' . Rule::unique('transactions','no_kuitansi')->ignore($this->transaction),
        ];
    }
}
