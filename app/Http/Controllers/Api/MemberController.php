<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MemberResource;
use App\Models\Member;

class MemberController extends Controller
{
    public function index()
    {
        return MemberResource::collection(Member::paginate(10));
    }

    public function show(string $id)
    {
        return new MemberResource(Member::findOrFail($id));
    }
}
