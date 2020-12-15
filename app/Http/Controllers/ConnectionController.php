<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use Illuminate\Http\Request;

class ConnectionController
{
    public function create(Request $request)
    {
        return view('connections.create');
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'unique:connections,name',
                'uri'  => ['required', 'url'],
                'color'  => ['required'],
            ]
        );
        Connection::query()->create($request->only(['name', 'uri', 'color']));
        return redirect()->route('home');
    }

    public function show(Request $request, Connection $connection)
    {
        return view('connections.show', compact('connection'));
    }

    public function edit(Request $request, Connection $connection)
    {
        return view('connections.edit', compact('connection'));
    }

    public function update(Request $request, Connection $connection)
    {
        $request->validate(
            [
                'name' => 'unique:connections,name,'.$connection->id,
                'uri'  => ['required', 'url'],
                'color' => ['required']
            ]
        );
        $connection->update($request->only(['name', 'uri', 'color']));
        return redirect()->route('home');
    }
}
