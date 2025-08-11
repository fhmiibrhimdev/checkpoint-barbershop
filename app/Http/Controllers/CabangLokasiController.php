<?php

namespace App\Http\Controllers;

use App\Models\CabangLokasi;
use Illuminate\Http\Request;

class CabangLokasiController extends Controller
{
    public function index()
    {
        $data = CabangLokasi::get();

        return response()->json([
            'success'   => true,
            'data'      => $data,
        ]);
    }

    public function store(Request $request)
    {
        $data = CabangLokasi::create($request->all());
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
        $data = CabangLokasi::where('id', $id)->first();

        return response()->json([
            'success'   => true,
            'data'      => $data,
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = CabangLokasi::where('id', $id)->update($request->all());

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
        $data = CabangLokasi::where('id', $id)->delete();

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
