<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class ConnectionController
{
    public function create(Request $request, ?Connection $connection = null)
    {
        $connection ??= new Connection();

        return view('connections.create', compact('connection'));
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'name'  => 'unique:connections,name',
                'uri'   => ['required', 'start_with:mongodb://,mongodb+srv://'],
                'color' => ['required'],
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
                'name'  => 'unique:connections,name,'.$connection->id,
                'uri'   => ['required', 'url'],
                'color' => ['required']
            ]
        );
        $connection->update($request->only(['name', 'uri', 'color']));
        return redirect()->route('home');
    }

    public function destroy(Connection $connection)
    {
        $connection->delete();
        return redirect()->route('home');
    }

    public function getFavicon(Connection $connection)
    {
        // create empty canvas with background color
        $img = Image::canvas(20, 20);

        $img->circle(18, 10, 10, function ($draw) use ($connection) {
            $draw->background($connection->color);
        });

        return $img->response('png');
    }
}
