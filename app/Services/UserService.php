<?php

namespace App\Services;

use App\Http\Requests\User\StoreUserRequest;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserService extends BaseService
{
    protected $user;
    protected $notificationService;

    public function __construct(User $user, NotificationService $notificationService)
    {
        $this->user = $user;
        $this->notificationService = $notificationService;
    }

    /**
     * Store user
     * 
     * @return User $user
     */
    public function store(StoreUserRequest $request)
    {
        $user = null;
        DB::transaction(function () use (&$user, $request) {
            $request->merge([
                'roleId' => Role::User
            ]);

            $user = $this->user->create($request->all());
        });

        return $user;
    }

    /**
     * Validate code to change user password
     * @param ChangePasswordRequest $request
     * 
     * @return boolean||string
     */
    public function changePassword($request)
    {
        $user = $this->getByEmail($request->email);

        if ($this->validateCode($request, $user)) {
            $user->confirmation->update(['confirmed' => true]);
            $user->update(['password' => $request->password]);

            return (object) [
                'token' => JWTAuth::fromUser($user)
            ];
        }

        return false;
    }

    /**
     * Create code to change user password
     * 
     * @param ForgotPasswordRequest $request
     */
    public function forgotPassword($request)
    {
        $user = $this->getByEmail($request->email);
        $this->createPasswordCodeRecovery($user);
        $this->notificationService->sendForgotEmail($user);
        return (object) [
            'message' => 'Email enviado com sucesso!'
        ];
    }

    /**
     * Valide user code
     * @param ValidateCodeRequest $request
     * @param User $user
     * 
     * @return boolean
     */
    public function validateCode($request, User $user)
    {
        if ($request->code != $user->confirmation->code) {
            abort(response()->json(['error' => 'Código inválido', 'message' => 'Verifique se o código está correto'], 400));
        }

        if (Carbon::now()->diffInMinutes(Carbon::parse($user->confirmation->updated_at)) > 15) {
            abort(response()->json(['error' => 'Código expirado', 'message' => 'Seu código expirou, gere outro para trocar a senha'], 400));
        }

        if ($user->confirmation->confirmed) {
            abort(response()->json(['error' => 'Código já utilizado', 'message' => 'Seu código já foi utilizado, gere outro para trocar a senha'], 400));
        }

        return (object) ['status' => true];
    }

    /**
     * Return user by email
     * @param integer $id user Id
     * @param Array $eagerLoading relations to eager load
     * 
     * @return User $user
     */
    public function getByEmail($email, $eagerLoading = null)
    {
        $user = $this->getBy('email', $email, $eagerLoading);
        return $user;
    }

    /**
     * Create code to user recovery password
     * @param User $user
     * 
     * @return void
     */
    private function createPasswordCodeRecovery(User $user)
    {
        $code = $this->generateCode();

        if ($user->confirmation()->first()) {
            $user->confirmation()->first()->update(['code' => $code, 'confirmed' => false]);
        } else {
            $user->confirmation()->create(['code' => $code]);
        }
    }

    /**
     * Create random code
     * 
     * @return string
     */
    private function generateCode()
    {
        return str_pad(rand(0, 9999), 4, 0, STR_PAD_LEFT);
    }

    /**
     * Return a user based on a type of get
     * @param string $type tipo do get
     * @param $param valor do get
     * @param Array $eagerLoading relations to eager load
     * 
     * @return User $user
     */
    private function getBy($type, $param, $eagerLoading = null)
    {
        $query = $this->user;

        if (isset($eagerLoading)) {
            $query = $query->with($eagerLoading);
        }

        if ($type == 'id') {
            $user = $query->findOrFail($param);
        } else {
            $user = $query->where($type, $param);
            $user = $user->first();
        }

        if (!$user) {
            abort(404, "Usuário não encontrado.");
        }

        return $user;
    }
}
