<?php

use App\Models\Midtran;
use App\Models\Websystem;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\IsolirController;
use App\Http\Controllers\PacketController;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\UpdateController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DropdownController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AutoIsolirController;
use App\Http\Controllers\TestCommandController;
use App\Http\Controllers\Midtran\OrderController;
use App\Http\Controllers\TestConnectionController;
use App\Http\Controllers\Mikrotik\SystemController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Mikrotik\MikrotikController;
use App\Http\Controllers\Auth\CustomerLoginController;
use App\Http\Controllers\Mikrotik\Ppp\SecretController;
use App\Http\Controllers\PaymentGateway\MidtranController;
use App\Http\Controllers\Midtran\PaymentCallbackController;
use App\Http\Controllers\PaymentGateway\PaymentGatewayController;
use App\Http\Controllers\WhatsappGateway\WhatsappGatewayController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\Mikrotik\Ppp\ProfileController as MikrotikProfileController;
use App\Http\Controllers\WebsystemController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\Paket\PppProfileController;
use App\Http\Controllers\ApiUrlController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/




/// Silahkan dihapus setelah melakukan instalasi

// Batas akhir hapus setelah instalasi

Route::resource('test-command', TestCommandController::class);
Route::get('/isolir', [IsolirController::class, 'index'])->name('isolir');
Route::redirect('/', '/customer/login');

Route::post('payments/midtrans-notification', [PaymentCallbackController::class, 'receive']);

Route::group(['prefix' => 'customer', 'namespace' => 'Customer'], function () {
    Route::group(['middleware' => 'guest'], function () {
        Route::get('/login', [CustomerLoginController::class, 'login'])->name('customerLogin');
        Route::post('/login', [CustomerLoginController::class, 'postLogin'])->name('customerLoginPost');
    });
    Route::group(['middleware' => 'customerauth'], function () {
        // Route::get('/', function () {
        //    return view('customers.pages.dashboard');
        // })->name('customerDashboard');
        Route::get('/', [CustomerProfileController::class, 'index'])->name('customer.dashboard');
        Route::get('/profile', [CustomerProfileController::class, 'show'])->name('customer.profile');
        Route::post('/logout', [CustomerLoginController::class, 'customerLogout'])->name('customerLogout');

        //Route::resource('orders', OrderController::class)->only(['index', 'show']);
        Route::get('billings/payments', [PaymentController::class, 'index'])->name('customer.payment.index');

        Route::get('billings/payments/{status}/show', [PaymentController::class, 'index'])->name('customer.payment.state');
        Route::get('billings/payments/{billing:id}/view', [PaymentController::class, 'show'])->name('customer.payment.show');

        // $paymentGateway = Websystem::where('slug', 'websystem')->value('payment_gateway');
        //  if ($paymentGateway == 'true') {
        Route::get('billings/orders', [OrderController::class, 'index'])->name('customer.order.index');
        Route::get('billings/orders/{status}/show', [OrderController::class, 'index'])->name('customer.order.state');
        Route::get('billings/orders/{order:id}/view', [OrderController::class, 'show'])->name('customer.order.show');
        Route::post('billings/orders/{order:id}/update', [OrderController::class, 'update'])->name('customerOrder.update');
        Route::get('billings/orders/{billing:id}/create', [OrderController::class, 'create'])->name('customer.order.create');
        //  }
    });
});

//$urlCpanel = Websystem::where('title', 'Customer Management')->value('cpanel_url');
//if ($urlCpanel == NULL) {
//    $urlCpanel = 'cpanel';
//}
Route::group(['prefix' => 'cpanel'], function () {
    Auth::routes([
        'register' => false, // Register Routes...
        'reset' => false, // Reset Password Routes...
        'verify' => false, // Email Verification Routes...
    ]);

    Route::group(['middleware' => ['auth']], function () {
        Route::get('/home', HomeController::class)->name('home');
        Route::view('about', 'about')->name('about');
        Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::post('/testcon', [TestConnectionController::class, 'testConn'])->name('test-con');
        Route::post('server/cek-online', [TestConnectionController::class, 'checkOnline'])->name('check-online');
        Route::get('/server/{mikrotik:slug}', [MikrotikController::class, 'show'])->name('mikrotiks.show');
        Route::get('/server/{mikrotik:slug}/get-resource', [SystemController::class, 'routerResource'])->name('mikrotik.resources');
        //=============================PPP SECRETS================================================================================
        Route::get('/server/{mikrotik:slug}/ppp/secrets', [SecretController::class, 'index'])->name('ppp.secrets.index');
        Route::get('/server/{mikrotik:slug}/ppp/secret/create', [SecretController::class, 'create'])->name('ppp.secret.create');
        Route::post('/server/{mikrotik:slug}/ppp/secret/store', [SecretController::class, 'store'])->name('ppp.secret.store');
        Route::put('/server/{mikrotik:slug}/ppp/secret/update', [SecretController::class, 'update'])->name('ppp.secret.update');
        Route::post('/server/{mikrotik:slug}/ppp/secret/import', [SecretController::class, 'clone_secret'])->name('ppp.secret.clone');
        Route::put('/server/{mikrotik:slug}/ppp/secret/enable', [SecretController::class, 'enable'])->name('ppp.secret.enable');
        Route::put('/server/{mikrotik:slug}/ppp/secret/disable', [SecretController::class, 'disable'])->name('ppp.secret.disable');
        Route::get('/server/{mikrotik:slug}/ppp/secret/edit', [SecretController::class, 'edit'])->name('ppp.secret.edit');
        Route::delete('/server/{mikrotik:slug}/ppp/secret/delete', [SecretController::class, 'delete'])->name('ppp.secret.delete');
        //=================================PPP Profiles==============================================================================
        Route::get('/server/{mikrotik:slug}/ppp/profiles', [MikrotikProfileController::class, 'index'])->name('ppp.profiles.index');
        Route::post('/server/{mikrotik:slug}/ppp/profiles/import', [MikrotikProfileController::class, 'clone_profile'])->name('ppp.profiles.clone');
        //==================================New PPP Profiles====================================================
        Route::resource('paket/ppp-profiles', PppProfileController::class);
        //==================================Paket====================================================
        Route::resource('packets', PacketController::class);
        Route::get('/packets/{packet:slug}/delete', [PacketController::class, 'delete'])->name('paket.delete');
        Route::put('/packets/{packet:slug}/disabled/true', [PacketController::class, 'disable'])->name('paket.disable');
        Route::put('/packets/{packet:slug}/disabled/false', [PacketController::class, 'enable'])->name('paket.enable');
        //========================ROUTE CUSTOMER===============================
        Route::resource('customers', CustomerController::class);
        Route::get('/customers/search', [PaymentController::class, 'index'])->name('customers.search');
        Route::put('/customers/{customer:slug}/disabled/true', [CustomerController::class, 'disable'])->name('customers.disable');
        Route::put('/customers/{customer:slug}/disabled/false', [CustomerController::class, 'enable'])->name('customers.enable');
        Route::put('/customers/{customer:slug}/force-disabled/true', [CustomerController::class, 'forceDisable'])->name('customers.forceDisable');
        Route::put('/customers/{customer:slug}/force-disabled/false', [CustomerController::class, 'forceEnable'])->name('customers.forceEnable');
        // Route::get('/customers/disabled/{disabled}/show', [CustomerController::class, 'showDisabled'])->name('customers.show.disabled');
        Route::get('/customers/{status}/show', [CustomerController::class, 'showStatusCustomer'])->name('customers.show.status');
        Route::get('/customers/{customer:slug}/delete', [CustomerController::class, 'delete'])->name('customers.delete');
        Route::get('/customers/{customer:slug}/edit/ppp', [CustomerController::class, 'editPpp'])->name('customers.edit.ppp');
        Route::patch('/customers/{customer:slug}/ppp/update', [CustomerController::class, 'updatePpp'])->name('customers.update.ppp');
        Route::get('/customers/{customer:slug}/router/edit', [CustomerController::class, 'editRouter'])->name('customers.edit.router');
        Route::patch('/customers/{customer:slug}/router/update', [CustomerController::class, 'updateRouter'])->name('customers.update.router');
        Route::put('/customers/{customer:slug}/activation', [CustomerController::class, 'activation'])->name('customers.activation');


        //========================ROUTE Billing===============================
        Route::delete('billing/periode/{billingperiode:id}/destroy', [BillingController::class, 'destroyPeriode'])->name('billing.periode.destroy');
        Route::get('billing/periode/{billingperiode:id}/delete', [BillingController::class, 'deletePeriode'])->name('billing.periode.delete');
        Route::resource('billings', BillingController::class);
        Route::resource('banks', BankController::class);
        Route::put('/banks/{bank}/disabled/true', [BankController::class, 'disable'])->name('bank.disable');
        Route::put('/banks/{bank}/disabled/false', [BankController::class, 'enable'])->name('bank.enable');
        Route::resource('receipts', ReceiptController::class);
        Route::get('receipt/{receipt}/print/', [ReceiptController::class, 'print'])->name('receipts.print');
        Route::get('receipt/{receipt}/thermal', [ReceiptController::class, 'thermalPrint'])->name('receipts.thermal');
        //Route::get('/billings/state/{status}/show', [BillingController::class, 'index'])->name('billings.show.state');
        Route::get('billings/create/one', [BillingController::class, 'create_one'])->name('billings.create.one');
        Route::post('billings/store/one', [BillingController::class, 'store_one'])->name('billings.store.one');
        Route::patch('billings/{billing:slug}/payment', [BillingController::class, 'payment'])->name('billings.payment');
        Route::get('billings/{billing:slug}/paylater', [BillingController::class, 'paylater'])->name('billings.paylater');
        Route::get('billings/{billing:slug}/transfer', [BillingController::class, 'transfer'])->name('billings.transfer');
        Route::patch('billings/{billing:slug}/post_paylater', [BillingController::class, 'post_paylater'])->name('billings.postPaylater');
        Route::patch('billings/{billing:slug}/unpayment', [BillingController::class, 'unpayment'])->name('billings.unpayment');
        //Route::get('billings/payment/search', [BillingController::class, 'searchPayment'])->name('billings.payment.search');
        // Route::get('billings/unpayment/search', [BillingController::class, 'searchUnpayment'])->name('billings.unpayment.search');

        Route::get('/server/{mikrotik:slug}/time', [SystemController::class, 'getTimeRouter'])->name('mikrotik.time');
        Route::get('/server/{mikrotik:slug}/ppp/secret/get-all-ppp-secrets', [SecretController::class, 'getCountPppSecrets'])->name('get.ppp.secrets');
        Route::get('/server/{mikrotik:slug}/ppp/secret/get-all-ppp-secrets-active', [SecretController::class, 'getCountPppSecretsActive'])->name('get.ppp.active');
        Route::get('/server/{mikrotik:slug}/ppp/secret/get-active-byName', [CustomerController::class, 'getPppSecretsActiveByName'])->name('get.ppp.active.byName');
        Route::get('/server/traffic/{mikrotik:slug}/get-monitoring', [SystemController::class, 'mikrotikTrafficInterface'])->name('mikrotik.traffic_interface');

        Route::resource('settings/autoisolir', AutoIsolirController::class);
        Route::get('settings/autoisolirs/{autoisolir:id}/nat', [AutoisolirController::class, 'edit_nat'])->name('autoisolir.nat.setting');
        Route::patch('settings/autoisolirs/{autoisolir:id}/nat/update', [AutoisolirController::class, 'update_nat'])->name('autoisolir.updateNat.setting');
        //Route::get('settings/customers', [CustomerController::class, 'setting'])->name('customer.setting');
        //Route::get('settings/pakets', [PacketController::class, 'setting'])->name('paket.setting');
        //Route::post('settings/customers/export', [CustomerController::class, 'exportToRouter'])->name('customers.export');
        Route::patch('settings/customers/activation', [CustomerController::class, 'activation_all_customer'])->name('customers.all.activation');
        // Route::post('settings/pakets/export', [PacketController::class, 'exportToRouter'])->name('pakets.export');
        Route::get('settings/exports', [ExportController::class, 'index'])->name('export.index');
        Route::post('settings/exports', [ExportController::class, 'exportToRouter'])->name('export.post');
        Route::resource('settings/roles', RoleController::class);
        Route::resource('settings/users', UserController::class);
        Route::get('settings/users{user:id}/delete', [UserController::class, 'delete'])->name('users.delete');

        Route::put('settings/users/{user:id}/disabled/true', [UserController::class, 'disable'])->name('user.disable');
        Route::put('settings/users/{user:id}/disabled/false', [UserController::class, 'enable'])->name('user.enable');
        Route::resource('settings/servers', ServerController::class);
        Route::resource('settings/routers', RouterController::class);
        Route::get('settings/routers/{router:id}/delete', [RouterController::class, 'delete'])->name('router.delete');
        Route::resource('settings/router/brand', BrandController::class);
        Route::get('settings/router/brand/{brand:id}/delete', [BrandController::class, 'delete'])->name('brand.delete');

        //Payment Gateway
        Route::get('settings/payment-gateway', [PaymentGatewayController::class, 'index'])->name('paymentgateway.index');
        Route::get('settings/payment-gateway/{paymentGateway:slug}', [PaymentGatewayController::class, 'edit'])->name('paymentgateway.edit');
        Route::get('settings/payment-gateway/1/{midtran:slug}', [MidtranController::class, 'edit'])->name('paymentgateway.midtran.edit');
        Route::patch('settings/payment-gateway/1/{midtran:slug}/update', [MidtranController::class, 'update'])->name('paymentgateway.midtran.update');

        // WhatsApp Gateway
        Route::get('settings/whatsapp-gateway', [WhatsappGatewayController::class, 'index'])->name('whatsapp-gateway.index'); // Menampilkan daftar WhatsApp Gateway
        Route::get('settings/whatsapp-gateway/create', [WhatsappGatewayController::class, 'create'])->name('whatsapp-gateway.create'); // Menampilkan form untuk membuat WhatsApp Gateway
        Route::post('settings/whatsapp-gateway', [WhatsappGatewayController::class, 'store'])->name('whatsapp-gateway.store'); // Menyimpan data WhatsApp Gateway yang baru
        Route::get('settings/whatsapp-gateway/{id}/edit', [WhatsappGatewayController::class, 'edit'])->name('whatsapp-gateway.edit'); // Menampilkan form untuk mengedit WhatsApp Gateway
        Route::patch('settings/whatsapp-gateway/{whatsappGateway:slug}', [WhatsappGatewayController::class, 'update'])->name('whatsapp-gateway.update'); // Mengupdate data WhatsApp Gateway
        Route::delete('settings/whatsapp-gateway/{id}', [WhatsappGatewayController::class, 'destroy'])->name('whatsapp-gateway.destroy'); // Route untuk delete
        Route::get('settings/whatsapp-gateway/get-customers', [WhatsappGatewayController::class, 'getCustomers']);

        //Web Systems
        Route::patch('settings/{webSystem:slug}/payment-gateway', [WebsystemController::class, 'update_paymentgateway'])->name('websystem.updatepaymentgateway');
        Route::patch('settings/{webSystem:slug}/whatsapp-gateway', [WebSystemController::class, 'update_whatsappGateway'])->name('websystem.updatewhatsappgateway');
        Route::get('settings/{webSystem:slug}/edit', [WebsystemController::class, 'general'])->name('websystem.edit');
        Route::get('settings/{webSystem:slug}/customers', [WebsystemController::class, 'customer'])->name('websystem.customer');
        Route::patch('settings/{webSystem:slug}/customers/update', [WebsystemController::class, 'update_customer'])->name('websystem.update.customer');
        Route::patch('settings/{webSystem:slug}/update', [WebsystemController::class, 'update_general'])->name('websystem.update');

        Route::get('settings/update/start', [UpdateController::class, 'updated'])->name('settings.update.start');
        Route::get('settings/update', [UpdateController::class, 'index'])->name('settings.update.index');
        Route::get('settings/update/optimize', [UpdateController::class, 'optimize'])->name('settings.update.optimize');

        Route::get('/server/{server:slug}/delete', [ServerController::class, 'delete'])->name('servers.delete');
        Route::put('/server/{server:slug}/disabled/true', [ServerController::class, 'disable'])->name('servers.disable');
        Route::put('/server/{server:slug}/disabled/false', [ServerController::class, 'enable'])->name('servers.enable');
        Route::put('/autoisolir/{autoisolir:id}/disabled/true', [AutoIsolirController::class, 'disable'])->name('autoisolir.disable');
        Route::put('/autoisolir/{autoisolir:id}/disabled/false', [AutoIsolirController::class, 'enable'])->name('autoisolir.enable');
        Route::put('/autoisolir/{autoisolir:id}/force-disabled', [AutoIsolirController::class, 'force_disable'])->name('autoisolir.force_disable');
    });
});

Route::group(['middleware' => ['auth']], function () {
    Route::post('api/fetch-pakets', [DropdownController::class, 'fetchPaket'])->name('fetch.paket');
    Route::post('api/fetch-profiles', [DropdownController::class, 'fetchProfile']);
    Route::post('api/fetch-parent-queues', [DropdownController::class, 'fetchParentQueue']);
    Route::post('api/fetch-queue-types', [DropdownController::class, 'fetchQueueType']);
    // Route::post('api/fetch-queue-types', [DropdownController::class, 'fetchQueueType']);
    Route::post('api/fetch-scripts', [DropdownController::class, 'fetchScript']);
    Route::post('api/fetch-schedules', [DropdownController::class, 'fetchSchedule']);
    Route::post('api/fetch-type-routers', [DropdownController::class, 'fetchTypeRouter']);
    Route::post('api/fetch-account-bank', [DropdownController::class, 'fetchAccountBank'])->name('fetch.account.bank');
    Route::resource('test-command', TestCommandController::class);
    Route::get('api/get-customers', [WhatsappGatewayController::class, 'getCustomers']);
    Route::post('api/urls', [ApiUrlController::class, 'store']); // Menyimpan URL
    Route::get('api/urls', [ApiUrlController::class, 'index']); // Mengambil semua URL
    Route::get('api/urls/{id}', [ApiUrlController::class, 'show']); // Mengambil URL berdasarkan ID
    Route::put('api/urls/{id}', [ApiUrlController::class, 'update']); // Mengupdate URL berdasarkan ID
    Route::delete('api/urls/{id}', [ApiUrlController::class, 'destroy']);
    Route::post('api/send-whatsapp', [WhatsAppGatewayController::class, 'sendMessage']);
    Route::post('api/send-broadcast', [WhatsAppGatewayController::class, 'sendBroadcastMessage']);
});
