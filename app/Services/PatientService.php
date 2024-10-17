<?php

namespace App\Services;

use App\Models\Patient;
use App\Repositories\PatientRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
class PatientService {

    protected $patientRepository;
    function __construct(PatientRepository $patientRepository)
    {
        $this->patientRepository = $patientRepository;
    }

    //запросом из роута нужно выгрузить список сущностей и также задействовать кеш (если есть и не просрочился).
    public function getAll(){
        $patients = Cache::get('patients');

        if (!$patients) {
            $patients = $this->patientRepository->getAll();
            Cache::put('patients', $patients, 300);
        }

        return response()->json($this->getFormatPatient($patients));
    }

    public function createPatient($fields)
    {
        $age = $this->getAge($fields['birthday']);
        $ageType = $this->getAgeType($fields['birthday']);

        $fields = array_merge($fields, ['age' => $age, 'age_type' => $ageType]);

        $patient = $this->patientRepository->createPatient($fields);

        // кеширование сущности на 5 минут
        Cache::put('patient_' . $patient->id, $patient, 300);

        // отправка в очередь (действие обработчика не требуется)
        Queue::push(new ProcessPatient($patient));

        return response()->json($patient, 201);
    }

    //Выгружается полями name (конкатенация first_name + last_name), дата рождения в формате (d.m.Y) и
    //возвраст в формате "4 день" (склонять не надо)
    public  function getFormatPatient($patients)
    {
        if(isset($patients) && is_array($patients) && count($patients)){
            $patients = $patients->map(function ($patient) {
                return [
                    'name' => "{$patient->first_name} {$patient->last_name}",
                    'birthdate' => $patient->birthdate->format('d.m.Y'),
                    'age' => "{$patient->age} {$patient->age_type}",
                ];
            });
        }
        return $patients;
    }

    //поля age(int возраст) и age_type(char день/месяц/год) заполняются в зависимости от
    //пришеднего в контроллер birthdate(date дата рождения). Если возраст меньше месяца,
    //то это дни. Если меньше года, то месяцы
    public function getAge($birthdate)
    {
        $birthdate = Carbon::parse($birthdate);
        $now = Carbon::now();
        $ageInDays = $now->diffInDays($birthdate);

        $age = floor($ageInDays / 365);

        if ($ageInDays < 30) {
            $age = $ageInDays;
        } elseif ($ageInDays < 365)
            $age = floor($ageInDays / 30);

        return $age;
    }

    public function getAgeType($birthdate)
    {
        $birthdate = Carbon::parse($birthdate);
        $now = Carbon::now();
        $ageInDays = $now->diffInDays($birthdate);
        $age_type = 'год';


        if ($ageInDays < 30)
            $age_type = 'день';
        elseif ($ageInDays < 365)
            $age_type = 'месяц';

        return $age_type;
    }

}
