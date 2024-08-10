<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function index()
    {
        $users = User::all();
        return response()->json($users, 200);
    }

    public function store(Request $request)
    {

        $request->validate([
            "name" => "required|string",
            "email" => "string|required|unique:users",
            "type" => "required|string",
            "cpf" => "string|required|unique:users",
            "password" => "string|required"
        ]);

        
        $cpf = $this->validateCpf($request->cpf);
        
        if(!$cpf) return response()->json(["message" => "Falha ao criar usuario. Cpf inválido"], 404);
        
        $newUser = new User($request->all());
        $newUser->password = bcrypt($newUser->password);

        if(!$newUser->save()){
            return response()->json(["message" => "Falha ao criar usuario"], 404);
        }

        return response()->json(["message" => "Usuario criado"], 201);

    }

    private function validateCpf($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if(strlen($cpf) !== 11) return false;

        if(preg_match('/(\d)\1{10}/', $cpf)) return false;

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($cpf[$c] != $d) return false;
        }

        return true;
    }
    
}
