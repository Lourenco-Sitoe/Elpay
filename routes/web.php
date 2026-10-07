<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\BumpController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CourseContentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KYCController;
use App\Http\Controllers\LearnController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PayoutAccountController;
use App\Http\Controllers\PixelController;
use App\Http\Controllers\ProducerAffiliateController;
use App\Http\Controllers\ProducerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductWizardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\UpsellController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WebhookController;
use App\Models\Affiliate;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ---------- Públicas ----------
Route::get('/', function () {
    try {
        $products = class_exists(Product::class)
            ? Product::where('status', 'approved')->latest()->take(12)->get()
            : collect();
    } catch (Throwable) {
        $products = collect();
    }

    return view('welcome', compact('products'));
})->name('home');

Route::get('/p/{slug}', function ($slug, Request $request) {
    $product = Product::where('slug', $slug)->firstOrFail();
    abort_if(($product->status ?? 'approved') !== 'approved', 404);
    // Tracking de clique do afiliado (só afiliados aprovados contam).
    if ($request->filled('ref')) {
        try {
            Affiliate::where('product_id', $product->id)
                ->where('affiliate_code', $request->get('ref'))
                ->where('status', 'approved')->increment('clicks');
        } catch (Throwable) {
        }
    }
    $response = response()->view('products.show', compact('product'));
    if ($request->filled('ref')) {
        $response->withCookie(cookie('aff_'.$product->id, $request->get('ref'), 60 * 24 * 30));
    }

    return $response;
})->name('product.public');

Route::get('/c/{code}', [CertificateController::class, 'verify'])->name('certificates.verify');

// App mobile (Framework7 PWA — consome a API REST)
Route::get('/app', fn () => view('app'))->name('app.mobile');

Route::get('/checkout/{slug}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/success/{sale}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::post('/checkout/upsell/{sale}', [CheckoutController::class, 'applyUpsell'])->name('checkout.upsell.accept');
Route::post('/checkout/upsell/{sale}/decline', [CheckoutController::class, 'declineUpsell'])->name('checkout.upsell.decline');
Route::post('/checkout/downsell/{sale}', [CheckoutController::class, 'applyDownsell'])->name('checkout.downsell.accept');
Route::post('/checkout/coupon-validate', [CheckoutController::class, 'validateCoupon'])->name('checkout.coupon.validate');
Route::post('/checkout/set-password', [CheckoutController::class, 'setPassword'])->name('checkout.set-password');

// ---------- Autenticadas (todas: active) ----------
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/producer', [DashboardController::class, 'producerDashboard'])->name('producer.dashboard');
    Route::get('/dashboard/affiliate', [DashboardController::class, 'affiliateDashboard'])->name('affiliate.dashboard');
    Route::get('/dashboard/client', [DashboardController::class, 'clientDashboard'])->name('client.dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');

    // KYC (sem middleware kyc para não haver lock-out)
    Route::get('/kyc', [KYCController::class, 'index'])->name('kyc.index');
    Route::get('/kyc/create', [KYCController::class, 'create'])->name('kyc.create');
    Route::post('/kyc', [KYCController::class, 'store'])->name('kyc.store');

    // Notificações
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Suporte
    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::post('/support', [SupportController::class, 'store'])->name('support.store');
    Route::get('/support/{message}', [SupportController::class, 'show'])->name('support.show');

    // Carteira (produtor + afiliado; todo o crédito vive em users.balance)
    Route::get('/wallet', [WalletController::class, 'index'])->middleware('affiliate')->name('wallet.index');
    Route::get('/wallet/request', [WalletController::class, 'requestWithdrawal'])->middleware('affiliate')->name('wallet.request');
    Route::post('/wallet/request', [WalletController::class, 'storeWithdrawal'])->middleware('affiliate')->name('wallet.store');

    // Área de estudo (cursos) + certificados
    Route::get('/learn/{product}', [LearnController::class, 'course'])->name('learn.course');
    Route::get('/learn/lesson/{lesson}', [LearnController::class, 'lesson'])->name('learn.lesson');
    Route::post('/learn/lesson/{lesson}/complete', [LearnController::class, 'complete'])->name('learn.complete');
    Route::get('/certificates', [CertificateController::class, 'mine'])->name('certificates.mine');

    // Contas de saque guardadas
    Route::get('/payouts/accounts', [PayoutAccountController::class, 'index'])->middleware('affiliate')->name('payouts.accounts');
    Route::post('/payouts/accounts', [PayoutAccountController::class, 'store'])->middleware('affiliate')->name('payouts.accounts.store');
    Route::match(['put', 'patch'], '/payouts/accounts/{account}', [PayoutAccountController::class, 'update'])->middleware('affiliate')->name('payouts.accounts.update');
    Route::delete('/payouts/accounts/{account}', [PayoutAccountController::class, 'destroy'])->middleware('affiliate')->name('payouts.accounts.destroy');

    // Ganhos de parceria
    Route::get('/partners/earnings', [PartnerController::class, 'earnings'])->name('partners.earnings');

    // Afiliado
    Route::get('/affiliate/marketplace', [AffiliateController::class, 'marketplace'])->name('affiliate.marketplace');
    Route::post('/affiliate/join/{product}', [AffiliateController::class, 'join'])->name('affiliate.join');
    Route::get('/affiliate/my-links', [AffiliateController::class, 'myLinks'])->name('affiliate.links');
    Route::get('/affiliate/commissions', [AffiliateController::class, 'commissions'])->name('affiliate.commissions');
    Route::get('/affiliate/materials', [MaterialController::class, 'affiliateView'])->name('affiliate.materials');
    Route::post('/quizzes/{quiz}/answer', [QuizController::class, 'answer'])->name('quizzes.answer');
});

// ---------- Produtor (+ KYC) ----------
Route::middleware(['auth', 'active', 'kyc', 'producer'])->group(function () {
    Route::post('/products/quick', [ProductWizardController::class, 'quickStore'])->name('products.quick-store');
    Route::get('/products/{product}/wizard/{section?}', [ProductWizardController::class, 'wizard'])->name('products.wizard');
    Route::match(['put', 'patch'], '/products/{product}/wizard/{section}', [ProductWizardController::class, 'updateSection'])->name('products.wizard.update');
    Route::post('/products/{product}/submit', [ProductWizardController::class, 'submitForReview'])->name('products.submit');
    Route::resource('products', ProductController::class);

    Route::get('/coupons', [CouponController::class, 'index'])->name('coupons.index');
    Route::post('/coupons', [CouponController::class, 'store'])->name('coupons.store');
    Route::match(['put', 'patch'], '/coupons/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
    Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy'])->name('coupons.destroy');
    Route::post('/coupons/{coupon}/toggle', [CouponController::class, 'toggle'])->name('coupons.toggle');

    Route::get('/bumps', [BumpController::class, 'index'])->name('bumps.index');
    Route::get('/bumps/create', [BumpController::class, 'create'])->name('bumps.create');
    Route::post('/bumps', [BumpController::class, 'store'])->name('bumps.store');
    Route::get('/bumps/{bump}/edit', [BumpController::class, 'edit'])->name('bumps.edit');
    Route::match(['put', 'patch'], '/bumps/{bump}', [BumpController::class, 'update'])->name('bumps.update');
    Route::delete('/bumps/{bump}', [BumpController::class, 'destroy'])->name('bumps.destroy');

    Route::get('/upsells', [UpsellController::class, 'index'])->name('upsells.index');
    Route::get('/upsells/create', [UpsellController::class, 'create'])->name('upsells.create');
    Route::post('/upsells', [UpsellController::class, 'store'])->name('upsells.store');
    Route::get('/upsells/{upsell}/edit', [UpsellController::class, 'edit'])->name('upsells.edit');
    Route::match(['put', 'patch'], '/upsells/{upsell}', [UpsellController::class, 'update'])->name('upsells.update');
    Route::delete('/upsells/{upsell}', [UpsellController::class, 'destroy'])->name('upsells.destroy');

    // Ferramentas (hub estilo Tutora)
    Route::get('/tools', [ToolsController::class, 'index'])->name('tools.index');

    Route::get('/tools/pixels', [PixelController::class, 'index'])->name('pixels.index');
    Route::post('/tools/pixels', [PixelController::class, 'store'])->name('pixels.store');
    Route::match(['put', 'patch'], '/tools/pixels/{pixel}', [PixelController::class, 'update'])->name('pixels.update');
    Route::delete('/tools/pixels/{pixel}', [PixelController::class, 'destroy'])->name('pixels.destroy');
    Route::post('/tools/pixels/{pixel}/toggle', [PixelController::class, 'toggle'])->name('pixels.toggle');

    Route::get('/tools/webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
    Route::post('/tools/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
    Route::match(['put', 'patch'], '/tools/webhooks/{webhook}', [WebhookController::class, 'update'])->name('webhooks.update');
    Route::delete('/tools/webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');
    Route::post('/tools/webhooks/{webhook}/toggle', [WebhookController::class, 'toggle'])->name('webhooks.toggle');
    Route::post('/tools/webhooks/{webhook}/test', [WebhookController::class, 'test'])->name('webhooks.test');
    Route::get('/tools/webhooks/{webhook}/deliveries', [WebhookController::class, 'deliveries'])->name('webhooks.deliveries');
    Route::post('/tools/deliveries/{delivery}/resend', [WebhookController::class, 'resend'])->name('webhooks.resend');

    // Parceiros / coprodução por produto
    Route::get('/products/{product}/partners', [PartnerController::class, 'index'])->name('partners.index');
    Route::post('/products/{product}/partners', [PartnerController::class, 'store'])->name('partners.store');
    Route::post('/partners/{partner}/approve', [PartnerController::class, 'approve'])->name('partners.approve');
    Route::post('/partners/{partner}/reject', [PartnerController::class, 'reject'])->name('partners.reject');
    Route::delete('/partners/{partner}', [PartnerController::class, 'destroy'])->name('partners.destroy');

    // Conteúdo de cursos (módulos + aulas)
    Route::get('/products/{product}/content', [CourseContentController::class, 'index'])->name('content.index');
    Route::post('/products/{product}/modules', [CourseContentController::class, 'storeModule'])->name('content.modules.store');
    Route::match(['put', 'patch'], '/content/modules/{module}', [CourseContentController::class, 'updateModule'])->name('content.modules.update');
    Route::delete('/content/modules/{module}', [CourseContentController::class, 'destroyModule'])->name('content.modules.destroy');
    Route::post('/content/modules/{module}/lessons', [CourseContentController::class, 'storeLesson'])->name('content.lessons.store');
    Route::match(['put', 'patch'], '/content/lessons/{lesson}', [CourseContentController::class, 'updateLesson'])->name('content.lessons.update');
    Route::delete('/content/lessons/{lesson}', [CourseContentController::class, 'destroyLesson'])->name('content.lessons.destroy');

    // Vendas e clientes do produtor (só dados próprios).
    Route::get('/producer/sales', [ProducerController::class, 'sales'])->name('producer.sales');
    Route::get('/producer/customers', [ProducerController::class, 'customers'])->name('producer.customers');
    Route::get('/producer/customers/{userId}', [ProducerController::class, 'customerHistory'])->name('producer.customers.history');
    Route::post('/producer/access/grant', [ProducerController::class, 'grantAccess'])->name('producer.access.grant');
    Route::delete('/producer/access/{access}', [ProducerController::class, 'revokeAccess'])->name('producer.access.revoke');

    // Afiliados do produtor: aprovar/rejeitar, comissão, modo de afiliação.
    Route::get('/producer/affiliates', [ProducerAffiliateController::class, 'index'])->name('producer.affiliates');
    Route::post('/producer/affiliates/{affiliate}/approve', [ProducerAffiliateController::class, 'approve'])->name('producer.affiliates.approve');
    Route::post('/producer/affiliates/{affiliate}/reject', [ProducerAffiliateController::class, 'reject'])->name('producer.affiliates.reject');
    Route::post('/producer/affiliates/{affiliate}/unblock', [ProducerAffiliateController::class, 'unblock'])->name('producer.affiliates.unblock');
    Route::match(['put', 'patch'], '/producer/affiliates/{affiliate}/rate', [ProducerAffiliateController::class, 'updateRate'])->name('producer.affiliates.rate');
    Route::match(['put', 'patch'], '/producer/products/{product}/affiliation', [ProducerAffiliateController::class, 'updateMode'])->name('producer.affiliation.mode');

    // Materiais de divulgação por produto.
    Route::get('/products/{product}/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::post('/products/{product}/materials', [MaterialController::class, 'store'])->name('materials.store');
    Route::match(['put', 'patch'], '/materials/{material}', [MaterialController::class, 'update'])->name('materials.update');
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');

    // Quizzes das aulas.
    Route::get('/lessons/{lesson}/quizzes', [QuizController::class, 'index'])->name('quizzes.index');
    Route::post('/lessons/{lesson}/quizzes', [QuizController::class, 'store'])->name('quizzes.store');
    Route::delete('/quizzes/{quiz}', [QuizController::class, 'destroy'])->name('quizzes.destroy');
});

// ---------- Admin ----------
Route::middleware(['auth', 'active', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/create', [AdminController::class, 'userCreate'])->name('users.create');
    Route::post('/users', [AdminController::class, 'userStore'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'userEdit'])->name('users.edit');
    Route::match(['put', 'patch'], '/users/{user}', [AdminController::class, 'userUpdate'])->name('users.update');
    Route::patch('/users/{user}/status', [AdminController::class, 'userUpdateStatus'])->name('users.status');
    Route::delete('/users/{user}', [AdminController::class, 'userDestroy'])->name('users.destroy');

    Route::get('/products', [AdminController::class, 'products'])->name('products');
    Route::get('/products/{product}/edit', [AdminController::class, 'productEdit'])->name('products.edit');
    Route::get('/products/{product}', [AdminController::class, 'productShow'])->name('products.show');
    Route::match(['put', 'patch'], '/products/{product}', [AdminController::class, 'productUpdate'])->name('products.update');
    Route::delete('/products/{product}', [AdminController::class, 'productDestroy'])->name('products.destroy');
    Route::post('/products/{product}/approve', [AdminController::class, 'productApprove'])->name('products.approve');
    Route::post('/products/{product}/reject', [AdminController::class, 'productReject'])->name('products.reject');
    Route::post('/products/{product}/suspend', [AdminController::class, 'productSuspend'])->name('products.suspend');
    Route::get('/courses', [AdminController::class, 'courses'])->name('courses');

    Route::get('/sales', [AdminController::class, 'sales'])->name('sales');
    Route::post('/sales/{sale}/cancel', [AdminController::class, 'saleCancel'])->name('sales.cancel');
    Route::post('/sales/{sale}/refund', [AdminController::class, 'saleRefund'])->name('sales.refund');

    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
    Route::post('/payments/{payment}/approve', [AdminController::class, 'paymentApprove'])->name('payments.approve');
    Route::post('/payments/{payment}/reject', [AdminController::class, 'paymentReject'])->name('payments.reject');

    Route::get('/commissions', [AdminController::class, 'commissions'])->name('commissions');
    Route::post('/commissions/{commission}/paid', [AdminController::class, 'commissionMarkPaid'])->name('commissions.paid');
    Route::post('/partner-earnings/{earning}/paid', [PartnerController::class, 'markPaid'])->name('partner-earnings.paid');
    Route::get('/affiliates', [AdminController::class, 'affiliatesAdmin'])->name('affiliates');
    Route::post('/affiliates/{affiliate}/approve', [AdminController::class, 'affiliateApprove'])->name('affiliates.approve');
    Route::post('/affiliates/{affiliate}/block', [AdminController::class, 'affiliateBlock'])->name('affiliates.block');

    Route::get('/withdrawals', [AdminController::class, 'withdrawalsAdmin'])->name('withdrawals');
    Route::post('/withdrawals/{withdrawal}/approve', [AdminController::class, 'withdrawalApprove'])->name('withdrawals.approve');
    Route::post('/withdrawals/{withdrawal}/reject', [AdminController::class, 'withdrawalReject'])->name('withdrawals.reject');

    Route::get('/kyc', [AdminController::class, 'kycAdmin'])->name('kyc');
    Route::post('/kyc/{id}/approve', [KYCController::class, 'adminApprove'])->name('kyc.approve');
    Route::post('/kyc/{id}/reject', [KYCController::class, 'adminReject'])->name('kyc.reject');
    Route::post('/kyc/{id}/comment', [KYCController::class, 'adminComment'])->name('kyc.comment');

    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');

    Route::get('/communication', [AdminController::class, 'communication'])->name('communication');
    Route::post('/communication', [AdminController::class, 'sendMass'])->name('communication.send');

    Route::get('/messages', [AdminController::class, 'messages'])->name('messages');
    Route::post('/messages/{message}/reply', [AdminController::class, 'reply'])->name('messages.reply');

    Route::get('/logs', [AdminController::class, 'logs'])->name('logs');

    Route::get('/banners', [BannerController::class, 'index'])->name('banners');
    Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
    Route::match(['put', 'patch'], '/banners/{banner}', [BannerController::class, 'update'])->name('banners.update');
    Route::post('/banners/{banner}/toggle', [BannerController::class, 'toggle'])->name('banners.toggle');
    Route::delete('/banners/{banner}', [BannerController::class, 'destroy'])->name('banners.destroy');

    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'settingsUpdate'])->name('settings.update');
});

require __DIR__.'/auth.php';
