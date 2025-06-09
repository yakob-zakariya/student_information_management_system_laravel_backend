<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Section;
use Illuminate\Http\Request;
use App\Models\Batch;
use App\Http\Resources\SectionResource;
use App\Rules\CompositeUnique;

use App\Traits\ApiResponse;


class SectionController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Department $department, Batch $batch)
    {
        if ($batch->department_id !== $department->id) {
            abort(404);
        }

        return $this->successResponse(
            SectionResource::collection($batch->sections),
            'Sections Fetched Successfully'
        );

        return  SectionResource::collection($batch->sections);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Department $department, Batch $batch)
    {
        if ($batch->department_id !== $department->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                new CompositeUnique('sections', ['name' => $request->name, 'batch_id' => $batch->id])
            ],

        ]);

        $section = $batch->sections()->create($validated);

        return $this->successResponse(
            new SectionResource($section),
            'Section Created Successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department, Batch $batch, Section $section)
    {
        if ($section->batch_id !== $batch->id) {
            abort(404);
        }

        if ($batch->department_id !== $department->id) {
            abort(404);
        }

        return $this->successResponse(
            new SectionResource($section),
            'Section Fetched Successfully'

        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department, Batch $batch, Section $section)
    {
        if ($section->batch_id !== $batch->id) {
            abort(404);
        }
        if ($batch->department_id !== $department->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => [
                'sometimes',
                new  CompositeUnique('sections', ['name' => $request->name, 'batch_id' => $batch->id], $section->id)
            ]
        ]);


        $section->update($validated);

        return $this->successResponse(
            new SectionResource($section),
            'Section Updated Successfully'
        );
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department, Batch $batch, Section $section)
    {
        if ($section->batch_id !== $batch->id) {
            abort(404);
        }
        if ($batch->department_id !== $department->id) {
            abort(404);
        }

        $section->delete();
        return response()->noContent();
    }
}
