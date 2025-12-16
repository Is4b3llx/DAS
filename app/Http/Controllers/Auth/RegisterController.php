<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Rol;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/login';

    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'nombre'             => ['required','string','max:255'],
            'apellido'           => ['required','string','max:255'],
            'telefono'           => ['required','integer'],
            'ci'                 => ['required','string'],
            'correo_electronico' => ['required','string','email','max:255','unique:users,correo_electronico'],
            'password'           => ['required','string','min:8','confirmed'],

            'id_rol' => [
                'required',
                'integer',
                Rule::exists('rol', 'id_rol')->where(function ($q) {
                    $q->where('titulo_rol', '!=', 'Administrador');
                }),
            ],
        ]);
    }

    public function showRegistrationForm()
    {
        $roles = Rol::where('titulo_rol', '!=', 'Administrador')
            ->orderBy('titulo_rol')
            ->get();

        return view('auth.register', compact('roles'));
    }

    /**
     * Create a new user instance after a valid registration.
     */
    protected function create(array $data)
    {
        return User::create([
            'nombre'             => $data['nombre'],
            'apellido'           => $data['apellido'],
            'correo_electronico' => $data['correo_electronico'],
            'email'              => $data['correo_electronico'],
            'telefono'           => $data['telefono'],
            'ci'                 => $data['ci'],
            'password'           => Hash::make($data['password']),
            'id_rol'             => $data['id_rol'] ?? null,
            'activo'             => false,
            'administrador'      => false,
        ]);
    }
}
