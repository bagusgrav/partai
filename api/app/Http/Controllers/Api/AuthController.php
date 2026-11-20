<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\MemberVerificationMail;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'request_id' => ['required', 'uuid'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => 'Jika alamat email terdaftar, tautan untuk mengatur ulang kata sandi akan dikirim.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'request_id' => ['required', 'uuid'],
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        unset($validated['request_id']);

        $status = Password::reset($validated, static function (User $user, string $password): void {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
        });

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Tautan reset kata sandi tidak valid atau sudah kedaluwarsa.',
            ], 422);
        }

        return response()->json([
            'message' => 'Kata sandi berhasil diubah.',
        ]);
    }

    public function guest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'request_id' => ['required', 'uuid'],
        ]);

        $user = User::query()->firstOrCreate(
            ['guest_request_id' => $validated['request_id']],
            [
                'name' => 'Tamu',
                'email' => 'guest_'.Str::uuid().'@partai.id',
                'password' => Str::random(64),
                'role' => 'guest',
                'is_verified' => false,
            ],
        );

        return response()->json([
            'token' => $user->createToken('guest-session')->plainTextToken,
        ], 201);
    }

    public function register(Request $request): JsonResponse
    {
        $existing = User::query()
            ->where('registration_request_id', $request->input('request_id'))
            ->first();

        $validated = $request->validate([
            'request_id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($existing?->id),
            ],
            'password' => ['required', 'string', 'min:8'],
            'cluster_id' => ['required', 'string', 'max:255'],
            'nik' => [
                'nullable',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) use ($existing): void {
                    if (! is_string($value) || $value === '') {
                        return;
                    }

                    $query = DB::table('users')->where('nik_hash', hash('sha256', $value));
                    if ($existing !== null) {
                        $query->where('id', '!=', $existing->id);
                    }

                    if ($query->exists()) {
                        $fail('NIK sudah terdaftar.');
                    }
                },
            ],
        ], [
            'email.unique' => 'Email sudah terdaftar.',
        ]);

        if ($existing !== null) {
            return $this->duplicateRegistrationResponse($existing, $validated);
        }

        try {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'role' => 'member',
                'nik' => $validated['nik'] ?? null,
                'cluster_id' => $validated['cluster_id'],
                'is_verified' => false,
                'registration_request_id' => $validated['request_id'],
            ]);
        } catch (QueryException $exception) {
            $existing = User::query()
                ->where('registration_request_id', $validated['request_id'])
                ->first();

            if ($existing === null) {
                throw $exception;
            }

            return $this->duplicateRegistrationResponse($existing, $validated);
        }

        $this->sendVerificationEmail($user);

        return response()->json([
            'token' => $user->createToken('member-session')->plainTextToken,
        ], 201);
    }

    public function verifyEmail(Request $request, string $id, string $hash): JsonResponse
    {
        $user = User::query()->findOrFail($id);

        abort_unless(hash_equals(sha1($user->email), $hash), 403);

        if (! $user->is_verified || $user->email_verified_at === null) {
            $user->forceFill([
                'email_verified_at' => now(),
                'is_verified' => true,
            ])->save();
        }

        return response()->json([
            'message' => 'Alamat email berhasil diverifikasi.',
        ]);
    }

    private function sendVerificationEmail(User $user): void
    {
        if ($user->email_verified_at === null) {
            Mail::to($user->email)->send(new MemberVerificationMail($user));
        }
    }

    /**
     * @param  array{name: string, email: string, password: string, cluster_id: string, nik?: ?string, request_id: string}  $validated
     */
    private function duplicateRegistrationResponse(User $user, array $validated): JsonResponse
    {
        if (
            $user->name !== $validated['name']
            || $user->email !== $validated['email']
            || $user->cluster_id !== $validated['cluster_id']
            || $user->nik !== ($validated['nik'] ?? null)
            || ! Hash::check($validated['password'], $user->password)
        ) {
            return response()->json([
                'message' => 'The request_id has already been used for a different registration.',
            ], 409);
        }

        $this->sendVerificationEmail($user);

        return response()->json([
            'token' => $user->createToken('member-session')->plainTextToken,
        ]);
    }
}
