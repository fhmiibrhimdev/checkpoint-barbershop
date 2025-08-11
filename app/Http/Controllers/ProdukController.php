<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{

    public function index()
    {
        $data = Produk::get();

        return response()->json([
            'success'   => true,
            'data'      => $data,
        ]);
    }

    public function store(Request $request)
    {
        $data = Produk::create($request->all());
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
        $data = Produk::where('id', $id)->first();

        return response()->json([
            'success'   => true,
            'data'      => $data,
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = Produk::where('id', $id)->update($request->all());

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
        $data = Produk::where('id', $id)->delete();

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
