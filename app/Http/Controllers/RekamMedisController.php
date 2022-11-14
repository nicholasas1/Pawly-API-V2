<?php

namespace App\Http\Controllers;

use App\Models\rekam_medis;
use App\Models\medicine;
use App\Models\penanganan;
use App\Models\orderservice;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RekamMedisController extends Controller
{
    public function create_rek_med(request $request){
        $order = orderservice::where('order_id',$request->order_id)->get();
        if($order->value('status')=='ON PROCCESS'){
            $insertrm = rekam_medis::insertGetId([
                'order_id' => $request->order_id,
                'pet_id' => $order->value('pet_id'),
                'keluhan' => $request->keluhan,
                'penanganan_sementara'=> $request->penanganan_sementara,
                'penanganan_lanjut' => $request->penanganan_lanjut,
                'diagnosa' => $request->diagnosa,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            $insertmd = medicine::insertGetId([
                'rm_id' => $insertrm,
                'nama_obat' => $request->nama_obat,
                'penggunaan' => $request->penggunaan
            ]);

            $insertpenanganan = penanganan::insertGetId([
                'rm_ids' => $insertrm,
                'tindakan' => $request->tindakan,
                'biaya_tambahan' => $request->biaya_tambahan
            ]);

            $checkrm = rekam_medis::where('id',$insertrm);
            $checkmd = medicine::where('id',$insertmd);
            $checkpg = penanganan::where('id',$insertpenanganan);

            if($checkrm->count()==1&&$checkmd->count()==1&&$checkpg->count()==1){
                $updateorder = orderservice::where('order_id',$request->order_id)->update([
                    'status' => 'COMPLATE'
                ]);
                if($updateorder==1){
                    return response()->JSON([
                        'status' => 'success'
                    ]);
                }
            }
        } else{
            return response()->JSON([
                'status' => 'error',
                'msg' => 'you can not edit this record'
            ]);
        }
    }


}
