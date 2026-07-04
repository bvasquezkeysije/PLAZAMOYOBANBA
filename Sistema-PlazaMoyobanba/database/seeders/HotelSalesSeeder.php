<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\GuestRegister;
use App\Models\PaymentType;
use App\Models\Product;
use App\Models\Room;
use App\Models\RoomRental;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HotelSalesSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->orderBy('id')->first();
        $rooms = Room::query()->where('active', true)->get();
        $products = Product::query()->where('active', true)->get();
        $paymentTypes = PaymentType::query()->where('is_active', true)->get();

        if (!$user || $rooms->isEmpty() || $products->isEmpty() || $paymentTypes->isEmpty()) {
            return;
        }

        $consumerFinal = Client::query()->updateOrCreate(
            ['code' => 'CLI-CFINAL'],
            [
                'dni' => '99999999',
                'full_name' => 'CONSUMIDOR FINAL',
                'email' => null,
                'phone' => null,
                'active' => true,
            ]
        );

        $clients = Client::query()->where('active', true)->where('id', '!=', $consumerFinal->id)->get();
        if ($clients->isEmpty()) {
            return;
        }

        GuestRegister::query()->delete();
        RoomRental::query()->delete();
        SaleItem::query()->delete();
        Sale::query()->delete();

        $guestFirstNames = ['Luis', 'Jose', 'Carlos', 'Miguel', 'Pedro', 'Jorge', 'Marco', 'Diego', 'Kevin', 'Andres', 'Maria', 'Rosa', 'Lucia', 'Camila', 'Andrea', 'Sofia', 'Paola', 'Valeria', 'Mariana', 'Fiorella'];
        $guestMiddleNames = ['Alberto', 'Enrique', 'Antonio', 'Javier', 'Manuel', 'David', 'Fernanda', 'Patricia', 'Estefania', 'Lorena'];
        $guestLastNames = ['Vasquez', 'Garcia', 'Torres', 'Flores', 'Mendoza', 'Castillo', 'Chavez', 'Diaz', 'Sanchez', 'Rodriguez', 'Fernandez', 'Ramos', 'Lopez', 'Vera', 'Huaman', 'Silva', 'Paredes', 'Ruiz', 'Campos', 'Cruz'];

        for ($i = 1; $i <= 120; $i++) {
            // Base: 52 con habitacion + 28 solo productos (primeras 80).
            // Extra: 40 adicionales (81-120) siempre con habitacion y huesped registrado.
            $withRoom = $i <= 52 || $i > 80;
            $client = $this->chance(25) ? $consumerFinal : $clients->random();
            $paymentType = $paymentTypes->random();
            $createdAt = Carbon::now()
                ->subDays(random_int(0, 40))
                ->setTime(random_int(7, 23), random_int(0, 59));

            $isFactura = $client->id !== $consumerFinal->id && $this->chance(15);
            $documentType = $isFactura ? 'factura' : 'boleta';
            $series = $isFactura ? 'F001' : 'B001';

            $sale = Sale::query()->create([
                'code' => sprintf('VTA-%04d', $i),
                'document_type' => $documentType,
                'series' => $series,
                'correlative' => $i,
                'client_id' => $client->id,
                'user_id' => $user->id,
                'payment_type_id' => $paymentType->id,
                'status' => 'paid',
                'total' => 0,
                'subtotal' => 0,
                'igv' => 0,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $total = 0;

            if ($withRoom) {
                $room = $rooms->random();
                $useDaily = $this->chance(35);

                if ($useDaily) {
                    $days = random_int(1, 3);
                    $rate = max(40, (float) $room->daily_rate + random_int(-10, 20));
                    $startAt = (clone $createdAt);
                    $endAt = (clone $startAt)->addDays($days);
                    $hours = max(1, $startAt->diffInHours($endAt));
                    $rentalSubtotal = round($days * $rate, 2);
                } else {
                    $hours = random_int(2, 14);
                    $rate = max(15, (float) $room->hourly_rate + random_int(-2, 5));
                    $startAt = (clone $createdAt);
                    $endAt = (clone $startAt)->addHours($hours);
                    $days = max(1, (int) ceil($hours / 24));
                    $rentalSubtotal = round($hours * $rate, 2);
                }

                RoomRental::query()->create([
                    'sale_id' => $sale->id,
                    'room_id' => $room->id,
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'hours' => $hours,
                    'days' => $days,
                    'rate' => $rate,
                    'subtotal' => $rentalSubtotal,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $guestName = $client->id === $consumerFinal->id
                    ? $this->buildGuestName($guestFirstNames, $guestMiddleNames, $guestLastNames)
                    : $client->full_name;

                $guestDocument = $client->id === $consumerFinal->id
                    ? (string) random_int(40000000, 79999999)
                    : $client->dni;

                GuestRegister::query()->create([
                    'code' => sprintf('HSP-%05d', $i),
                    'sale_id' => $sale->id,
                    'room_id' => $room->id,
                    'created_by' => $user->id,
                    'full_name' => $guestName,
                    'document_type' => 'DNI',
                    'document_number' => $guestDocument,
                    'nationality' => 'PERUANA',
                    'check_in_at' => $startAt,
                    'check_out_at' => $endAt,
                    'status' => 'salio',
                    'notes' => 'Registro demo generado automaticamente',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $total += $rentalSubtotal;
            }

            $lineCount = $withRoom ? random_int(0, 3) : random_int(1, 4);
            $pickedProducts = $products->shuffle()->take($lineCount);

            foreach ($pickedProducts as $product) {
                $qty = random_int(1, 4);
                $unitPrice = round(max(0.5, (float) $product->price + (random_int(-20, 20) / 10)), 2);
                $lineSubtotal = round($qty * $unitPrice, 2);

                SaleItem::query()->create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineSubtotal,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $total += $lineSubtotal;
            }

            [$base, $igv] = $this->splitTax($total);
            $sale->update([
                'total' => $total,
                'subtotal' => $base,
                'igv' => $igv,
            ]);
        }
    }

    private function splitTax(float $total): array
    {
        $base = round($total / 1.18, 2);
        $igv = round($total - $base, 2);

        return [$base, $igv];
    }

    private function buildGuestName(array $firstNames, array $middleNames, array $lastNames): string
    {
        $first = $firstNames[array_rand($firstNames)];
        $middle = $middleNames[array_rand($middleNames)];
        $last1 = $lastNames[array_rand($lastNames)];
        $last2 = $lastNames[array_rand($lastNames)];

        if ($last1 === $last2) {
            $last2 = $lastNames[(array_search($last1, $lastNames, true) + 3) % count($lastNames)];
        }

        return trim($first . ' ' . $middle . ' ' . $last1 . ' ' . $last2);
    }

    private function chance(int $percent): bool
    {
        return random_int(1, 100) <= max(0, min(100, $percent));
    }
}
