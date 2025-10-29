<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class VentasExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
{
    protected $desde;
    protected $hasta;
    protected $store;

    public function __construct($desde, $hasta, $storeId = null)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
        $this->store = $storeId;
    }

    public function collection()
    {
        $q = DB::table('sales as s')
            ->join('customers as c','c.id','=','s.customer_id')
            ->select('s.date','s.id','c.name as customer','s.subtotal','s.tax','s.total')
            ->whereBetween('s.date', [$this->desde, $this->hasta])
            ->orderBy('s.date')->orderBy('s.id');

        if ($this->store) $q->where('s.store_id',$this->store);

        return $q->get();
    }

    public function headings(): array
    {
        return ['Fecha','Doc','Cliente','Gravado','IVA','Total'];
    }

    public function map($row): array
    {
        return [
            $row->date,
            'VTA-'.$row->id,
            $row->customer,
            (float)$row->subtotal,
            (float)$row->tax,
            (float)$row->total,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER_00,
            'E' => NumberFormat::FORMAT_NUMBER_00,
            'F' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
