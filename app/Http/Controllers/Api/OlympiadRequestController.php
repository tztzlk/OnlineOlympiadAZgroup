<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OlympiadRequestResource;
use App\Models\OlympiadRequest;
use Illuminate\Http\Request;

class OlympiadRequestController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'grade' => 'required|integer|between:3,11',
            'language' => 'required|string|max:10',
            'parent_name' => 'required|string|max:255',
            'parent_phone' => 'required|string|max:20',
            'parent_email' => 'required|email|max:255',
        ]);

        $data['user_id'] = $user->id;
        $data['status'] = 'approved';
        $data['payment_status'] = 'pending';

        $existing = OlympiadRequest::where('user_id', $user->id)
            ->where('subject_id', $data['subject_id'])
            ->latest()
            ->first();

        if ($existing) {
            $existing->update(array_merge($data, [
                'status' => 'approved',
                'payment_status' => $existing->payment_status ?? 'pending',
            ]));

            $existing->load('subject');

            return response()->json([
                'message' => 'Заявка обновлена',
                'request' => new OlympiadRequestResource($existing),
                'redirect_to_quiz' => $existing->payment_status === 'paid',
                'payment_url' => config('services.kaspi.payment_url'),
            ]);
        }

        $requestModel = OlympiadRequest::create($data);
        $requestModel->load('subject');

        return response()->json([
            'message' => 'Регистрация на олимпиаду завершена',
            'request' => new OlympiadRequestResource($requestModel),
            'redirect_to_quiz' => false,
            'payment_url' => config('services.kaspi.payment_url'),
        ]);
    }

    public function status(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['status' => null]);
        }

        $requestModel = OlympiadRequest::where('user_id', $user->id)
            ->latest()
            ->first();

        return response()->json([
            'status' => $requestModel?->status,
            'payment_status' => $requestModel?->payment_status,
            'payment_url' => config('services.kaspi.payment_url'),
        ]);
    }

    public function index()
    {
        $requests = OlympiadRequest::with(['subject', 'user'])
            ->latest()
            ->paginate(50);

        return OlympiadRequestResource::collection($requests);
    }

    public function show(OlympiadRequest $olympiadRequest)
    {
        $olympiadRequest->load(['subject', 'user']);

        return response()->json([
            'id' => $olympiadRequest->id,
            'status' => $olympiadRequest->status,
            'payment_status' => $olympiadRequest->payment_status,
            'paid_at' => optional($olympiadRequest->paid_at)->toISOString(),
            'completed' => $olympiadRequest->completed,
            'first_name' => $olympiadRequest->first_name,
            'last_name' => $olympiadRequest->last_name,
            'birth_date' => optional($olympiadRequest->birth_date)->toDateString(),
            'grade' => $olympiadRequest->grade,
            'language' => $olympiadRequest->language,
            'parent_name' => $olympiadRequest->parent_name,
            'parent_phone' => $olympiadRequest->parent_phone,
            'parent_email' => $olympiadRequest->parent_email,
            'subject' => [
                'id' => $olympiadRequest->subject?->id,
                'name' => $olympiadRequest->subject?->name,
            ],
            'user' => [
                'id' => $olympiadRequest->user?->id,
                'name' => $olympiadRequest->user?->name,
                'email' => $olympiadRequest->user?->email,
            ],
            'created_at' => optional($olympiadRequest->created_at)->toISOString(),
        ]);
    }

    public function stats()
    {
        return response()->json([
            'total' => OlympiadRequest::count(),
            'pending' => OlympiadRequest::where('status', 'pending')->count(),
            'approved' => OlympiadRequest::where('status', 'approved')->count(),
            'rejected' => OlympiadRequest::where('status', 'rejected')->count(),
            'completed' => OlympiadRequest::where('completed', true)->count(),
        ]);
    }

    public function approveReject(Request $request, OlympiadRequest $olympiadRequest)
    {
        return $this->updateStatus($request, $olympiadRequest);
    }

    public function updateStatus(Request $request, OlympiadRequest $olympiadRequest)
    {
        $user = $request->user();

        if (!$user || !$user->is_admin) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $olympiadRequest->status = $request->status;
        $olympiadRequest->save();
        $olympiadRequest->load('subject');

        return response()->json([
            'message' => "Заявка {$request->status}",
            'request' => new OlympiadRequestResource($olympiadRequest),
        ]);
    }

    public function updatePaymentStatus(Request $request, OlympiadRequest $olympiadRequest)
    {
        $user = $request->user();

        if (!$user || !$user->is_admin) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed',
        ]);

        $status = $request->input('payment_status');

        $olympiadRequest->update([
            'payment_status' => $status,
            'paid_at' => $status === 'paid' ? now() : null,
        ]);

        $olympiadRequest->load(['subject', 'user']);

        return response()->json([
            'message' => 'Статус оплаты обновлён',
            'request' => new OlympiadRequestResource($olympiadRequest),
        ]);
    }

    public function destroy(OlympiadRequest $olympiadRequest)
    {
        $olympiadRequest->delete();

        return response()->json([
            'message' => 'Заявка удалена',
        ]);
    }
}
