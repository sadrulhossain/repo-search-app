<?php

namespace App\Imports;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExcelImport implements FromCollection{
    
 public function collection()
    {   
        //return SalesOrder::all();
    }

}
