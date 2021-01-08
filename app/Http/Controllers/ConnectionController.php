<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class ConnectionController
{
    public function index()
    {
        $connections = Connection::query()
            ->orderBy('order')
            ->get();
        return view('home', compact('connections'));
    }

    public function create(?Connection $connection = null)
    {
        $connection ??= new Connection();

        return view('connections.create', compact('connection'));
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'name'  => 'unique:connections,name',
                'uri'   => ['required', 'starts_with:mongodb://,mongodb+srv://'],
                'color' => ['required'],
            ]
        );

        $request->merge([
            'order' => Connection::query()->max('order') + 1
        ]);

        Connection::query()->create($request->only(['name', 'uri', 'color', 'order']));
        return redirect()->route('home');
    }

    public function show(Connection $connection)
    {
        return view('connections.show', compact('connection'));
    }

    public function edit(Connection $connection)
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
        DB::transaction(function () use ($connection) {
            $connection->delete();

            $index = 1;

            $connections = Connection::query()
                ->orderBy('order')
                ->get();

            foreach ($connections as $connection) {
                $connection->update([
                    'order' => $index++
                ]);
            }
        });

        return redirect()->route('home');
    }

    public function orderUp(Connection $connection)
    {
        $prevConnection = Connection::query()
            ->where('order', '<', $connection->order)
            ->orderBy('order', 'desc')
            ->first();

        if (empty($prevConnection)) {
            return redirect()->route('home');
        }

        $prevOrder = $prevConnection->order;

        $prevConnection->update([
            'order' => $connection->order
        ]);

        $connection->update([
            'order' => $prevOrder
        ]);

        return redirect()->route('home');
    }

    public function orderDown(Connection $connection)
    {
        $nextConnection = Connection::query()
            ->where('order', '>', $connection->order)
            ->orderBy('order')
            ->first();

        if (empty($nextConnection)) {
            return redirect()->route('home');
        }

        $nextOrder = $nextConnection->order;

        $nextConnection->update([
            'order' => $connection->order
        ]);

        $connection->update([
            'order' => $nextOrder
        ]);

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
