<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AddressController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return AddressResource::collection($request->user()->addresses()->latest('is_default')->latest('id')->get());
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $address = $this->persist($request->user(), $request->validated());

        return (new AddressResource($address))->response()->setStatusCode(201);
    }

    public function update(UpdateAddressRequest $request, Address $address): AddressResource
    {
        $this->authorizeAddress($request, $address);

        return new AddressResource($this->persist($request->user(), $request->validated(), $address));
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        $this->authorizeAddress($request, $address);
        $address->delete();

        return response()->json(['message' => 'Alamat dihapus.']);
    }

    private function persist($user, array $data, ?Address $address = null): Address
    {
        if (! empty($data['is_default'])) {
            $user->addresses()->update(['is_default' => false]);
        }

        if ($address) {
            $address->update($data);

            return $address;
        }

        // Alamat pertama otomatis jadi default.
        if ($user->addresses()->count() === 0) {
            $data['is_default'] = true;
        }

        return $user->addresses()->create($data);
    }

    private function authorizeAddress(Request $request, Address $address): void
    {
        abort_unless($address->user_id === $request->user()->id, 403, 'Alamat bukan milik Anda.');
    }
}
