<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\PetBreed;
use App\Models\PetType;
use App\Models\PetMedicalRecord;
use App\Models\PetVaccination;
use App\Models\PetHealthCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PetController extends Controller
{
    /**
     * Display a listing of pets for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Pet::with(['breed', 'type', 'medicalRecords', 'vaccinations', 'healthConditions'])
            ->where('owner_id', $user->id)
            ->active();

        // Apply filters
        if ($request->filled('species')) {
            $query->ofSpecies($request->species);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('species', 'like', "%{$search}%")
                  ->orWhereHas('breed', function ($breedQuery) use ($search) {
                      $breedQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $pets = $query->orderBy('name')->get()->map(function ($pet) {
            return [
                'id' => $pet->id,
                'name' => $pet->name,
                'species' => $pet->species,
                'breed' => $pet->breed_id && $pet->breed ? $pet->breed->name : ($pet->breed ?? null),
                'breed_id' => $pet->breed_id,
                'type' => $pet->type ? $pet->type->name : null,
                'type_id' => $pet->type_id,
                'gender' => $pet->gender,
                'gender_display' => $pet->gender_display,
                'age' => $pet->age,
                'age_in_years' => $pet->age_in_years,
                'birth_date' => $pet->birth_date?->format('Y-m-d'),
                'weight' => $pet->weight,
                'size' => $pet->size,
                'size_display' => $pet->size_display,
                'color' => $pet->color,
                'markings' => $pet->markings,
                'microchip_number' => $pet->microchip_number,
                'is_neutered' => $pet->is_neutered,
                'special_needs' => $pet->special_needs,
                'profile_image' => $pet->profile_image,
                'images' => $pet->images,
                'health_status' => $pet->health_status,
                'display_name' => $pet->display_name,
                'created_at' => $pet->created_at,
                'updated_at' => $pet->updated_at,
                // Related data counts
                'medical_records_count' => $pet->medicalRecords->count(),
                'vaccinations_count' => $pet->vaccinations->count(),
                'active_health_conditions_count' => $pet->activeHealthConditions->count(),
                'next_appointment' => null, // You can add this if appointments are implemented
            ];
        });

        // Get summary statistics
        $stats = [
            'total_pets' => $pets->count(),
            'dogs' => $pets->where('species', 'dog')->count(),
            'cats' => $pets->where('species', 'cat')->count(),
            'other_species' => $pets->whereNotIn('species', ['dog', 'cat'])->count(),
            'pets_needing_attention' => $pets->where('health_status.overall', 'needs_attention')->count(),
        ];

        return Inertia::render('pets/Pet', [
            'pets' => $pets,
            'stats' => $stats,
            'filters' => $request->only(['species', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new pet.
     */
    public function create()
    {
        $breeds = PetBreed::orderBy('name')->get(['id', 'name', 'species', 'characteristics']);
        $types = PetType::orderBy('name')->get(['id', 'name', 'species', 'description']);

        return Inertia::render('pets/addPet', [
            'breeds' => $breeds,
            'types' => $types,
            'species_options' => [
                'dog' => 'Dog',
                'cat' => 'Cat',
                'bird' => 'Bird',
                'rabbit' => 'Rabbit',
                'hamster' => 'Hamster',
                'guinea_pig' => 'Guinea Pig',
                'reptile' => 'Reptile',
                'fish' => 'Fish',
                'other' => 'Other',
            ],
            'gender_options' => [
                'male' => 'Male',
                'female' => 'Female',
                'unknown' => 'Unknown',
            ],
            'size_options' => [
                'tiny' => 'Tiny',
                'small' => 'Small',
                'medium' => 'Medium',
                'large' => 'Large',
                'giant' => 'Giant',
            ],
        ]);
    }

    /**
     * Store a newly created pet in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required|string|max:50',
            'breed_id' => 'nullable|exists:pet_breeds,id',
            'type_id' => 'nullable|exists:pet_types,id',
            'gender' => 'required|in:male,female,unknown',
            'birth_date' => 'nullable|date|before_or_equal:today',
            'weight' => 'nullable|numeric|min:0|max:500',
            'size' => 'nullable|in:tiny,small,medium,large,giant',
            'color' => 'nullable|string|max:100',
            'markings' => 'nullable|string|max:500',
            'microchip_number' => 'nullable|string|max:50|unique:pets,microchip_number',
            'is_neutered' => 'boolean',
            'special_needs' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validated['owner_id'] = Auth::id();
        $validated['is_active'] = true;

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request->file('profile_image')
                ->store('pets/profile-images', 'public');
        }

        // Handle additional images upload
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('pets/images', 'public');
            }
            $validated['images'] = $images;
        }

        $pet = Pet::create($validated);

        return redirect()->route('petsShow', $pet)
            ->with('success', "Pet '{$pet->name}' has been registered successfully!");
    }

    /**
     * Display the specified pet.
     */
    public function show(Pet $pet)
    {
        // Ensure the user owns this pet
        if ($pet->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized access to pet information.');
        }

        $pet->load([
            'breed',
            'type',
            'owner.profile',
            'medicalRecords.clinicRegistration',
            'vaccinations.clinicRegistration',
            'healthConditions.clinicRegistration',
            'appointments.clinic'
        ]);

        $petData = [
            'id' => $pet->id,
            'name' => $pet->name,
            'species' => $pet->species,
                'breed' => $pet->breed ? [
                    'id' => $pet->breed->id,
                    'name' => $pet->breed->name,
                    'species' => $pet->breed->species,
                    'size_category' => $pet->breed->size_category,
                    'size_category_display' => $pet->breed->size_category_display,
                    'temperament' => $pet->breed->temperament,
                    'life_expectancy_range' => $pet->breed->life_expectancy_range,
                    'weight_range' => $pet->breed->weight_range,
                    'height_range' => $pet->breed->height_range,
                    'common_health_issues' => $pet->breed->common_health_issues,
                    'grooming_requirements' => $pet->breed->grooming_requirements,
                    'exercise_requirements' => $pet->breed->exercise_requirements,
                    'description' => $pet->breed->description,
                ] : null,
            'type' => $pet->type ? [
                'id' => $pet->type->id,
                'name' => $pet->type->name,
                'description' => $pet->type->description,
            ] : null,
            'gender' => $pet->gender,
            'gender_display' => $pet->gender_display,
            'age' => $pet->age,
            'age_in_years' => $pet->age_in_years,
            'birth_date' => $pet->birth_date?->format('Y-m-d'),
            'weight' => $pet->weight,
            'size' => $pet->size,
            'size_display' => $pet->size_display,
            'color' => $pet->color,
            'markings' => $pet->markings,
            'microchip_number' => $pet->microchip_number,
            'is_neutered' => $pet->is_neutered,
            'special_needs' => $pet->special_needs,
            'notes' => $pet->notes,
            'profile_image' => $pet->profile_image,
            'images' => $pet->images,
            'health_status' => $pet->health_status,
            'display_name' => $pet->display_name,
            'created_at' => $pet->created_at,
            'updated_at' => $pet->updated_at,
            'owner' => [
                'id' => $pet->owner->id,
                'name' => $pet->owner->name,
                'email' => $pet->owner->email,
                'profile' => $pet->owner->profile ? [
                    'first_name' => $pet->owner->profile->first_name,
                    'last_name' => $pet->owner->profile->last_name,
                    'phone' => $pet->owner->phone,
                ] : null,
            ],
        ];

        // Get medical records with clinic information
        $medicalRecords = $pet->medicalRecords->map(function ($record) {
            return [
                'id' => $record->id,
                'visit_date' => $record->visit_date?->format('Y-m-d'),
                'visit_type' => $record->visit_type,
                'visit_type_display' => $record->visit_type_display,
                'chief_complaint' => $record->chief_complaint,
                'diagnosis' => $record->diagnosis,
                'treatment_provided' => $record->treatment_provided,
                'medications_prescribed' => $record->medications_prescribed,
                'veterinarian_name' => $record->veterinarian_name,
                'clinic_name' => $record->clinic_name,
                'next_visit_date' => $record->next_visit_date?->format('Y-m-d'),
                'weight_recorded' => $record->weight_recorded,
                'cost_formatted' => $record->cost_formatted,
                'is_emergency' => $record->is_emergency,
                'vital_signs' => $record->vital_signs,
                'days_since_visit' => $record->days_since_visit,
                'clinic' => $record->clinicRegistration ? [
                    'id' => $record->clinicRegistration->id,
                    'clinic_name' => $record->clinicRegistration->clinic_name,
                    'phone_number' => $record->clinicRegistration->phone,
                ] : null,
            ];
        })->sortByDesc('visit_date')->values();

        // Get vaccinations with clinic information
        $vaccinations = $pet->vaccinations->map(function ($vaccination) {
            return [
                'id' => $vaccination->id,
                'vaccine_name' => $vaccination->vaccine_name,
                'vaccine_type' => $vaccination->vaccine_type,
                'administered_date' => $vaccination->administered_date?->format('Y-m-d'),
                'expiry_date' => $vaccination->expiry_date?->format('Y-m-d'),
                'next_due_date' => $vaccination->next_due_date?->format('Y-m-d'),
                'veterinarian_name' => $vaccination->veterinarian_name,
                'clinic_name' => $vaccination->clinic_name,
                'status' => $vaccination->status,
                'is_expired' => $vaccination->is_expired,
                'is_due_soon' => $vaccination->is_due_soon,
                'days_until_expiry' => $vaccination->days_until_expiry,
                'days_until_due' => $vaccination->days_until_due,
                'clinic' => $vaccination->clinicRegistration ? [
                    'id' => $vaccination->clinicRegistration->id,
                    'clinic_name' => $vaccination->clinicRegistration->clinic_name,
                    'phone_number' => $vaccination->clinicRegistration->phone,
                ] : null,
            ];
        })->sortByDesc('administered_date')->values();

        // Get health conditions with clinic information
        $healthConditions = $pet->healthConditions->map(function ($condition) {
            return [
                'id' => $condition->id,
                'condition_name' => $condition->condition_name,
                'condition_type' => $condition->condition_type,
                'condition_type_display' => $condition->condition_type_display,
                'severity' => $condition->severity,
                'severity_display' => $condition->severity_display,
                'diagnosis_date' => $condition->diagnosis_date?->format('Y-m-d'),
                'treatment_plan' => $condition->treatment_plan,
                'medications' => $condition->medications,
                'follow_up_date' => $condition->follow_up_date?->format('Y-m-d'),
                'veterinarian_name' => $condition->veterinarian_name,
                'clinic_name' => $condition->clinic_name,
                'is_chronic' => $condition->is_chronic,
                'is_active' => $condition->is_active,
                'resolved_date' => $condition->resolved_date?->format('Y-m-d'),
                'status' => $condition->status,
                'days_since_diagnosis' => $condition->days_since_diagnosis,
                'days_until_follow_up' => $condition->days_until_follow_up,
                'clinic' => $condition->clinicRegistration ? [
                    'id' => $condition->clinicRegistration->id,
                    'clinic_name' => $condition->clinicRegistration->clinic_name,
                    'phone_number' => $condition->clinicRegistration->phone,
                ] : null,
            ];
        })->sortByDesc('diagnosis_date')->values();

        return Inertia::render('pets/petDetailed', [
            'pet' => $petData,
            'medical_records' => $medicalRecords,
            'vaccinations' => $vaccinations,
            'health_conditions' => $healthConditions,
        ]);
    }

    /**
     * Show the form for editing the specified pet.
     */
    public function edit(Pet $pet)
    {
        // Ensure the user owns this pet
        if ($pet->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized access to pet information.');
        }

        $pet->load(['breed', 'type']);

        $breeds = PetBreed::orderBy('name')->get(['id', 'name', 'species', 'characteristics']);
        $types = PetType::orderBy('name')->get(['id', 'name', 'species', 'description']);

        $petData = [
            'id' => $pet->id,
            'name' => $pet->name,
            'species' => $pet->species,
            'breed_id' => $pet->breed_id,
            'type_id' => $pet->type_id,
            'gender' => $pet->gender,
            'birth_date' => $pet->birth_date?->format('Y-m-d'),
            'weight' => $pet->weight,
            'size' => $pet->size,
            'color' => $pet->color,
            'markings' => $pet->markings,
            'microchip_number' => $pet->microchip_number,
            'is_neutered' => $pet->is_neutered,
            'special_needs' => $pet->special_needs,
            'notes' => $pet->notes,
            'profile_image' => $pet->profile_image,
            'images' => $pet->images,
        ];

        return Inertia::render('pets/editPet', [
            'pet' => $petData,
            'breeds' => $breeds,
            'types' => $types,
            'species_options' => [
                'dog' => 'Dog',
                'cat' => 'Cat',
                'bird' => 'Bird',
                'rabbit' => 'Rabbit',
                'hamster' => 'Hamster',
                'guinea_pig' => 'Guinea Pig',
                'reptile' => 'Reptile',
                'fish' => 'Fish',
                'other' => 'Other',
            ],
            'gender_options' => [
                'male' => 'Male',
                'female' => 'Female',
                'unknown' => 'Unknown',
            ],
            'size_options' => [
                'tiny' => 'Tiny',
                'small' => 'Small',
                'medium' => 'Medium',
                'large' => 'Large',
                'giant' => 'Giant',
            ],
        ]);
    }

    /**
     * Update the specified pet in storage.
     */
    public function update(Request $request, Pet $pet)
    {
        // Ensure the user owns this pet
        if ($pet->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized access to pet information.');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'species' => 'sometimes|required|string|max:50',
            'breed_id' => 'nullable|exists:pet_breeds,id',
            'type_id' => 'nullable|exists:pet_types,id',
            'gender' => 'sometimes|required|in:male,female,unknown',
            'birth_date' => 'nullable|date|before_or_equal:today',
            'weight' => 'nullable|numeric|min:0|max:500',
            'size' => 'nullable|in:tiny,small,medium,large,giant',
            'color' => 'nullable|string|max:100',
            'markings' => 'nullable|string|max:500',
            'microchip_number' => 'nullable|string|max:50|unique:pets,microchip_number,' . $pet->id,
            'is_neutered' => 'boolean',
            'special_needs' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_profile_image' => 'boolean',
            'remove_images' => 'nullable|array',
        ], [
            'profile_image.max' => 'The profile image must not be larger than 10MB.',
            'profile_image.image' => 'The file must be an image.',
            'profile_image.mimes' => 'The profile image must be a file of type: jpeg, png, jpg, gif, webp.',
        ]);

        // Handle profile image upload (single image only)
        if ($request->hasFile('profile_image')) {
            \Log::info('Profile image upload detected', [
                'pet_id' => $pet->id,
                'file_name' => $request->file('profile_image')->getClientOriginalName(),
                'file_size' => $request->file('profile_image')->getSize(),
            ]);
            
            // Delete old profile image if exists
            if ($pet->profile_image) {
                Storage::disk('public')->delete($pet->profile_image);
            }
            $path = $request->file('profile_image')
                ->store('pets/profile-images', 'public');
            $validated['profile_image'] = $path;
            
            \Log::info('Profile image saved', ['path' => $path]);
        } else {
            \Log::info('No profile_image file in request', [
                'has_files' => $request->hasFile('profile_image'),
                'all_files' => array_keys($request->allFiles()),
                'all_input' => array_keys($request->all()),
            ]);
        }

        // Handle additional images
        if ($request->hasFile('images')) {
            $newImages = [];
            foreach ($request->file('images') as $image) {
                $newImages[] = $image->store('pets/images', 'public');
            }
            
            // Merge with existing images (if not removing all)
            $existingImages = $pet->images ?? [];
            
            // Remove selected images
            if ($request->filled('remove_images')) {
                $removeImages = $request->remove_images;
                foreach ($removeImages as $imageToRemove) {
                    Storage::disk('public')->delete($imageToRemove);
                    $existingImages = array_filter($existingImages, fn($img) => $img !== $imageToRemove);
                }
            }
            
            $validated['images'] = array_merge(array_values($existingImages), $newImages);
        } elseif ($request->filled('remove_images')) {
            // Only removing images, not adding new ones
            $removeImages = $request->remove_images;
            $existingImages = $pet->images ?? [];
            
            foreach ($removeImages as $imageToRemove) {
                Storage::disk('public')->delete($imageToRemove);
                $existingImages = array_filter($existingImages, fn($img) => $img !== $imageToRemove);
            }
            
            $validated['images'] = array_values($existingImages);
        }

        $pet->update($validated);

        // Handle Inertia requests (like image upload from modal)
        if ($request->wantsJson() || $request->header('X-Inertia')) {
            return back()->with('success', "Pet '{$pet->name}' has been updated successfully!");
        }

        return redirect()->route('petsShow', $pet)
            ->with('success', "Pet '{$pet->name}' has been updated successfully!");
    }

    /**
     * Remove the specified pet from storage.
     */
    public function destroy(Pet $pet)
    {
        // Ensure the user owns this pet
        if ($pet->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized access to pet information.');
        }

        // Soft delete by setting is_active to false
        $pet->update(['is_active' => false]);

        return redirect()->route('petsIndex')
            ->with('success', "Pet '{$pet->name}' has been removed from your active pets.");
    }

    /**
     * Get breeds for a specific species.
     */
    public function getBreedsBySpecies(Request $request)
    {
        $species = $request->get('species');
        
        $breeds = PetBreed::ofSpecies($species)
            ->orderBy('name')
            ->get(['id', 'name', 'species', 'characteristics']);

        return response()->json($breeds);
    }
}