<?php

// Debug route for testing patient record data
Route::get('/debug/patient/{id}', function ($id) {
    $user = auth()->user();
    
    if (!$user || !$user->isClinic()) {
        return response()->json(['error' => 'Not a clinic user']);
    }

    $clinicRegistration = $user->clinicRegistration;
    $clinicId = $clinicRegistration->id;

    // Get pet with all related data
    $pet = \App\Models\Pet::with([
        'owner',
        'owner.profile',
        'type',
        'breed',
        'appointments' => function ($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId)
              ->orderBy('scheduled_at', 'desc');
        },
        'medicalRecords' => function ($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId)
              ->orderBy('date', 'desc');
        },
    ])->findOrFail($id);

    // Transform medical records
    $medicalRecords = $pet->medicalRecords->map(function ($record) {
        return [
            'id' => $record->id,
            'date' => $record->date->format('Y-m-d'),
            'formatted_date' => $record->date->format('M j, Y'),
            'type' => $record->record_type,
            'title' => $record->title,
            'description' => $record->description,
            'diagnosis' => $record->description, // For backward compatibility
            'treatment' => $record->instructions,
            'medication' => $record->medication,
            'cost' => $record->cost,
            'formatted_cost' => $record->getCostFormattedAttribute(),
            'veterinarian' => $record->veterinarian ? $record->veterinarian->name : 'Dr. Staff',
            'follow_up_date' => $record->follow_up_date?->format('Y-m-d'),
            'attachments' => $record->attachments ?? [],
            'notes' => $record->instructions,
        ];
    });

    return response()->json([
        'pet_id' => $pet->id,
        'pet_name' => $pet->name,
        'appointments_count' => $pet->appointments->count(),
        'medical_records_count' => $pet->medicalRecords->count(),
        'medical_records' => $medicalRecords,
    ]);
})->middleware(['auth', 'verified', 'clinic']);