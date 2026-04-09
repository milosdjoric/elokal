<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShipmentResource;
use App\Models\Carrier;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function index(Order $order): JsonResponse
    {
        return response()->json([
            'data' => ShipmentResource::collection($order->shipments()->latest()->get()),
        ]);
    }

    public function store(Request $request, Order $order): ShipmentResource
    {
        $request->validate([
            'tracking_number' => 'nullable|string|max:255',
            'carrier' => 'nullable|string|max:100',
            'carrier_url' => 'nullable|url|max:500',
            'weight' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Auto-generisanje tracking URL-a iz Carrier konfiguracije
        $carrierUrl = $request->carrier_url;
        if (! $carrierUrl && $request->carrier && $request->tracking_number) {
            $carrier = Carrier::where('code', $request->carrier)->first();
            if ($carrier) {
                $carrierUrl = $carrier->getTrackingUrl($request->tracking_number);
            }
        }

        $shipment = $order->shipments()->create([
            'tracking_number' => $request->tracking_number,
            'carrier' => $request->carrier,
            'carrier_url' => $carrierUrl,
            'status' => 'pending',
            'weight' => $request->weight,
            'notes' => $request->notes,
        ]);

        // Sinhronizuj tracking na order nivo
        if ($request->tracking_number) {
            $order->update([
                'tracking_number' => $request->tracking_number,
                'tracking_carrier' => $request->carrier,
                'tracking_url' => $carrierUrl,
            ]);
        }

        $order->timeline()->create([
            'status' => $order->status,
            'old_status' => $order->status,
            'note' => 'Pošiljka kreirana' . ($request->tracking_number ? ": {$request->carrier} — {$request->tracking_number}" : ''),
            'actor_type' => 'admin',
            'actor_id' => $request->user()->id,
        ]);

        return new ShipmentResource($shipment);
    }

    public function update(Request $request, Shipment $shipment): ShipmentResource
    {
        $request->validate([
            'tracking_number' => 'nullable|string|max:255',
            'carrier' => 'nullable|string|max:100',
            'carrier_url' => 'nullable|url|max:500',
            'weight' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $shipment->update($request->only(['tracking_number', 'carrier', 'carrier_url', 'weight', 'notes']));

        return new ShipmentResource($shipment);
    }

    public function updateStatus(Request $request, Shipment $shipment): ShipmentResource
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Shipment::STATUSES),
        ]);

        $oldStatus = $shipment->status;
        $newStatus = $request->status;

        $updates = ['status' => $newStatus];

        if ($newStatus === 'picked_up' && ! $shipment->shipped_at) {
            $updates['shipped_at'] = now();
        }
        if ($newStatus === 'delivered' && ! $shipment->delivered_at) {
            $updates['delivered_at'] = now();
        }

        $shipment->update($updates);

        // Ažuriraj order status na osnovu shipment statusa
        $order = $shipment->order;
        if ($newStatus === 'picked_up' || $newStatus === 'in_transit') {
            if (! in_array($order->status, ['shipped', 'delivered', 'completed', 'cancelled', 'refunded'])) {
                $order->update(['status' => 'shipped']);
            }
        }
        if ($newStatus === 'delivered') {
            $allDelivered = $order->shipments()->where('status', '!=', 'delivered')->doesntExist();
            if ($allDelivered) {
                $order->update(['status' => 'delivered']);
            }
        }

        $order->timeline()->create([
            'status' => $order->status,
            'old_status' => $order->getOriginal('status') ?? $order->status,
            'note' => "Pošiljka #{$shipment->id}: {$oldStatus} → {$newStatus}",
            'actor_type' => 'admin',
            'actor_id' => $request->user()->id,
        ]);

        return new ShipmentResource($shipment->fresh());
    }

    public function destroy(Shipment $shipment): JsonResponse
    {
        $shipment->delete();

        return response()->json(['message' => 'Pošiljka obrisana.']);
    }
}
