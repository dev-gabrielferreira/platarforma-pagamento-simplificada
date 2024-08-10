<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::all();
        return response()->json($transactions, 200);
    }

    public function store(Request $request)
    {   
        $request->validate([
            "value" => "numeric|required",
            "payer" => "required",
            "payee" => "required"
        ]);

        $payer = User::find($request->payer);
        $payee = User::find($request->payee);

        if(!$payer || !$payee) return response()->json(["message" => "Payer ou payee invalido"], 404);

        if($payer->type == "lojista") return response()->json(["message" => "Lojista não realizam transações"], 404);

        if($payer->balance < $request->value) return response()->json(["message" => "Saldo insuficiente"], 404);

        $payer->balance = $payer->balance - $request->value;
        $payee->balance = $payee->balance + $request->value;

        $url = "https://util.devi.tools/api/v2/authorize";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: MyApp/1.0',
        ]);
        
        $response = curl_exec($ch);
        $data = json_decode($response, true);
        
        if($data['status'] == 'fail') return response()->json(["message" => "Transação não autorizada"], 404);

        $payer->save();
        $payee->save();

        $notifyPayee = rand(0, 5);

        if($notifyPayee == 0){
            $payer->balance = $payer->balance + $request->value;
            $payee->balance = $payee->balance - $request->value;

            $payer->save();
            $payee->save();

            $newPayerBalance = User::find($request->payer)->balance;
            $newPayeeBalance = User::find($request->payee)->balance; 

            return response()->json([
                "message" => "Falha ao realizar transferência",
                "newPayerBalance" => $newPayerBalance,
                "newPayeeBalance" => $newPayeeBalance,
            ], 404);
        }

        $newPayerBalance = User::find($request->payer)->balance;
        $newPayeeBalance = User::find($request->payee)->balance; 

        return response()->json([
            "message" => "Transferência realizada",
            "newPayerBalance" => $newPayerBalance,
            "newPayeeBalance" => $newPayeeBalance,
        ]);

    }
}
