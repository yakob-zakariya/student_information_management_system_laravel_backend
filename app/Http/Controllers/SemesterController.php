<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use App\Http\Resources\SemesterResource;
use App\Rules\CompositeUnique;
use App\Traits\ApiResponse;

class SemesterController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(AcademicYear $academicYear)
    {
        return $this->successResponse(
            SemesterResource::collection($academicYear->semesters),
            'Semesters fetched successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, AcademicYear $academicYear)

    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                new CompositeUnique('semesters', ['name' => $request->name, 'academic_year_id' => $academicYear->id])
            ],
            'registration_open' => [
                'sometimes',
                'boolean'
            ]
        ]);

        $semester = $academicYear->semesters()->create($validated);

        return $this->successResponse(
            new SemesterResource($semester),
            'Semester created successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(AcademicYear $academicYear, Semester $semester)
    {
        if ($academicYear->id !== $semester->academic_year_id) {
            abort(404);
        }

        return $this->successResponse(
            new SemesterResource($semester),
            'Semester fetched successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AcademicYear $academicYear, Semester $semester)
    {
        if ($academicYear->id !== $semester->academic_year_id) {
            abort(404);
        }
        $validated = $request->validate([
            'name' => [
                'sometimes',
                new CompositeUnique('semesters', ['name' => $request->name, 'academic_year_id' => $academicYear->id], $semester->id)
            ],
            'registration_open' => [
                'sometimes',
                'boolean'
            ]
        ]);


        $semester->update($validated);

        return $this->successResponse(
            new SemesterResource($semester),
            'Semester updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcademicYear $academicYear, Semester $semester)
    {
        if ($academicYear->id !== $semester->academic_year_id) {
            abort(404);
        }
        $semester->delete();

        return response()->noContent();
    }
}
