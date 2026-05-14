<?php
namespace App\Imports;

use App\Models\SupplierSalesData;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;

class SupplierSalesDataImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $insertData = [];
        $userId     = Auth::id() ?? 1;

        // Skip header row
        $rows->shift();

        foreach ($rows as $row) {

            if (! $row->filter()->count()) {
                continue;
            }

            $insertData[] = [
                'supplier'            => $this->clean($row[0] ?? null),
                'invoice_date'        => $this->parseDate($row[1] ?? null),
                'invoice_no'          => $this->clean($row[2] ?? null),
                'order_ref'           => $this->clean($row[3] ?? null),
                'pip_code'            => $this->clean($row[4] ?? null),
                'account_no'          => $this->clean($row[5] ?? null),
                'customer_name'       => $this->clean($row[6] ?? null),
                'address1'            => $this->clean($row[7] ?? null),
                'address2'            => $this->clean($row[8] ?? null),
                'address3'            => $this->clean($row[9] ?? null),
                'postcode'            => $this->clean($row[10] ?? null),
                'quantity'            => is_numeric($row[11] ?? null) ? $row[11] : 0,
                'pack'                => $this->clean($row[12] ?? null),
                'product_description' => $this->clean($row[13] ?? null),
                'batch_no'            => $this->clean($row[14] ?? null),
                'status'              => 1,
                'created_by'          => $userId,
                'updated_by'          => $userId,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];
        }

        if (! empty($insertData)) {
            foreach (array_chunk($insertData, 500) as $chunk) {
                SupplierSalesData::insert($chunk);
            }
        }
    }

    private function clean($value)
    {
        return is_string($value) ? trim($value) : $value;
    }

    private function parseDate($date)
    {
        try {
            return $date ? Carbon::parse($date) : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
