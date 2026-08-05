<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success($data = null, string $message = 'Berhasil', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function error(string $message = 'Terjadi kesalahan', int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    protected function datatableResponse($query, $request, \Closure $mapper = null): JsonResponse
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        
        // $search is already handled in the controller query before calling this method
        
        $totalRecords = clone $query;
        $totalRecords = $totalRecords->count();

        $filteredRecords = $query->count();

        if ($length > 0) {
            $data = $query->skip($start)->take($length)->get();
        } else {
            $data = $query->get();
        }

        if ($mapper) {
            $data->transform($mapper);
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }
}
