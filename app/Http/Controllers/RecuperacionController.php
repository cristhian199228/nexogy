<?php

namespace App\Http\Controllers;
//use Maatwebsite\Excel\Excel;
use App\Imports\RecuperacionImport;
use Maatwebsite\Excel\Facades\Excel; 
use Illuminate\Http\Request;

class RecuperacionController extends Controller
{
    //
    
    public function importExportView()
    {
       return view('import');
    }
   
    /**
    * @return \Illuminate\Support\Collection
    */
    public function export() 
    {
        return Excel::download(new RecuperacionImport, 'users.xlsx');
    }
   
    /**
    * @return \Illuminate\Support\Collection
    */
    public function import() 
    {
        Excel::import(new RecuperacionImport,request()->file('file'));
           
        return back();
    }
}
