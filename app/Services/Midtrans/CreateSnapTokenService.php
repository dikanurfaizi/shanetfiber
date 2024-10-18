<?php

namespace App\Services\Midtrans;

use App\Models\Websystem;
use Midtrans\Snap;

class CreateSnapTokenService extends Midtrans
{
    protected $order;

    public function __construct($order)
    {
        parent::__construct();

        $this->order = $order;
    }

    public function getSnapToken()
    {
        $websystem = Websystem::where('slug', 'websystem')->first();
        // return dd($this->order->billing->id);
        $params = [
            /**
             * 'order_id' => id order unik yang akan digunakan sebagai "primary key" oleh Midtrans untuk
             * 				 membedakan order satu dengan order lain. Key ini harus unik (tidak boleh ada duplikat).
             * 'gross_amount' => merupakan total harga yang harus dibayar customer.
             */
            'transaction_details' => [
                'order_id' => $this->order->number,
                'gross_amount' => $this->order->total_price,
            ],
            /**
             * 'item_details' bisa diisi dengan detail item dalam order.
             * Umumnya, data ini diambil dari tabel `order_items`.
             */
            'item_details' => [
                [
                    'id' => $this->order->billing->id, // primary key produk
                    'price' => $this->order->billing->paket_price + $this->order->billing->tax, // harga satuan produk
                    'quantity' => 1, // kuantitas pembelian
                    'name' => $this->order->billing->paket_name, // nama produk
                ],
                //[
                //    'id' => 2,
                //    'price' => '60000',
                //     'quantity' => 2,
                //     'name' => 'Memory Card VGEN 4GB',
                //  ],
            ],
            'customer_details' => [
                // Key `customer_details` dapat diisi dengan data customer yang melakukan order.
                'first_name' => $this->order->billing->customer_name,
                //'last_name' => $this->order->billing->customer_name,
                'email' => $this->order->customer->email,
                'phone' => $this->order->customer->contact,
                'billing_address' => [
                    'address' => $this->order->customer->address,
                    // 'first_name' => 'TEST',
                    // 'last_name' => '@midtrans.com',
                    // 'phone' => '081 2233 44-55',
                    // 'address' => 'Sudirman',
                    // 'city' => 'Jakarta',
                    // 'postal_code' => '12190',
                    // 'country_code' => 'IDN'
                ],

                // 'shipping_address' => [
                //     'first_name' => 'TEST',
                //     'last_name' => 'MIDTRANSER',
                //     'email' => 'test@midtrans.com',
                //     'phone' => '0 8128-75 7-9338',
                //      'address' => 'Sudirman',
                //     'city' => 'Jakarta',
                //     'postal_code' => '12190',
                //     'country_code' => 'IDN',
                //  ]
            ],
            'seller_details' => [
                'id' => '01',
                'name' => $websystem->title,
                'email' => $websystem->email,
                'url' => 'https://windawetiga.net',
                'address' => [
                    'first_name' => 'Ari',
                    'last_name' => 'Purnawan',
                    'phone' => $websystem->phone,
                    'address' => $websystem->address,
                    'city' => $websystem->address,
                    'postal_code' => $websystem->postal_code,
                    'country_code' => $websystem->country_code
                ]
            ]
        ];

        $snapToken = Snap::getSnapToken($params);

        return $snapToken;
    }
}
