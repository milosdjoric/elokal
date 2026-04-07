<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressFormRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()->orderByDesc('is_default')->get();

        return response()->json([
            'data' => AddressResource::collection($addresses),
        ]);
    }

    public function store(AddressFormRequest $request): JsonResponse
    {
        if ($request->boolean('is_default')) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $isFirst = $request->user()->addresses()->count() === 0;

        $address = $request->user()->addresses()->create(
            array_merge($request->validated(), ['is_default' => $request->boolean('is_default') || $isFirst])
        );

        return (new AddressResource($address))
            ->response()
            ->setStatusCode(201);
    }

    public function update(AddressFormRequest $request, Address $address): AddressResource
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($request->boolean('is_default')) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($request->validated());

        return new AddressResource($address);
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $request->user()->addresses()->first()?->update(['is_default' => true]);
        }

        return response()->json(['message' => 'Adresa obrisana.']);
    }
}
