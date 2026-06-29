<?php

namespace App\Http\Requests;

class StoreComplaintRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'consumer_name' => ['required', 'string', 'max:120'],
            'document_type' => ['required', 'string', 'in:dni,ce,pasaporte,ruc'],
            'document_number' => ['required', 'string', 'max:32'],
            'address' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32'],
            'item_type' => ['required', 'string', 'in:producto,servicio'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'complaint_type' => ['required', 'string', 'in:reclamo,queja'],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
            'order_reference' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function attributes(): array
    {
        return [
            ...parent::attributes(),
            'consumer_name' => 'nombre del consumidor',
            'document_type' => 'tipo de documento',
            'document_number' => 'número de documento',
            'address' => 'domicilio',
            'phone' => 'teléfono',
            'item_type' => 'tipo de bien contratado',
            'amount' => 'monto reclamado',
            'complaint_type' => 'tipo de solicitud',
            'description' => 'detalle del reclamo o queja',
            'order_reference' => 'pedido o referencia',
        ];
    }
}
