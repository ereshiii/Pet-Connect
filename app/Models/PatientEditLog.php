<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PatientEditLog extends Model
{
    protected $fillable = [
        'patient_id',
        'user_id',
        'clinic_id',
        'action',
        'old_values',
        'new_values',
        'changed_fields',
        'notes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_fields' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the patient that this edit log belongs to.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Pet::class, 'patient_id');
    }

    /**
     * Get the user who made the edit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the clinic where the edit was made.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicRegistration::class, 'clinic_id');
    }

    /**
     * Get a human-readable description of the changes.
     */
    public function getChangeDescriptionAttribute(): string
    {
        if (empty($this->changed_fields)) {
            return 'No specific changes recorded';
        }

        $fieldMappings = [
            'name' => 'patient name',
            'species' => 'species',
            'breed' => 'breed',
            'gender' => 'gender',
            'birth_date' => 'birth date',
            'weight' => 'weight',
            'color' => 'color',
            'microchip_id' => 'microchip ID',
            'notes' => 'medical notes',
            'allergies' => 'allergies',
            'medical_conditions' => 'medical conditions',
            'vaccination_status' => 'vaccination status',
            'owner_name' => 'owner name',
            'owner_email' => 'owner email',
            'owner_phone' => 'owner phone',
            'owner_address' => 'owner address',
            'emergency_contact_name' => 'emergency contact',
            'emergency_contact_phone' => 'emergency contact phone',
        ];

        $readableFields = array_map(function ($field) use ($fieldMappings) {
            return $fieldMappings[$field] ?? $field;
        }, $this->changed_fields);

        if (count($readableFields) === 1) {
            return "Updated {$readableFields[0]}";
        } elseif (count($readableFields) === 2) {
            return "Updated " . implode(' and ', $readableFields);
        } else {
            $lastField = array_pop($readableFields);
            return "Updated " . implode(', ', $readableFields) . ", and {$lastField}";
        }
    }

    /**
     * Get the time elapsed since this edit was made.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get formatted date and time of the edit.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('M j, Y • g:i A');
    }

    /**
     * Get the action in a more readable format.
     */
    public function getActionDisplayAttribute(): string
    {
        return match($this->action) {
            'created' => 'Patient Created',
            'updated' => 'Patient Updated',
            'deleted' => 'Patient Deleted',
            default => ucfirst($this->action)
        };
    }

    /**
     * Scope to get logs for a specific patient.
     */
    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope to get logs for a specific clinic.
     */
    public function scopeForClinic($query, $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to get recent logs.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }
}
