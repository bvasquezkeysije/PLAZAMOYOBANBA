<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuestRegister;
use App\Models\Room;
use Illuminate\Http\Request;

class GuestRegisterController extends Controller
{
    public function index(Request $request)
    {
        $registers = $this->filteredQuery($request)
            ->latest('check_in_at')
            ->paginate(12)
            ->withQueryString();

        $rooms = Room::query()->where('active', true)->orderBy('room_number')->get();

        return view('admin.modules.huespedes', compact('registers', 'rooms'));
    }

    public function print(Request $request)
    {
        $registers = $this->filteredQuery($request)
            ->latest('check_in_at')
            ->get();

        return view('admin.modules.huespedes-print', [
            'registers' => $registers,
            'filters' => $request->only(['q', 'status', 'date_from', 'date_to']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'full_name' => ['required', 'string', 'max:150'],
            'document_type' => ['required', 'in:DNI,CE,PASAPORTE,OTRO'],
            'document_number' => ['required', 'string', 'max:20'],
            'nationality' => ['required', 'string', 'max:80'],
            'check_in_at' => ['required', 'date'],
            'check_out_at' => ['nullable', 'date', 'after:check_in_at'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $status = empty($validated['check_out_at']) ? 'hospedado' : 'salio';

        GuestRegister::create([
            'code' => $this->nextCode(),
            'sale_id' => null,
            'room_id' => $validated['room_id'],
            'created_by' => $request->user()->id,
            'full_name' => $validated['full_name'],
            'document_type' => $validated['document_type'],
            'document_number' => $validated['document_number'],
            'nationality' => mb_strtoupper($validated['nationality']),
            'check_in_at' => $validated['check_in_at'],
            'check_out_at' => $validated['check_out_at'] ?? null,
            'status' => $status,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Huésped registrado correctamente.');
    }

    public function checkout(GuestRegister $register)
    {
        if ($register->status === 'salio') {
            return back()->with('success', 'Este huésped ya tiene salida registrada.');
        }

        $register->update([
            'check_out_at' => now(),
            'status' => 'salio',
        ]);

        return back()->with('success', 'Salida del huésped registrada correctamente.');
    }

    private function nextCode(): string
    {
        do {
            $code = 'HSP-' . strtoupper(substr(uniqid(), -6));
        } while (GuestRegister::query()->where('code', $code)->exists());

        return $code;
    }

    private function filteredQuery(Request $request)
    {
        return GuestRegister::query()
            ->with(['room', 'creator'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->q;
                $query->where(function ($sub) use ($q) {
                    $sub->where('code', 'like', "%{$q}%")
                        ->orWhere('full_name', 'like', "%{$q}%")
                        ->orWhere('document_number', 'like', "%{$q}%")
                        ->orWhereHas('room', fn ($room) => $room->where('room_number', 'like', "%{$q}%"));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('check_in_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('check_in_at', '<=', $request->date_to));
    }
}
