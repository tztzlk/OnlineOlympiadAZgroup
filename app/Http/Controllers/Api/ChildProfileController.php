<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChildProfile;
use App\Support\NotificationWorkflow;
use App\Support\OnboardingProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ChildProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return response()->json(
            $user->childProfiles()
                ->withCount(['olympiadRequests', 'quizResults', 'trainingAttempts'])
                ->get()
                ->map(fn (ChildProfile $child) => $this->mapChild($child))
        );
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date|before:today',
            'grade' => 'nullable|integer|in:3,4,5,6,7,8,9,10,11',
            'school' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'language_preference' => 'nullable|string|max:10',
        ]);

        $child = $user->childProfiles()->create([
            ...$data,
            'school' => $data['school'] ?? $user->school,
            'city' => $data['city'] ?? $user->city,
            'language_preference' => $data['language_preference'] ?? 'ru',
        ]);

        Cache::forget("profile:me:{$user->id}");
        OnboardingProgress::syncStep($user, 'child_profile');

        NotificationWorkflow::createForUser(
            user: $user,
            type: 'child_added',
            title: 'Профиль ребёнка добавлен',
            body: "Профиль участника {$child->full_name} сохранён. Теперь можно перейти к выбору олимпиады.",
            actionUrl: rtrim(config('app.url'), '/') . '/subject',
            statusKey: 'child_profile',
            payload: [
                'action_label' => 'Выбрать олимпиаду',
                'context' => [
                    'Участник' => $child->full_name,
                    'Класс' => $child->grade ?: 'Не указан',
                ],
            ],
            sendEmail: true
        );

        return response()->json([
            'message' => 'Профиль ребёнка создан.',
            'child' => $this->mapChild($child),
        ], 201);
    }

    public function show(Request $request, ChildProfile $childProfile)
    {
        $this->authorizeChild($request, $childProfile);

        $childProfile->loadCount(['olympiadRequests', 'quizResults', 'trainingAttempts']);

        return response()->json($this->mapChild($childProfile));
    }

    public function update(Request $request, ChildProfile $childProfile)
    {
        $this->authorizeChild($request, $childProfile);

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'grade' => 'nullable|integer|in:3,4,5,6,7,8,9,10,11',
            'school' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'language_preference' => 'nullable|string|max:10',
        ]);

        $childProfile->update($data);
        $childProfile->loadCount(['olympiadRequests', 'quizResults', 'trainingAttempts']);
        Cache::forget("profile:me:{$request->user()->id}");

        return response()->json([
            'message' => 'Профиль ребёнка обновлён.',
            'child' => $this->mapChild($childProfile),
        ]);
    }

    public function destroy(Request $request, ChildProfile $childProfile)
    {
        $this->authorizeChild($request, $childProfile);
        $childProfile->delete();
        Cache::forget("profile:me:{$request->user()->id}");

        return response()->json([
            'message' => 'Профиль ребёнка удалён.',
        ]);
    }

    protected function authorizeChild(Request $request, ChildProfile $childProfile): void
    {
        if ($childProfile->parent_id !== $request->user()->id) {
            abort(403);
        }
    }

    protected function mapChild(ChildProfile $child): array
    {
        return [
            'id' => $child->public_id,
            'first_name' => $child->first_name,
            'last_name' => $child->last_name,
            'full_name' => $child->full_name,
            'birth_date' => optional($child->birth_date)->toDateString(),
            'grade' => $child->grade,
            'school' => $child->school,
            'city' => $child->city,
            'language_preference' => $child->language_preference,
            'olympiads_count' => $child->olympiad_requests_count ?? 0,
            'results_count' => $child->quiz_results_count ?? 0,
            'training_attempts_count' => $child->training_attempts_count ?? 0,
            'created_at' => optional($child->created_at)->toISOString(),
        ];
    }
}
