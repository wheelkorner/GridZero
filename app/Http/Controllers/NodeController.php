<?php

namespace App\Http\Controllers;

use App\Models\Node;
use Illuminate\Http\JsonResponse;

class NodeController extends Controller
{
    /**
     * Display a listing of the nodes.
     */
    public function index(): JsonResponse
    {
        $nodes = Node::all();
        return response()->json($nodes);
    }
}
