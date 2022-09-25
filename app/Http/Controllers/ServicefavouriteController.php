<?php

namespace App\Http\Controllers;

use App\Models\servicefavourite;
use App\Http\Requests\StoreservicefavouriteRequest;
use App\Http\Requests\UpdateservicefavouriteRequest;

class ServicefavouriteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreservicefavouriteRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreservicefavouriteRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\servicefavourite  $servicefavourite
     * @return \Illuminate\Http\Response
     */
    public function show(servicefavourite $servicefavourite)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\servicefavourite  $servicefavourite
     * @return \Illuminate\Http\Response
     */
    public function edit(servicefavourite $servicefavourite)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateservicefavouriteRequest  $request
     * @param  \App\Models\servicefavourite  $servicefavourite
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateservicefavouriteRequest $request, servicefavourite $servicefavourite)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\servicefavourite  $servicefavourite
     * @return \Illuminate\Http\Response
     */
    public function destroy(servicefavourite $servicefavourite)
    {
        //
    }
}
