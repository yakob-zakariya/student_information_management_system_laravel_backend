<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\AcademicYearResource;
use App\Models\AcademicYear;
use App\Traits\ApiResponse;


class AcademicYearController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $academicYears = AcademicYear::with('semesters')->get();
        return $this->successResponse(
            AcademicYearResource::collection($academicYears),
            'Academic years retrieved successfully.'
        );
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:academic_years,name'],
        ]);

        $academicYear = AcademicYear::create($validated);

        return $this->successResponse(
            new AcademicYearResource($academicYear),
            'Academic year created successfully.',
            201
        );
        // return new AcademicYearResource($academicYear);
    }


    public function show(AcademicYear $academicYear)
    {
        $academicYear->load('semesters');
        // return new AcademicYearResource($academicYear);
        return $this->successResponse(
            new AcademicYearResource($academicYear),
            'Academic year retrieved successfully.'
        );
    }



    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', 'unique:academic_years,name,' . $academicYear->id],
        ]);
        $academicYear->update($validated);
        $academicYear->load('semesters');

        return $this->successResponse(
            new AcademicYearResource($academicYear),
            'Academic year updated successfully.'
        );
    }

    /**
     * Delete an academic year
     *
     * @OA\Delete(
     * path="/api/academic-years/{id}",
     * summary="Delete an academic year",
     * tags={"Academic Year"},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="ID of the academic year to delete",
     * @OA\Schema(type="integer", example=1)
     * ),
     * @OA\Response(
     * response=204,
     * description="Academic year deleted"
     * )
     * )
     */
    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();
        return response()->json([
            "message" => "Academic Year Deleted Successfully"
        ], 204);
    }
}
