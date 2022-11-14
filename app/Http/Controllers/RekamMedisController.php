<?php

namespace App\Http\Controllers;

use App\Models\rekam_medis;
use App\Models\medicine;
use App\Models\penanganan;
use App\Models\orderservice;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RekamMedisController extends Controller
{
    public function create_rek_med(request $request){
        $order = orderservice::where('order_id',$request->order_id)->get();
        if(rekam_medis::where('order_id',$request->order_id)->count==1){
            return response()->JSON([
                'status' => 'error',
                'msg' => 'duplicate medic record'
            ]);
        }
        if($order->value('status')=='ON PROCCESS'){
            $insertrm = rekam_medis::insert([
                'order_id' => $request->order_id,
                'pet_id' => $order->value('pet_id'),
                'keluhan' => $request->keluhan,
                'penanganan_sementara'=> $request->penanganan_sementara,
                'penanganan_lanjut' => $request->penanganan_lanjut,
                'diagnosa' => $request->diagnosa,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            if($insertrm==1){
                return response()->JSON([
                    'status' => 'success'
                ]);
            }
        } else{
            return response()->JSON([
                'status' => 'error',
                'msg' => 'you can not add this record yet'
            ]);
        }
    }

    public function update_rek_med(request $request){
        $order = orderservice::where('order_id',$request->order_id)->get();
        if($order->value('status')=='ON PROCCESS'){
            $updaterm = rekam_medis::where('id',$request->id)->update([
                'keluhan' => $request->keluhan,
                'penanganan_sementara'=> $request->penanganan_sementara,
                'penanganan_lanjut' => $request->penanganan_lanjut,
                'diagnosa' => $request->diagnosa,
                'updated_at' => Carbon::now()
            ]);
    
            if($updaterm==1){   
                return response()->JSON([
                'status' => 'success'
                ]);
            }
        } else{
            return response()->JSON([
                'status' => 'error',
                'msg' => 'you can not edit this record'
            ]);
        }
    }

    public function delete_rek_med(request $request){
        $deleterekmed = rekam_medis::where('order_id',$request->order_id)->delete();

        if($deleterekmed==1){
            return response()->JSON([
                'status' => 'success'
            ]);
        } else{
            return response()->JSON([
                'status' => 'error',
                'msg' => 'rekam medis failed to be deleted'
            ]);
        }
    }

    public function add_obat(request $request){
        $order = orderservice::where('order_id',$request->order_id)->get();
        if($order->value('status')=='ON PROCCESS'){
    
            $insertmd = medicine::insert([
                'rm_id' => $request->rm_id,
                'nama_obat' => $request->nama_obat,
                'penggunaan' => $request->penggunaan
            ]);

            if($insertmd==1){
                return response()->JSON([
                    'status' => 'success'
                ]);
            }
        } else{
            return response()->JSON([
                'status' => 'error',
                'msg' => 'you can not edit this record'
            ]);
        }
    }

    public function edit_obat(request $request){
        $order = orderservice::where('order_id',$request->order_id)->get();
        if($order->value('status')=='ON PROCCESS'){
    
            $updatemd = medicine::insertGetId([
                'rm_id' => $request->rm_id,
                'nama_obat' => $request->nama_obat,
                'penggunaan' => $request->penggunaan
            ]);

            if($updatemd==1){
                return response()->JSON([
                    'status' => 'success'
                ]);
            }
        } else{
            return response()->JSON([
                'status' => 'error',
                'msg' => 'you can not edit this record'
            ]);
        }
    }

    public function delete_obat(request $request){
        $deleteobat = medicine::where('id',$request->id)->delete();

        if($deleteobat==1){
            return response()->JSON([
                'status' => 'success'
            ]);
        } else{
            return response()->JSON([
                'status' => 'error',
                'msg' => 'obat failed to be deleted'
            ]);
        }
    }

    public function add_penanganan(request $request){
        $order = orderservice::where('order_id',$request->order_id)->get();
        if($order->value('status')=='ON PROCCESS'){
    
            $insertpn = penanganan::insert([
                'rm_ids' => $request->rm_id,
                'tindakan' => $request->nama_obat,
                'biaya_tambahan' => $request->penggunaan
            ]);

            if($insertpn==1){
                return response()->JSON([
                    'status' => 'success'
                ]);
            }
        } else{
            return response()->JSON([
                'status' => 'error',
                'msg' => 'you can not edit this record'
            ]);
        }
    }

    public function edit_penanganan(request $request){
        $order = orderservice::where('order_id',$request->order_id)->get();
        if($order->value('status')=='ON PROCCESS'){
    
            $updatepn = penanganan::where('id',$request->id)->update([
                'rm_ids' => $request->rm_id,
                'tindakan' => $request->nama_obat,
                'biaya_tambahan' => $request->penggunaan
            ]);

            if($updatepn==1){
                return response()->JSON([
                    'status' => 'success'
                ]);
            }
        } else{
            return response()->JSON([
                'status' => 'error',
                'msg' => 'you can not edit this record'
            ]);
        }
    }

    public function delete_penanganan(request $request){
        $deleteobat = penanganan::where('id',$request->id)->delete();

        if($deleteobat==1){
            return response()->JSON([
                'status' => 'success'
            ]);
        } else{
            return response()->JSON([
                'status' => 'error',
                'msg' => 'obat failed to be deleted'
            ]);
        }
    }

    public function get_record_detail(request $request){
        $query = DB::table('rekam_medis')
        ->join('penanganan','id','=','rm_ids')
        ->select(['id','order_id','pet_id','keluhan','penanganan_sementara','penanganan_lanjut','diagnosa','penanganan.tindakan','penanganan.biaya_tambahan'])
        ->where('id',$request->id);

        $arr = [
            'rm_id' => $query->id,
            'order_id' => $query->order_id,
            'pet_id' => $query->pet_id,
            'keluhan' => $query->keluhan,
            'penanganan_sementara' => $query->penanganan_sementara,
            'penanganan_lanjut' => $query->penanganan_lanjut,
            'diagnosa' => $query->diagnosa,
            'obat' => medicine::where('rm_id',$request->id)->get(),
            'tindakan' => $query->tindakan,
            'biaya_tambahan' => $query->biaya_tambahan

        ];

        return response()->JSON([
            'status' => 'success',
            'results' => $arr
        ]);
    }
}
