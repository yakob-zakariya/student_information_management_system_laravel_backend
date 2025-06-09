<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Http\Resources\CourseResource;
use App\Http\Resources\CourseCollection;
use App\Traits\ApiResponse;

class CourseController extends Controller
{
    use ApiResponse;
    public function index()
    {
        $courses = Course::paginate(10);
        return CourseResource::collection($courses);
    }

    public function store(Request $request)
    {


        $validated =  $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'unique:courses,code'],
            'credit_hour' => ['required', 'integer'],
        ]);

        $course = Course::create($validated);
        $course->load(['prerequisites', 'prerequisiteFor']);


        return $this->successResponse(
            new CourseResource($course),
            'Course created successfully',
            201
        );
    }

    public function show(Course $course)
    {
        $course->load(['prerequisites', 'prerequisiteFor', 'availableCourses']);

        // Available courses for prerequisites selection
        // This will exclude the current course and its prerequisites
        // $course->available_courses = $course->getAvailableCourses();
        return $this->successResponse(
            new CourseResource($course),
            'Course fetched successfully'
        );
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'unique:courses,code,' . $course->id],
            'credit_hour' => ['sometimes', 'integer'],
        ]);
        $course->update($validated);

        return new CourseResource($course);
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return response()->json([
            'message' => 'Course Deleted Successfully'
        ]);
    }


    public function attachPrerequisites(Request $request, Course $course)
    {
        // dd("attach");
        $validated = $request->validate([
            'prerequisite_ids' => ['required', 'array'],
            'prerequisite_ids.*' => [
                'exists:courses,id',
                function ($attribute, $value, $fail) use ($course) {
                    if ($value == $course->id) {
                        $fail('A course cannot be a prerequisite of itself.');
                    }
                },
            ],
        ]);

        $course->prerequisites()->syncWithoutDetaching($validated['prerequisite_ids']);

        return $this->successResponse(
            new CourseResource($course->load(['prerequisites', 'prerequisiteFor'])),
            'Prerequisites attached successfully'
        );
    }

    public function detachPrerequisites(Request $request, Course $course)
    {
        $validated = $request->validate([
            'prerequisite_ids' => ['required', 'array'],
            'prerequisite_ids.*' => ['exists:courses,id'],
        ]);

        $course->prerequisites()->detach($validated['prerequisite_ids']);

        return $this->successResponse(
            new CourseResource($course->load(['prerequisites', 'prerequisiteFor'])),
            'Prerequisites detached successfully'
        );
    }


    public function syncPrerequisites(Request $request, Course $course)
    {
        $validated = $request->validate([
            'prerequisite_ids' => ['required', 'array'],
            'prerequisite_ids.*' => ['exists:courses,id'],
        ]);

        $course->prerequisites()->sync($validated['prerequisite_ids']);

        return $this->successResponse(
            new CourseResource($course->load('prerequisites')),
            'Prerequisites synced successfully'
        );
    }
}
