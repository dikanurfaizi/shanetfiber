<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Billings\Billing;
use App\Models\Customers\Customer;
use App\Helpers\UserAuth;

class BillingHelper
{
    public static function getBillingStatusBadge($billing, $data)
    {
        $html = '';

        // tidak perlu ambil ulang billing
        $billingsUnpaid = Billing::with('customer')
            ->where('customer_id', $billing->customer_id)
            ->whereNotIn('status', ['LS', 'VD'])
            ->get();

        $countBillingCustomerUnpaid = $data
            ->where('customer_id', $billing->customer_id)
            ->whereNotIn('status', ['LS', 'VD'])
            ->count();

        if ($billing->status === 'LS') {
            $html .= '<span class="badge bg-success text-white"><i class="fas fa-check-circle"></i> '
                . __('billing.state.lunas') . '</span>';
        } elseif ($billing->status === 'PL') {
            $payLaterDate = Carbon::parse($billing->paylater_until);
            $expired = now()->gt($payLaterDate);
            $color = $expired ? 'bg-danger' : 'bg-warning';
            $html .= '<span class="badge ' . $color . ' text-white"><i class="fas fa-ban"></i> '
                . __('billing.state.paylater') . ' ' . $payLaterDate->translatedFormat('d F, Y') . '</span>';
        } elseif ($billing->status === 'NP') {
            $html .= '<span class="badge bg-primary text-white"><i class="fas fa-info-circle"></i> '
                . __('billing.state.tidak-ada-pemakaian') . '</span>';
        } else {
            $expired = now()->gt(Carbon::parse($billing->due_date));
            $color = $expired ? 'bg-danger' : 'bg-primary';

            $html .= '<span class="badge ' . $color . ' text-white"><i class="fas fa-ban"></i> '
                . __('billing.state.belum-lunas') . '. ' . __('billing.state.due-date') . ' ' . Carbon::parse($billing->due_date)->translatedFormat('d F, Y') . '</span>';
        }

        $unpaidOther = count($billingsUnpaid) - $countBillingCustomerUnpaid;
        if ($unpaidOther > 0 && $billing->customer) {
            $html .= '<br><a class="text-xs fw-bold text-danger d-inline-flex align-items-center mt-1" href="'
                . route('billings.index', ['search_status' => '!LS', 'customer' => $billing->customer->slug]) . '">' .
                __('billing.state.belum-lunas-lainnya', ['count_bill_unpaid' => $unpaidOther]) . '</a>';
        }

        return $html;
    }

    public static function getBillingPaymentSection($billing, $bank)
    {
        // Jika status NP
        if ($billing->status === 'VD') {
            return '<span class="badge bg-primary text-white">
            <i class="fas fa-times-circle"></i> ' .
                __('billing.state.confirm-on') . ' ' .
                e(Carbon::parse($billing->updated_at)->translatedFormat('d F, Y')) .
                '</span>';
        }

        // Jika sudah lunas
        if ($billing->status === 'LS') {
            $paymentMethod = $billing->billingPayment->payment_method ?? '-';
            $paymentTime = $billing->billingPayment
                ? Carbon::parse($billing->billingPayment->payment_time)->translatedFormat('d F, Y')
                : '';

            return '<span class="badge bg-success text-white">
            <i class="fas fa-check-circle"></i> ' .
                ucfirst(e($paymentMethod)) . ' on ' . e($paymentTime) .
                '</span>';
        }

        // Jika user TIDAK punya permission billing-edit → hanya tampilkan label merah
        if (!auth()->user() || !auth()->user()->can('billing-edit')) {
            // Jika belum lunas (status selain NP dan LS)
            $dueDate = $billing->due_date
                ? Carbon::parse($billing->due_date)->translatedFormat('d F Y')
                : '-';

            $label = '<span class="badge bg-danger text-white">
        <i class="fas fa-exclamation-circle"></i> ' . __('billing.state.belum-lunas') . ', ' . __('billing.state.due-date') . ' ' . e($dueDate) . '
        </span>';

            return $label;
        }

        // Jika user punya permission billing-edit → tampilkan dropdown payment method
        $html = '<select name="payment_method"
            class="form-control form-control-sm billing-method-select mt-1"
            data-slug="' . e($billing->slug) . '"
            style="min-width: 180px; width: max-content;">';

        $html .= '<option value="cash">' . __('billing.state.cash') . '</option>';

        if (!empty($bank) && count($bank)) {
            $html .= '<option value="transfer">' . __('billing.state.transfer') . '</option>';
        }

        $html .= '<option value="paylater">' . __('billing.state.paylater') . '</option>';
        $html .= '<option value="not-active-periode">' . __('billing.state.tidak-ada-pemakaian') . '</option>';
        $html .= '</select>';

        return $html;
    }

    public static function getBillingActionButton($billing)
    {
        $auth = UserAuth::getUserDetails();
        $user = $auth['user'];

        if (!$user || !method_exists($user, 'can') || !$user->can('billing-edit')) {
            return '';
        }

        if ($billing->status === 'VD') {
            return '<span class="badge bg-secondary text-white">
            <i class="fas fa-info-circle"></i> Marked
        </span>';
        }

        $customer = $billing->customer ?? Customer::find($billing->customer_id);
        $buttons = [];

        // ======================
        // BL / PL
        // ======================
        if (in_array($billing->status, ['BL', 'PL'], true)) {

            if ($customer && $customer->activation === 'true') {

                $buttons[] = '<button type="button"
                        class="btn btn-success btn-sm billing-submit-btn"
                        data-slug="' . $billing->slug . '">
                        <i class="fas fa-money-bill-wave text-white"></i> ' . __('billing.button.pay') . '
                    </button>

                    <a class="btn btn-outline-primary btn-sm ml-1"
                        target="_blank"
                        href="' . route('billings.print-invoice', $billing->slug) . '">
                        <i class="fas fa-file-pdf"></i> ' . __('billing.button.invoice') . '
                    </a>';
            } elseif ($customer) {

                $action = route('customers.activation', $customer->slug);

                $buttons[] = '<form method="POST" class="d-inline" action="' . $action . '">'
                    . csrf_field()
                    . method_field('PUT')
                    . '<a class="btn btn-success btn-sm mt-1" href="#"
                    onclick="event.preventDefault(); this.closest(\'form\').submit();">
                    <i class="fa fa-check me-2 text-white"></i> ' . __('billing.button.activation') . '
                </a>'
                    . '</form>';
            }
        }

        // ======================
        // LS
        // ======================
        if ($billing->status === 'LS' && $billing->total_amount > 0) {

            $buttons[] = '<form method="POST" class="d-inline" action="'
                . route('billings.unpayment', $billing->slug) . '">'
                . csrf_field()
                . method_field('PATCH')
                . '<a class="btn btn-danger btn-sm mt-1" href="#"
                onclick="event.preventDefault(); this.closest(\'form\').submit();">
                <i class="fas fa-money-bill-wave text-white"></i> ' . __('billing.button.unpay') . '
            </a>'
                . '</form>';
        }

        return implode(' ', $buttons);
    }
}
