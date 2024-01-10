<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
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
            'donatur'                   => 'required|exists:donors,ulid',
            'metode_pembayaran'         => 'required|exists:payment_methods,ulid',
            // 'kuitansi'                  => 'required|string|max:20|unique:transactions,no_kuitansi',
            'pembayaran_via'            => 'required|exists:account_payments,ulid',
            'bukti_donasi[].*'          => 'nullable|mimes:pdf,jpg,png,doc,docx|max:10240',
            'details.*.program_id'      => 'required|exists:programs,ulid',
            'details.*.nominal'         => 'required',
            'details.*.linked_to'       => 'nullable|exists:transaction_details,ulid',
        ];
    }
}
