<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Players;
use Illuminate\Http\Request;

class PlayersController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function index()
  {
    $players = Players::all();
    return response()->json(['status' => 200, 'players' => $players], 200);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required',
      'jersey_number' => 'required|numeric',
    ]);

    $player = Players::create($request->all());

    return response()->json(['status' => 200, 'player' => $player], 200);
  }

  /**
   * Display the specified resource.
   *
   * @param  \App\Models\Players  $player
   * @return \Illuminate\Http\JsonResponse
   */
  public function show(Players $player)
  {
    return response()->json(['player' => $player]);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Models\Players  $player
   * @return \Illuminate\Http\JsonResponse
   */
  public function update(Request $request, Players $player)
  {
    $request->validate([
      'name' => 'required',
      'jersey_number' => 'required|numeric',
    ]);

    $player->update($request->all());

    return response()->json(['status' => 200, 'player' => $player], 200);
  }
}
