<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientRequest;
use App\Services\PatientService;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    protected $patientService;

    function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    public function index()
    {
        return $this->patientService->getAll();
    }

    //Создает сущность "Пациент" через модель Patient
    public  function store(PatientRequest $request)
    {
        $fields = $request->validated();
        return $this->patientService->createPatient($fields);
    }
}
