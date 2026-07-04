<?php

namespace Database\Seeders;

use App\Models\Worker;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class WorkersSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = Role::query()->where('name', 'admin')->value('id');
        $gerenteRoleId = Role::query()->where('name', 'gerente')->value('id');
        $contadorRoleId = Role::query()->where('name', 'contador')->value('id');
        $recepcionistaRoleId = Role::query()->where('name', 'recepcionista')->value('id');
        $limpiezaRoleId = Role::query()->where('name', 'limpieza')->value('id');

        if (!$adminRoleId || !$gerenteRoleId || !$contadorRoleId || !$recepcionistaRoleId || !$limpiezaRoleId) {
            return;
        }

        $workers = [
            [
                'code' => 'TRB-0001',
                'full_name' => 'KEYSI JEANPIERRE BARDALES VASQUEZ',
                'document_number' => '76636255',
                'phone' => '989112233',
                'email' => 'bvasquezkeysije@uss.edu.pe',
                'address' => 'Urb. Santa Victoria, Chiclayo',
                'role_id' => $adminRoleId,
            ],
            [
                'code' => 'TRB-0002',
                'full_name' => 'DELGADO GARCIA BRIGGITTE LUCERO',
                'document_number' => '76465678',
                'phone' => '981224466',
                'email' => 'dgarciabriggitl@uss.edu.pe',
                'address' => 'Av. Balta 1250, Chiclayo',
                'role_id' => $adminRoleId,
            ],
            [
                'code' => 'TRB-0003',
                'full_name' => 'VASQUEZ QUISPE JORGE TOMAS',
                'document_number' => '72838203',
                'phone' => '982335577',
                'email' => 'vquispejorgetom@uss.edu.pe',
                'address' => 'Urb. Federico Villarreal, Chiclayo',
                'role_id' => $adminRoleId,
            ],
            [
                'code' => 'TRB-0004',
                'full_name' => 'CAPITAN LEON GRABIEL ALEXANDER',
                'document_number' => '73149801',
                'phone' => '983446688',
                'email' => 'cleonalexandgra@uss.edu.pe',
                'address' => 'Av. Luis Gonzales, Chiclayo',
                'role_id' => $adminRoleId,
            ],
            [
                'code' => 'TRB-0005',
                'full_name' => 'MELISSA FERNANDA RUIZ CAMPOS',
                'document_number' => '74231568',
                'phone' => '984557799',
                'email' => 'mruizcampos@plazamoyobanba.com',
                'address' => 'Jr. Ayacucho 410, Chiclayo',
                'role_id' => $gerenteRoleId,
            ],
            [
                'code' => 'TRB-0006',
                'full_name' => 'EDUARDO ANTONIO SALAZAR VEGA',
                'document_number' => '73821456',
                'phone' => '985668811',
                'email' => 'esalazarvega@plazamoyobanba.com',
                'address' => 'Calle Elias Aguirre 890, Chiclayo',
                'role_id' => $contadorRoleId,
            ],
            [
                'code' => 'TRB-0007',
                'full_name' => 'KARLA NOEMI PEREZ HUAMAN',
                'document_number' => '75120349',
                'phone' => '986779922',
                'email' => 'kperezhuaman@plazamoyobanba.com',
                'address' => 'Urb. Los Parques, Chiclayo',
                'role_id' => $recepcionistaRoleId,
            ],
            [
                'code' => 'TRB-0008',
                'full_name' => 'JULIO CESAR ORTIZ VILLAR',
                'document_number' => '75983412',
                'phone' => '987123654',
                'email' => 'jortizvillar@plazamoyobanba.com',
                'address' => 'Av. Grau 1456, Chiclayo',
                'role_id' => $recepcionistaRoleId,
            ],
            [
                'code' => 'TRB-0009',
                'full_name' => 'ROSA ELENA CUBAS PINTADO',
                'document_number' => '77234156',
                'phone' => '988234511',
                'email' => 'rcubas@plazamoyobanba.com',
                'address' => 'Jr. Bolognesi 210, Chiclayo',
                'role_id' => $limpiezaRoleId,
            ],
            [
                'code' => 'TRB-0010',
                'full_name' => 'MANUEL JESUS LLONTOP CHAFLOQUE',
                'document_number' => '78125643',
                'phone' => '989667321',
                'email' => 'mllontop@plazamoyobanba.com',
                'address' => 'Urb. San Eduardo, Chiclayo',
                'role_id' => $limpiezaRoleId,
            ],
        ];

        foreach ($workers as $workerData) {
            $worker = Worker::query()
                ->where('document_number', $workerData['document_number'])
                ->orWhere('email', $workerData['email'])
                ->first();

            if (!$worker) {
                $worker = new Worker();
                $worker->code = $this->nextAvailableCode($workerData['code']);
            } elseif (!$worker->code) {
                $worker->code = $this->nextAvailableCode($workerData['code']);
            }

            $worker->document_number = $workerData['document_number'];
            $worker->full_name = $workerData['full_name'];
            $worker->phone = $workerData['phone'];
            $worker->email = $workerData['email'];
            $worker->address = $workerData['address'];
            $worker->role_id = $workerData['role_id'];
            $worker->is_active = true;
            $worker->save();

            $user = User::query()->where('email', $worker->email)->first();
            if ($user) {
                $user->worker_id = $worker->id;
                $user->name = $worker->full_name;
                $user->save();
            }
        }
    }

    private function nextAvailableCode(string $preferredCode): string
    {
        if (!Worker::query()->where('code', $preferredCode)->exists()) {
            return $preferredCode;
        }

        $next = (int) (Worker::query()->max('id') ?? 0) + 1;
        do {
            $code = sprintf('TRB-%04d', $next);
            $next++;
        } while (Worker::query()->where('code', $code)->exists());

        return $code;
    }
}
