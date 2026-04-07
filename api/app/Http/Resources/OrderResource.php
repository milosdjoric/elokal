<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'email' => $this->email,
            'phone' => $this->phone,
            'shipping' => [
                'first_name' => $this->shipping_first_name,
                'last_name' => $this->shipping_last_name,
                'company' => $this->shipping_company,
                'address_line_1' => $this->shipping_address_line_1,
                'address_line_2' => $this->shipping_address_line_2,
                'city' => $this->shipping_city,
                'postal_code' => $this->shipping_postal_code,
                'country' => $this->shipping_country,
            ],
            'subtotal' => $this->subtotal,
            'shipping_cost' => $this->shipping_cost,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'total' => $this->total,
            'refunded_amount' => $this->refunded_amount,
            'refund_reason' => $this->refund_reason,
            'tracking' => [
                'number' => $this->tracking_number,
                'carrier' => $this->tracking_carrier,
                'url' => $this->tracking_url,
            ],
            'notes' => $this->notes,
            'admin_notes' => $this->when($request->routeIs('admin.*') || $request->is('api/admin/*'), $this->admin_notes),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'timeline' => OrderTimelineResource::collection($this->whenLoaded('timeline')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
