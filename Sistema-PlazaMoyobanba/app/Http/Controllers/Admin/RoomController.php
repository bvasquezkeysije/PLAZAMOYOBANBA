<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::query()->with('floor');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('room_number', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('type', 'like', "%{$q}%");
            });
        }

        if ($request->filled('floor_id')) {
            $query->where('floor_id', (int) $request->floor_id);
        }

        if ($request->filled('active')) {
            $query->where('active', (bool) $request->active);
        }

        if ($request->filled('price_max')) {
            $query->where('hourly_rate', '<=', (float) $request->price_max);
        }

        $rooms = $query->latest()->paginate(10)->withQueryString();
        $floors = Floor::query()->orderBy('number')->get();
        $roomTypes = RoomType::query()->where('active', true)->orderBy('name')->get();
        $roomTypeRows = RoomType::query()->orderBy('name')->get();

        return view('admin.modules.habitaciones', compact('rooms', 'floors', 'roomTypes', 'roomTypeRows'));
    }

    public function storeFloor(Request $request)
    {
        $validated = $request->validateWithBag('floor', [
            'name' => ['required', 'string', 'max:100'],
            'number' => ['required', 'integer', 'min:1', 'max:200', 'unique:floors,number'],
        ]);

        Floor::create([
            'code' => $this->nextFloorCode(),
            'name' => $validated['name'],
            'number' => $validated['number'],
        ]);

        return back()
            ->with('floor_success', 'Piso registrado correctamente.')
            ->with('open_floors_modal', true);
    }

    public function storeType(Request $request)
    {
        $validated = $request->validateWithBag('roomType', [
            'name' => ['required', 'string', 'max:100', 'unique:room_types,name'],
        ]);

        RoomType::create([
            'code' => $this->nextRoomTypeCode(),
            'name' => $validated['name'],
            'active' => true,
        ]);

        return back()
            ->with('room_type_success', 'Tipo de habitación registrado correctamente.')
            ->with('open_types_modal', true);
    }

    public function updateType(Request $request, RoomType $type)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100', 'unique:room_types,name,'.$type->id],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'roomType')
                ->withInput()
                ->with('open_types_modal', true)
                ->with('open_edit_type_modal', true)
                ->with('edit_type', [
                    'id' => $type->id,
                    'name' => $request->input('name', $type->name),
                ]);
        }

        $oldName = $type->name;
        $newName = $validator->validated()['name'];

        $type->update(['name' => $newName]);
        Room::where('type', $oldName)->update(['type' => $newName]);

        return back()
            ->with('room_type_success', 'Tipo de habitación actualizado correctamente.')
            ->with('open_types_modal', true);
    }

    public function storeRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'floor_id' => ['required', 'integer', 'exists:floors,id'],
            'room_number' => ['required', 'string', 'max:20', 'unique:rooms,room_number'],
            'type' => ['required', 'string', 'max:50'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'daily_rate' => ['nullable', 'numeric', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'room')
                ->withInput()
                ->with('open_room_modal', true);
        }

        $validated = $validator->validated();

        Room::create([
            'code' => $this->nextRoomCode(),
            'floor_id' => (int) $validated['floor_id'],
            'room_number' => $validated['room_number'],
            'type' => $validated['type'],
            'hourly_rate' => $validated['hourly_rate'] ?? null,
            'daily_rate' => $validated['daily_rate'] ?? null,
            'active' => (bool) ($validated['active'] ?? true),
        ]);

        return back()
            ->with('room_success', 'Habitación registrada correctamente.')
            ->with('open_room_modal', false);
    }

    public function updateRoom(Request $request, Room $room)
    {
        $validator = Validator::make($request->all(), [
            'floor_id' => ['required', 'integer', 'exists:floors,id'],
            'room_number' => ['required', 'string', 'max:20', 'unique:rooms,room_number,'.$room->id],
            'type' => ['required', 'string', 'max:50'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'daily_rate' => ['nullable', 'numeric', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'room')
                ->withInput()
                ->with('open_room_modal', false)
                ->with('open_edit_room_modal', true)
                ->with('edit_room', [
                    'id' => $room->id,
                    'floor_id' => $request->input('floor_id', $room->floor_id),
                    'room_number' => $request->input('room_number', $room->room_number),
                    'type' => $request->input('type', $room->type),
                    'hourly_rate' => $request->input('hourly_rate', $room->hourly_rate),
                    'daily_rate' => $request->input('daily_rate', $room->daily_rate),
                    'active' => (string) $request->input('active', $room->active ? '1' : '0'),
                ]);
        }

        $validated = $validator->validated();

        $room->update([
            'floor_id' => (int) $validated['floor_id'],
            'room_number' => $validated['room_number'],
            'type' => $validated['type'],
            'hourly_rate' => $validated['hourly_rate'] ?? null,
            'daily_rate' => $validated['daily_rate'] ?? null,
            'active' => (bool) ($validated['active'] ?? true),
        ]);

        return back()->with('room_success', 'Habitación actualizada correctamente.');
    }

    public function toggleRoomStatus(Room $room)
    {
        $room->update([
            'active' => !$room->active,
        ]);

        return back()->with('room_success', $room->active ? 'Habitación activada correctamente.' : 'Habitación desactivada correctamente.');
    }

    public function updateFloor(Request $request, Floor $floor)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100'],
            'number' => ['required', 'integer', 'min:1', 'max:200', 'unique:floors,number,'.$floor->id],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'floor')
                ->withInput()
                ->with('open_floors_modal', true)
                ->with('open_edit_floor_modal', true)
                ->with('edit_floor', [
                    'id' => $floor->id,
                    'name' => $request->input('name', $floor->name),
                    'number' => $request->input('number', $floor->number),
                ]);
        }

        $validated = $validator->validated();

        $floor->update([
            'name' => $validated['name'],
            'number' => $validated['number'],
        ]);

        return back()
            ->with('floor_success', 'Piso actualizado correctamente.')
            ->with('open_floors_modal', true)
            ->with('open_edit_floor_modal', false);
    }

    private function nextFloorCode(): string
    {
        do {
            $code = 'PIS-' . strtoupper(substr(uniqid(), -4));
        } while (Floor::where('code', $code)->exists());

        return $code;
    }

    private function nextRoomCode(): string
    {
        do {
            $code = 'HAB-' . strtoupper(substr(uniqid(), -5));
        } while (Room::where('code', $code)->exists());

        return $code;
    }

    private function nextRoomTypeCode(): string
    {
        do {
            $code = 'TIP-' . strtoupper(substr(uniqid(), -4));
        } while (RoomType::where('code', $code)->exists());

        return $code;
    }
}
