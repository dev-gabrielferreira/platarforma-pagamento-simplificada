<?php

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get("/transfers", [TransactionController::class, "index"])->name("transfers.index");
Route::post("transfer", [TransactionController::class, "store"])->name("transfers.store");

Route::get("/users", [UserController::class, "index"])->name("users.index");
Route::post("/user/create", [UserController::class, "store"])->name("users.store");
