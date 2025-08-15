<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\IsolirController;
use App\Http\Controllers\Paket\PacketController;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\UpdateController;
use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\DropdownController;
use App\Http\Controllers\AutoIsolirController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TestConnectionController;
use App\Http\Controllers\Mikrotik\SystemController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Mikrotik\MikrotikController;
use App\Http\Controllers\Auth\CustomerLoginController;
use App\Http\Controllers\Mikrotik\Ppp\SecretController;
use App\Http\Controllers\PaymentGateway\MidtranController;
use App\Http\Controllers\PaymentGateway\TripayController;
use App\Http\Controllers\Midtran\PaymentCallbackController;
use App\Http\Controllers\PaymentGateway\PaymentGatewayController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\Mikrotik\Ppp\ProfileController as MikrotikProfileController;
use App\Http\Controllers\WebsystemController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\Paket\PppProfileController;
use App\Http\Controllers\WhatsappGatewayController;
use App\Http\Controllers\WhatsappMessageController;
use App\Http\Controllers\WhatsappNumberController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\TelegramGatewayController;
use App\Http\Controllers\TelegramMessageController;
use App\Http\Controllers\TelegramChatIdController;
use App\Http\Controllers\Paket\PaketCategoriesController;
use App\Http\Controllers\Subscription\SubscriptionDurationController;
use App\Http\Controllers\Subscription\CustomerSubscriptionController;
use App\Http\Controllers\Billing\BillingHistoryController;
use Illuminate\Support\Facades\Mail;

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

//Silahkan dihapus setelah melakukan instalasi
// Route::get('install', [InstallController::class, 'install'])->name('install');
// Route::get('install/start', [InstallController::class, 'start'])->name('installStart');
// Route::get('install/seeder', [InstallController::class, 'seeder'])->name('installSeeder');
// Route::get('install/create/seeder', [InstallController::class, 'create_seeder'])->name('installCreateSeeder');
// Route::get('install/create', [InstallController::class, 'create_admin'])->name('installCreateAdmin');
// Route::post('install/store', [InstallController::class, 'store_admin'])->name('installStoreAdmin');
// Route::get('install/finish', [InstallController::class, 'finish'])->name('installFinish');
//Batas akhir hapus setelah instalasi


//Route::resource('test-command', TestCommandController::class);
Route::get('/isolir', [IsolirController::class, 'index'])->name('isolir');
Route::redirect('/', '/customer/login');
Route::post('payments/midtrans-notification', [PaymentCallbackController::class, 'receive']);
Route::group(['prefix' => 'customer', 'namespace' => 'Customer'], function () {
    Route::group(['middleware' => 'guest'], function () {
        Route::get('/login', [CustomerLoginController::class, 'login'])->name('customerLogin');
        Route::post('/login', [CustomerLoginController::class, 'postLogin'])->name('customerLoginPost');
    });
    Route::group(['middleware' => 'customerauth'], function () {
        Route::get('/', [CustomerProfileController::class, 'index'])->name('customer.dashboard');
        Route::get('/profile', [CustomerProfileController::class, 'show'])->name('customer.profile');
        Route::post('/logout', [CustomerLoginController::class, 'customerLogout'])->name('customerLogout');
        Route::get('billings/payments', [PaymentController::class, 'index'])->name('customer.payment.index');
        Route::get('billings/payments/{status}/show', [PaymentController::class, 'index'])->name('customer.payment.state');
        Route::get('billings/payments/{billing:id}/view', [PaymentController::class, 'show'])->name('customer.payment.show');
        Route::get('billings/orders', [OrderController::class, 'index'])->name('customer.order.index');
        Route::get('billings/orders/{status}/show', [OrderController::class, 'index'])->name('customer.order.state');
        Route::post('billings/orders/{order:id}/update', [OrderController::class, 'update'])->name('customer.order.update');
        Route::get('billings/orders/{billing:id}/create', [OrderController::class, 'create'])->name('customer.order.create');
        //================================TRIPAY==================================================================================
        Route::post('billings/orders/create/{billing:id}', [OrderController::class, 'tripayCreate'])->name('customer.tripays.order.create');
        Route::get('billings/orders/{order:id}/view', [OrderController::class, 'tripayShow'])->name('customer.pages.order.tripays.show');
        Route::get('billings/orders/{order:id}/instruction', [OrderController::class, 'paymentInstruction'])->name('customer.tripay.instruction');
        Route::get('billings/orders/{order:id}/detail', [OrderController::class, 'show'])->name('customer.order.show');
        Route::patch('billings/orders/cancel/payment/{order:id}', [OrderController::class, 'cancelPayment'])->name('customer.order.tripays.cancel');
        Route::get('billings/orders/{order:id}/status', [OrderController::class, 'checkStatus'])->name('customer.order.tripays.status');
    });
});

Route::group(['prefix' => 'cpanel'], function () {
    Auth::routes([
        'register' => false, // Register Routes...
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
        Route::resource('/packets/ppp-profiles', PppProfileController::class)->names('ppp-profiles');
        Route::get('/packets/ppp-profiles/{ppp_profile}/delete', [PppProfileController::class, 'delete'])->name('ppp-profiles.delete');
        Route::put('/packets/ppp-profiles/{mikrotik:slug}/disabled/true', [PppProfileController::class, 'disable'])->name('ppp-profiles.disable');
        Route::put('/packets/ppp-profiles/{mikrotik:slug}/disabled/false', [PppProfileController::class, 'enable'])->name('ppp-profiles.enable');
        // Route::get('/server/ppp/profiles/mikrotik/list', [PppProfileController::class, 'mikrotik_list'])->name('ppp.profiles.mikrotik.list');
        Route::get('/packets/ppp-profiles/{mikrotik}/list', [PppProfileController::class, 'get_mikrotik_profiles'])->name('ppp.profiles.get.mikrotik.profiles');
        Route::get('/packets/ppp-profiles/import/data', [PppProfileController::class, 'viewServer'])->name('ppp-profiles.import.data');
        Route::post('/packets/ppp-profiles/export/{mikrotik:slug}', [PppProfileController::class, 'export'])->name('ppp-profiles.export');
        Route::post('/packets/ppp-profiles/export-one/{mikrotik:id}', [PppProfileController::class, 'export_one'])->name('ppp-profiles.export-one');
        Route::post('/packets/profiles/import', [PppProfileController::class, 'import'])->name('ppp.profiles.import');
        //==================================Paket====================================================
        Route::resource('packets', PacketController::class)->names('packets');
        Route::put('/packets/{packet:slug}/disabled/true', [PacketController::class, 'disable'])->name('disable.package');
        Route::put('/packets/{packet:slug}/disabled/false', [PacketController::class, 'enable'])->name('enable.package');
        Route::get('/packets/{packet:slug}/delete', [PacketController::class, 'delete'])->name('paket.delete');
        //=================================Package Categories============================================
        Route::resource('package-categories', PaketCategoriesController::class)->names('categories');
        Route::put('/package-categories/{package_category:slug}/deactivate', [PaketCategoriesController::class, 'deactivate'])->name('deactivate.package.categories');
        Route::put('/package-categories/{package_category:slug}/activate', [PaketCategoriesController::class, 'activate'])->name('activate.package.categories');
        //=================================CustomerSubscription=============================================
        Route::resource('customer-subscriptions', CustomerSubscriptionController::class)->names('customerSubscriptions');
        //=================================SubscriptionDuration=============================================
        Route::get('subscription/durations/manage/{packet:slug}', [SubscriptionDurationController::class, 'manage'])->name('subscriptionDurations.manage');
        Route::resource('subscription/durations', SubscriptionDurationController::class)->names('subscriptionDurations');
        //========================ROUTE Restore Customer======================
        Route::get('/customers/trashed', [CustomerController::class, 'trashed'])->name('customers.trashed');
        Route::put('/customers/{customer:slug}/restore', [CustomerController::class, 'restore'])->name('customers.restore');
        Route::delete('/customers/{customer:slug}/force-delete', [CustomerController::class, 'forceDelete'])->name('customers.forceDelete');
        //========================ROUTE CUSTOMER===============================
        Route::resource('customers', CustomerController::class);
        Route::get('/customers/search', [PaymentController::class, 'index'])->name('customers.search');
        Route::put('/customers/{customer:slug}/disabled/true', [CustomerController::class, 'disable'])->name('customers.disable');
        Route::put('/customers/{customer:slug}/disabled/false', [CustomerController::class, 'enable'])->name('customers.enable');
        Route::put('/customers/{customer:slug}/force-disabled/true', [CustomerController::class, 'forceDisable'])->name('customers.forceDisable');
        Route::put('/customers/{customer:slug}/force-disabled/false', [CustomerController::class, 'forceEnable'])->name('customers.forceEnable');
        Route::get('/customers/{status}/show', [CustomerController::class, 'showStatusCustomer'])->name('customers.show.status');
        Route::get('/customers/{customer:slug}/delete', [CustomerController::class, 'delete'])->name('customers.delete');
        Route::get('/customers/{customer:slug}/edit/ppp', [CustomerController::class, 'editPpp'])->name('customers.edit.ppp');
        Route::patch('/customers/{customer:slug}/ppp/update', [CustomerController::class, 'updatePpp'])->name('customers.update.ppp');
        Route::get('/customers/{customer:slug}/router/edit', [CustomerController::class, 'editRouter'])->name('customers.edit.router');
        Route::patch('/customers/{customer:slug}/router/update', [CustomerController::class, 'updateRouter'])->name('customers.update.router');
        Route::put('/customers/{customer:slug}/activation', [CustomerController::class, 'activation'])->name('customers.activation');
        Route::put('/customers/{customer:slug}/export/secret', [CustomerController::class, 'exportSecret'])->name('customers.pppoe.export');
        Route::get('/customers/file/export', [CustomerController::class, 'export'])->name('customers.export');
        //========================ROUTE Member================================
        Route::resource('members', MemberController::class);
        Route::get('/members/{member:slug}/delete', [MemberController::class, 'delete'])->name('members.delete');
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
        Route::get('/receipt/export', [ReceiptController::class, 'export'])->name('receipts.export');
        //Route::get('/billings/state/{status}/show', [BillingController::class, 'index'])->name('billings.show.state');
        Route::get('billings/create/one', [BillingController::class, 'create_one'])->name('billings.create.one');
        Route::post('billings/store/one', [BillingController::class, 'store_one'])->name('billings.store.one');
        Route::patch('billings/{billing:slug}/payment', [BillingController::class, 'payment'])->name('billings.payment');
        Route::get('billings/{billing:slug}/paylater', [BillingController::class, 'paylater'])->name('billings.paylater');
        Route::get('billings/{billing:slug}/transfer', [BillingController::class, 'transfer'])->name('billings.transfer');
        Route::patch('billings/{billing:slug}/post_paylater', [BillingController::class, 'post_paylater'])->name('billings.postPaylater');
        Route::patch('billings/{billing:slug}/unpayment', [BillingController::class, 'unpayment'])->name('billings.unpayment');
        Route::post('billings/auto-generate', [BillingController::class, 'billing_auto_generate'])->name('billings.auto-generate');
        Route::get('/server/{mikrotik:slug}/time', [SystemController::class, 'getTimeRouter'])->name('mikrotik.time');
        Route::get('/server/{mikrotik:slug}/ppp/secret/get-all-ppp-secrets', [SecretController::class, 'getCountPppSecrets'])->name('get.ppp.secrets');
        Route::get('/server/{mikrotik:slug}/ppp/secret/get-all-ppp-secrets-active', [SecretController::class, 'getCountPppSecretsActive'])->name('get.ppp.active');
        Route::get('/server/{mikrotik:slug}/ppp/secret/get-active-byName', [CustomerController::class, 'getPppSecretsActiveByName'])->name('get.ppp.active.byName');
        Route::get('/server/traffic/{mikrotik:slug}/get-monitoring', [SystemController::class, 'mikrotikTrafficInterface'])->name('mikrotik.traffic_interface');

        Route::get('settings/autoisolirs/{autoisolir:id}/nat', [AutoisolirController::class, 'edit_nat'])->name('autoisolir.nat.setting');
        Route::patch('settings/autoisolirs/{autoisolir:id}/nat/update', [AutoisolirController::class, 'update_nat'])->name('autoisolir.updateNat.setting');
        Route::patch('settings/customers/activation', [CustomerController::class, 'activation_all_customer'])->name('customers.all.activation');
        Route::get('settings/exports', [ExportController::class, 'index'])->name('export.index');
        Route::post('settings/exports', [ExportController::class, 'exportToRouter'])->name('export.post');
        Route::resource('settings/roles', RoleController::class);
        Route::resource('settings/users', UserController::class);
        Route::get('settings/users{user:id}/delete', [UserController::class, 'delete'])->name('users.delete');
        Route::put('settings/users/{user:id}/disabled/true', [UserController::class, 'disable'])->name('user.disable');
        Route::put('settings/users/{user:id}/disabled/false', [UserController::class, 'enable'])->name('user.enable');
        Route::get('settings/servers/preview', [ServerController::class, 'preview'])->name('servers.preview');
        Route::get('settings/servers/mikrotik/{mikrotik:slug}/preview', [ServerController::class, 'showDataCustomer'])->name('servers.preview.data');
        Route::post('settings/servers/import/{mikrotik:slug}', [ServerController::class, 'importCustomer'])->name('servers.import.customer');
        Route::resource('settings/servers', ServerController::class);
        Route::resource('settings/routers', RouterController::class);
        Route::get('settings/routers/{router:id}/delete', [RouterController::class, 'delete'])->name('router.delete');
        Route::resource('settings/router/brand', BrandController::class);
        Route::get('settings/router/brand/{brand:id}/delete', [BrandController::class, 'delete'])->name('brand.delete');

        //=================================Billing History=============================================
        Route::resource('billing-history', BillingHistoryController::class)->names('billingHistory');

        //Payment Gateway
        Route::get('settings/payment-gateway', [PaymentGatewayController::class, 'index'])->name('paymentgateway.index');
        Route::get('settings/payment-gateway/{paymentGateway:slug}', [PaymentGatewayController::class, 'edit'])->name('paymentgateway.edit');
        Route::get('settings/payment-gateway/1/{midtran:slug}', [MidtranController::class, 'edit'])->name('paymentgateway.midtran.edit');
        Route::patch('settings/payment-gateway/1/{midtran:slug}/update', [MidtranController::class, 'update'])->name('paymentgateway.midtran.update');
        Route::get('settings/payment-gateway/2/{tripay:slug}', [TripayController::class, 'edit'])->name('paymentgateway.tripay.edit');
        Route::patch('settings/payment-gateway/2/{tripay:slug}/update', [TripayController::class, 'update'])->name('paymentgateway.tripay.update');

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

        Route::resource('settings/autoisolir', AutoIsolirController::class);
        //   Route::get('/settings/autoisolir/create/ip-static', [AutoIsolirController::class, 'create_ip_static_isolir'])->name('autoisolir.create_ipstatic');
        //  Route::post('/settings/autoisolir/store-ip-static', [AutoIsolirController::class, 'store_ipstatic'])->name('autoisolir.store_ipstatic');
        Route::put('/autoisolir/{autoisolir:id}/disabled/true', [AutoIsolirController::class, 'disable'])->name('autoisolir.disable');
        Route::put('/autoisolir/{autoisolir:id}/disabled/false', [AutoIsolirController::class, 'enable'])->name('autoisolir.enable');
        Route::put('/autoisolir/{autoisolir:id}/force-disabled', [AutoIsolirController::class, 'force_disable'])->name('autoisolir.force_disable');

        //Whatsapp Gateway
        Route::get('settings/whatsappgateway', [WhatsappGatewayController::class, 'index'])->name('whatsappgateway.index');
        Route::get('settings/whatsappgateway/{whatsappgateway}/edit_number', [WhatsappGatewayController::class, 'edit_number'])->name('whatsappgateway.edit_number');
        Route::get('settings/whatsappgateway/{whatsappgateway}/edit_gateway', [WhatsappGatewayController::class, 'edit_gateway'])->name('whatsappgateway.edit_gateway');
        Route::patch('settings/whatsappgateway/{whatsappgateway}/test_gateway', [WhatsappGatewayController::class, 'test_gateway'])->name('whatsappgateway.test_gateway');
        Route::patch('settings/whatsappgateway/{whatsappgateway}/update_number', [WhatsappGatewayController::class, 'update_number'])->name('whatsappgateway.update_number');
        Route::patch('settings/whatsappgateway/{whatsappgateway}/update_gateway', [WhatsappGatewayController::class, 'update_gateway'])->name('whatsappgateway.update_gateway');
        Route::resource('settings/whatsappnumber', WhatsappNumberController::class);
        Route::put('settings/whatsappnumber/{whatsappnumber}/disabled', [WhatsappNumberController::class, 'disable_number'])->name('whatsappnumber.disable');
        Route::get('settings/whatsappnumber/{whatsappnumber}/delete', [WhatsappNumberController::class, 'delete'])->name('whatsappnumber.delete');
        Route::resource('settings/whatsappmessage', WhatsappMessageController::class);
        Route::put('settings/whatsappmessage/{whatsappmessage}/disabled', [WhatsappMessageController::class, 'disable_message'])->name('whatsappmessage.disable');
        Route::get('settings/whatsappmessage/{whatsappmessage}/delete', [WhatsappMessageController::class, 'delete'])->name('whatsappmessage.delete');
        Route::get('settings/whatsappgateway/trigger-job', [WhatsappGatewayController::class, 'dispatchJob'])->name('trigger-job');
        // Route::get('/test-job', function () {
        //     dispatch(new SendPaymentReminderJob(app(WhatsappMessageService::class)));
        //     return 'Job dispatched!';
        // })->name('test-job')->middleware('auth');

        //Telegram Gateway
        Route::get('settings/telegramgateway', [TelegramGatewayController::class, 'index'])->name('telegramgateway.index');
        Route::get('settings/telegramgateway/{telegramGateway}/edit_description', [TelegramGatewayController::class, 'edit_description'])->name('telegramgateway.edit_description');
        Route::get('settings/telegramgateway/{telegramGateway}/edit_gateway', [TelegramGatewayController::class, 'edit_gateway'])->name('telegramgateway.edit_gateway');
        Route::patch('settings/telegramgateway/{telegramGateway}/test_gateway', [TelegramGatewayController::class, 'test_gateway'])->name('telegramgateway.test_gateway');
        Route::patch('settings/telegramgateway/{telegramGateway}/update_description', [TelegramGatewayController::class, 'update_description'])->name('telegramgateway.update_description');
        Route::patch('settings/telegramgateway/{telegramGateway}/update_gateway', [TelegramGatewayController::class, 'update_gateway'])->name('telegramgateway.update_gateway');
        Route::get('settings/telegramchat/get_updates', [TelegramChatIdController::class, 'get_updates'])->name('telegramchatid.get_updates');
        Route::patch('settings/telegramchat/delete/{telegramchat}', [TelegramChatIdController::class, 'softDelete'])->name('telegramchatid.soft_delete');
        Route::patch('settings/telegramchat/{telegramchat}/update_telegram_data', [TelegramChatIdController::class, 'update_telegram_data'])->name('telegramchat.update_telegram_data');
        Route::resource('settings/telegramchat', TelegramChatIdController::class);
        // Route::put('settings/telegramchat/{telegramchat}/disabled', [TelegramChatIdController::class, 'disable_chat_id'])->name('telegramchatid.disable');
        Route::post('settings/telegramchat/{telegramchat}/test_message', [TelegramChatIdController::class, 'test_message'])->name('telegramchatid.test_message');
        Route::resource('settings/telegrammessage', TelegramMessageController::class);
        Route::put('settings/telegrammessage/{telegrammessage}/disabled', [TelegramMessageController::class, 'disable_message'])->name('telegrammessage.disable');
        Route::get('settings/telegrammessage/{telegrammessage}/delete', [TelegramMessageController::class, 'delete'])->name('telegrammessage.delete');
        Route::get('settings/telegramgateway/trigger-job', [TelegramGatewayController::class, 'dispatchJob'])->name('trigger-job');

        Route::get('/test-email', function () {
            Mail::raw('Ini email test dari Laravel', function ($message) {
                $message->to('dikanurfaizi.rn@gmail.com')
                    ->subject('Test Email SMTP Google');
            });
            return 'Email terkirim!';
        });
    });
});

Route::group(['middleware' => ['auth']], function () {
    Route::post('api/fetch-pakets', [DropdownController::class, 'fetchPaket'])->name('fetch.paket');
    Route::post('api/fetch-profiles', [DropdownController::class, 'fetchProfile']);
    // Route::post('api/fetch-addresslist', [DropdownController::class, 'fetchAddressList']);
    Route::post('api/fetch-parent-queues', [DropdownController::class, 'fetchParentQueue']);
    Route::post('api/fetch-queue-types', [DropdownController::class, 'fetchQueueType']);
    Route::post('api/fetch-scripts', [DropdownController::class, 'fetchScript']);
    Route::post('api/fetch-schedules', [DropdownController::class, 'fetchSchedule']);
    Route::post('api/fetch-type-routers', [DropdownController::class, 'fetchTypeRouter']);
    Route::post('api/fetch-account-bank', [DropdownController::class, 'fetchAccountBank'])->name('fetch.account.bank');
    Route::post('api/fetch-interfaces', [DropdownController::class, 'fetchInterface'])->name('fetch.interface');
    Route::post('api/fetch-monthly-summary', [HomeController::class, 'getBillingMonthly'])->name('monthly.summary');
    Route::post('api/customer-registration-churn-data', [HomeController::class, 'getCustomerRegistrationChurnData'])->name('customer.registration');
    // Route::post('api/send-whatsapp', [WhatsAppGatewayController::class, 'sendMessage']);
    // Route::post('api/send-broadcast', [WhatsAppGatewayController::class, 'sendBroadcastMessage']);
});
