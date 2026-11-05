<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.profile', [
            'user' => $request->user(),
            'addresses' => $request->user()->addresses()->orderByDesc('is_default')->latest('id')->get(),
        ]);
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipient' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
        ]);

        $user = $request->user();

        if ($request->boolean('is_default') || $user->addresses()->count() === 0) {
            $user->addresses()->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        $user->addresses()->create($validated);

        return redirect()->route('profile.index')->with('status', 'Alamat ditambahkan.');
    }

    public function updateAddress(Request $request, Address $address): RedirectResponse
    {
        $this->authorizeAddress($request, $address);

        $validated = $request->validate([
            'recipient' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
        ]);

        if ($request->boolean('is_default')) {
            $request->user()->addresses()->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        $address->update($validated);

        return redirect()->route('profile.index')->with('status', 'Alamat diperbarui.');
    }

    public function destroyAddress(Request $request, Address $address): RedirectResponse
    {
        $this->authorizeAddress($request, $address);
        $address->delete();

        return redirect()->route('profile.index')->with('status', 'Alamat dihapus.');
    }

    private function authorizeAddress(Request $request, Address $address): void
    {
        abort_unless($address->user_id === $request->user()->id, 403);
    }
}
