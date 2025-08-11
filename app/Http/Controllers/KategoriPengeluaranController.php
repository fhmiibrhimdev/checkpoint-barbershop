<?php

namespace App\Http\Controllers;

use App\Models\KategoriPengeluaran;
use Illuminate\Http\Request;

class KategoriPengeluaranController extends Controller
{

    public function index()
    {
        $data = KategoriPengeluaran::get();

        return response()->json([
            'success'   => true,
            'data'      => $data,
        ]);
    }

    public function store(Request $request)
    {
        $data = KategoriPengeluaran::create($request->all());
        if ($data) {
            $result = response()->json([
                'success'   => true,
                'data'      => $data,
                'msg'       => 'Data inserted successfully'
            ]);
        } else {
            $result = response()->json([
                'success'   => false,
                'msg'       => 'Cannot inserted data'
            ], 500);
        }

        return $result;
    }

    public function edit($id)
    {
        $data = KategoriPengeluaran::where('id', $id)->first();

        return response()->json([
            'success'   => true,
            'data'      => $data,
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = KategoriPengeluaran::where('id', $id)->update($request->all());

        if ($data) {
            $result = response()->json([
                'success'   => true,
                'msg'       => 'Data updated successfully'
            ]);
        } else {
            $result = response()->json([
                'success'   => false,
                'msg'       => 'Cannot updated data'
            ], 500);
        }

        return $result;
    }

    public function destroy($id)
    {
        $data = KategoriPengeluaran::where('id', $id)->delete();

        if ($data) {
            $result = response()->json([
                'success'   => true,
                'msg'       => 'Data deleted successfully'
            ]);
        } else {
            $result = response()->json([
                'success'   => false,
                'msg'       => 'Cannot deleted data'
            ], 500);
        }

        return $result;
    }
}
