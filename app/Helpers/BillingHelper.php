<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Billing\Billing;
use App\Helpers\UserAuth;

class BillingHelper
{
    public static function getBillingStatusBadge($billing, $data)
    {
        $html = '';

        $billingsUnpaid = Billing::where('customer_id', $billing->customer_id)
            ->whereNotIn('status', ['LS', 'NP'])
            ->get();

        $countBillingCustomerUnpaid = $data
            ->where('customer_id', $billing->customer_id)
            ->whereNotIn('status', ['LS', 'NP'])
            ->count();

        if ($billing->status === 'LS') {
            $html .= '<span class="badge bg-success text-white"><i class="fas fa-check-circle"></i> '
                . __('billing.state.lunas') . '</span>';
        } elseif ($billing->status === 'PL') {
            $payLaterDate = Carbon::parse($billing->pay_later);
            $expired = now()->gt($payLaterDate);
            $color = $expired ? 'bg-danger' : 'bg-warning';
            $html .= '<span class="badge ' . $color . ' text-white"><i class="fas fa-ban"></i> '
                . __('billing.state.paylater') . ' ' . $payLaterDate->format('F d, Y') . '</span>';
        } elseif ($billing->status === 'NP') {
            $html .= '<span class="badge bg-primary text-white"><i class="fas fa-info-circle"></i> '
                . __('billing.state.tidak-ada-pemakaian') . '</span>';
        } else {
            $activationDate = Carbon::now()->format('Y-m') . '-' . Carbon::parse($billing->customer->activation_date)->format('d') . ' ' . Carbon::now()->format('H:i:s');
            $expired = now()->gt($activationDate);
            $color = $expired ? 'bg-danger' : 'bg-primary';

            $html .= '<span class="badge ' . $color . ' text-white"><i class="fas fa-ban"></i> '
                . __('billing.state.belum-lunas') . '. due ' . Carbon::parse($billing->due_date)->format('F d, Y') . '</span>';
        }

        $unpaidOther = count($billingsUnpaid) - $countBillingCustomerUnpaid;
        if ($unpaidOther > 0) {
            $html .= '<br><a class="text-xs fw-bold text-danger d-inline-flex align-items-center mt-1" href="'
                . route('billings.index', ['search_status' => '!LS', 'customer' => $billing->customer->slug]) . '">' .
                __('billing.state.belum-lunas-lainnya', ['count_bill_unpaid' => $unpaidOther]) . '</a>';
        }

        return $html;
    }

    public static function getBillingPaymentSection($billing, $bank)
    {
        // Jika status NP (Tidak ada pemakaian)
        if ($billing->status === 'NP') {
            return '<span class="badge bg-primary text-white">
            <i class="fas fa-times-circle"></i> ' .
                __('billing.state.confirm-on') . ' ' .
                Carbon::parse($billing->updated_at)->format('F d, Y') .
                '</span>';
        }

        // Jika belum lunas dan ada harga paket
        if ($billing->status !== 'LS') {
            $formId = 'form-pay-' . $billing->slug;
            $action = route('billings.payment', $billing->slug);

            $html = '<form method="POST" action="' . $action . '" id="' . $formId . '" style="display:inline-block;">';
            $html .= csrf_field();
            $html .= method_field('PATCH');

            $html .= '<select name="payment_method" class="form-control form-control-sm" style="min-width: 180px; width: max-content;">';
            $html .= '<option value="cash">Cash</option>';

            if (!empty($bank) && count($bank)) {
                $html .= '<option value="transfer">Transfer</option>';
            }

            $html .= '<option value="paylater">Pay Later</option>';
            $html .= '<option value="not-active-periode">Not Active Period</option>';
            $html .= '</select>';

            $html .= '</form>';

            return $html;
        }

        // Jika sudah lunas
        if ($billing->status === 'LS') {
            $paymentMethod = $billing->receipt->payment_method ?? '';
            $paymentTime = $billing->payment_time
                ? Carbon::parse($billing->payment_time)->format('F d, Y')
                : '';

            return '<span class="badge bg-success text-white">
            <i class="fas fa-check-circle"></i> ' .
                e($paymentMethod) . ' on ' . e($paymentTime) .
                '</span>';
        }

        return '';
    }

    public static function getBillingActionButton($billing)
    {
        $auth = UserAuth::getUserDetails();
        $user = $auth['user'];
        // Cek permission user
        if (!$user || !method_exists($user, 'can') || !$user->can('billing-edit')) {
            return '';
        }

        // Jika status NP (Tidak ada pemakaian)
        if ($billing->status === 'NP') {
            return '<span class="badge bg-secondary text-white">
            <i class="fas fa-info-circle"></i> ' .
                __('Marked') .
                '</span>';
        }

        // Jika belum lunas (status bukan LS)
        if ($billing->status !== 'LS') {
            // Jika customer sudah aktif
            if ($billing->customer->activation === 'true') {
                return '<a class="btn btn-success btn-sm"
                onclick="document.getElementById(\'form-pay-' . $billing->slug . '\').submit();">
                <i class="fas fa-money-bill-wave text-white"></i> ' .
                    __('billing.button.pay') .
                    '</a>';
            } else {
                $action = route('customers.activation', $billing->customer->slug);

                return '<form method="POST" class="d-inline" action="' . $action . '">'
                    . csrf_field()
                    . method_field('PUT')
                    . '<a class="btn btn-success btn-sm" href="#"
                    onclick="event.preventDefault(); this.closest(\'form\').submit();">
                    <i class="fa fa-check me-2 text-white"></i> ' . __('billing.button.activation') . '
                </a>'
                    . '</form>';
            }
        }

        // Jika sudah lunas (LS) dan ada harga paket
        if ($billing->status === 'LS' && $billing->paket_price > 0) {
            $action = route('billings.unpayment', $billing->slug);

            $html = '<form method="POST" class="d-inline" action="' . $action . '">'
                . csrf_field()
                . method_field('PATCH')
                . '<a class="btn btn-danger btn-sm" href="#"
                onclick="event.preventDefault(); this.closest(\'form\').submit();">
                <i class="fas fa-money-bill-wave text-white"></i> ' . __('billing.button.unpay') . '
            </a>'
                . '</form>';

            return $html;
        }

        return '';
    }
    // Tambahkan juga fungsi getBillingPaymentSection(), getBillingActionButton() jika ingin
}
