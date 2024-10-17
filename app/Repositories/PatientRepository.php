<?php

namespace App\Repositories;

use App\Models\Patient;

class PatientRepository
{
    public function getAll()
    {
        return Patient::all();
    }

    public function createPatient($fields)
    {
        return new Patient($fields);
    }
}
