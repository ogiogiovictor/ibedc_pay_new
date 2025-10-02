<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;

class ViewWalletTopUp extends Component
{

    public $payments = [];
    public $currentPage = 1;
    public $lastPage = 1;
    public $search = ''; // ✅ new property for search

    public function mount()
    {
        $this->fetchPayments();
    }

    public function fetchPayments()
    {
        $response = Http::withoutVerifying()->get("https://intranet.ibedc.com/api/get_payment?page={$this->currentPage}");

//             $result = $response->json();
// dd($result);
        if ($response->successful()) {
            $result = $response->json();
             // If API returns a plain array
        if (isset($result[0])) {
            $this->payments = $result;
            $this->lastPage = 1; // no pagination
        } else {
            $this->payments = $result['data'] ?? [];
            $this->lastPage = $result['last_page'] ?? 1;
        }

        } else {
            $this->payments = [];
            $this->lastPage = 1;
        }
    }

    public function nextPage()
    {
        if ($this->currentPage < $this->lastPage) {
            $this->currentPage++;
            $this->fetchPayments();
        }
    }

    public function prevPage()
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
            $this->fetchPayments();
        }
    }
    

     // ✅ Computed property (filters results by search)
    public function getFilteredPaymentsProperty()
    {
        if (empty($this->search)) {
            return $this->payments;
        }

        return collect($this->payments)->filter(function ($payment) {
            return str_contains(strtolower($payment['customer_name']), strtolower($this->search))
                || str_contains(strtolower($payment['customer_email']), strtolower($this->search))
                || str_contains(strtolower($payment['tx_ref']), strtolower($this->search))
                || str_contains(strtolower($payment['flw_ref']), strtolower($this->search));
        })->values()->all();
    }

    public function reSync($id) {
        dd($id);
    }

    public function render()
    {
        return view('livewire.view-wallet-top-up', [
            'filteredPayments' => $this->filteredPayments,
        ]);
        //return view('livewire.view-wallet-top-up');
    }
}
