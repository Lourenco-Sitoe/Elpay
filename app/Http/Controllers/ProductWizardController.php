<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Product;
use App\Models\ProductPartner;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductWizardController extends Controller
{
    use LogsActivity;

    public const SECTIONS = [
        'overview' => 'Visão geral',
        'basic' => 'Informações básicas',
        'pricing' => 'Preços e ofertas',
        'partners' => 'Parceiros (coprodução)',
        'affiliates' => 'Programa de afiliados',
        'links' => 'Links de divulgação',
        'support' => 'Suporte e tracking',
        'materials' => 'Materiais',
    ];

    public const CATEGORIES = [
        'Animais e Plantas',
        'Apps & Software',
        'Casa e Construção',
        'Culinária e Gastronomia',
        'Desenvolvimento Pessoal',
        'Direito',
        'Ecologia e Meio Ambiente',
        'Educacional',
        'Empreendedorismo Digital',
        'Entretenimento',
        'Finanças e Investimentos',
        'Hobbies',
        'Idiomas',
        'Literatura',
        'Moda e Beleza',
        'Música e Artes',
        'Negócios e Carreira',
        'Outros',
        'Relacionamentos',
        'Saúde e Esportes',
        'Sexualidade',
        'Tecnologia da Informação',
    ];

    public const FORMATS = [
        'course' => 'Curso (aulas em vídeo)',
        'ebook' => 'E-book (PDF ou ficheiro)',
        'app' => 'App (aplicação ou software)',
        'file' => 'Ficheiro digital',
        'link' => 'Link externo',
    ];

    public const PRICE_MIN = ['MZN' => 100, 'USD' => 5];

    public const STATUS_LABELS = [
        'draft' => 'Rascunho',
        'pending' => 'Em análise',
        'approved' => 'Ativo',
        'rejected' => 'Recusado',
        'suspended' => 'Suspenso',
        'banned' => 'Banido',
    ];

    protected function authorizeProduct(Product $product): void
    {
        $user = auth()->user();
        if (($user->type ?? '') === 'admin') {
            return;
        }
        abort_unless((int) $product->producer_id === (int) $user->id, 403);
    }

    /** Criação rápida via modal (nome, categoria, formato, moeda, preço). */
    public function quickStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'type' => 'required|in:course,ebook,file,link,app',
            'currency' => 'required|in:MZN,USD',
            'price' => 'required|numeric|min:1',
        ]);
        $min = self::PRICE_MIN[$data['currency']] ?? 1;
        if ((float) $data['price'] < $min) {
            return back()->withErrors(['price' => "Mínimo de {$data['currency']} ".number_format($min, 2).'.'])->withInput();
        }

        $product = Product::create([
            'producer_id' => auth()->id(),
            'title' => $data['title'],
            'description' => '',
            'price' => $data['price'],
            'currency' => $data['currency'],
            'type' => $data['type'],
            'category' => $data['category'],
            'commission_rate' => (float) setting('default_commission_rate', 10),
            'affiliation_mode' => 'automatic',
            'payment_methods' => ['mpesa', 'emola'],
            'slug' => 'tmp-'.Str::random(8),
            'status' => 'draft',
        ]);
        $product->slug = Str::slug($data['title']).'-'.$product->id;
        $product->save();

        $this->logActivity('product.created', 'Produto criado: '.$product->title);

        return redirect()->route('products.wizard', [$product, 'overview'])
            ->with('success', 'Produto cadastrado com sucesso');
    }

    public function wizard(Product $product, string $section = 'overview'): View
    {
        $this->authorizeProduct($product);
        abort_unless(array_key_exists($section, self::SECTIONS), 404);
        $product->load(['modules.lessons', 'partners', 'affiliates', 'coupons']);

        $done = $this->completion($product);
        $total = count(self::SECTIONS);
        $count = count(array_filter($done));
        $progress = (int) round(($count / $total) * 100);

        return view('products.wizard', [
            'product' => $product,
            'section' => $section,
            'sections' => self::SECTIONS,
            'done' => $done,
            'progress' => $progress,
            'doneCount' => $count,
            'totalCount' => $total,
            'categories' => self::CATEGORIES,
            'formats' => self::FORMATS,
        ]);
    }

    public function updateSection(Request $request, Product $product, string $section): RedirectResponse
    {
        $this->authorizeProduct($product);
        abort_unless(array_key_exists($section, self::SECTIONS), 404);

        match ($section) {
            'basic' => $this->saveBasic($request, $product),
            'pricing' => $this->savePricing($request, $product),
            'affiliates' => $this->saveAffiliates($request, $product),
            'support' => $this->saveSupport($request, $product),
            'materials' => $this->saveMaterials($request, $product),
            default => null,
        };

        $next = $request->get('next', $section);

        return redirect()->route('products.wizard', [$product, $next])
            ->with('success', 'Guardado com sucesso');
    }

    /** Envia o rascunho para avaliação do Admin (draft -> pending). */
    public function submitForReview(Product $product): RedirectResponse
    {
        $this->authorizeProduct($product);
        abort_unless(in_array($product->status, ['draft', 'rejected'], true), 403, 'Só rascunhos podem ser enviados para avaliação.');
        $done = self::completion($product);
        $missing = array_keys(array_filter($done, fn ($v) => ! $v));
        $missing = array_diff($missing, ['overview', 'partners']);
        if ($missing) {
            return back()->withErrors(['product' => 'Completa as secções obrigatórias antes de enviar para avaliação.']);
        }
        $product->status = 'pending';
        $product->rejection_reason = null;
        $product->save();
        $this->logActivity('product.submitted', 'Produto enviado para avaliação: '.$product->title);

        return redirect()->route('products.wizard', [$product, 'overview'])
            ->with('success', 'Produto enviado para avaliação.');
    }

    protected function saveBasic(Request $request, Product $product): void
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'type' => 'required|in:course,ebook,file,link,app',
            'delivery' => 'required|in:internal,external',
            'external_url' => 'nullable|url|max:2048',
            'cover' => ['nullable', 'image', 'max:'.config('uploads.max_image_kb')],
        ]);
        if ($request->hasFile('cover')) {
            $product->cover = $request->file('cover')->store('products', 'public');
        }
        $product->fill([
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'category' => $data['category'],
            'type' => $data['type'],
            'delivery' => $data['delivery'],
            'external_url' => $data['delivery'] === 'external' ? ($data['external_url'] ?? null) : $product->external_url,
        ]);
        $product->slug = Str::slug($data['title']).'-'.$product->id;
        $product->save();
        $this->logActivity('product.updated', 'Informações básicas: '.$product->title);
    }

    protected function savePricing(Request $request, Product $product): void
    {
        $data = $request->validate([
            'currency' => 'required|in:MZN,USD',
            'price' => 'required|numeric|min:1',
            'warranty_days' => 'required|integer|min:0|max:365',
            'payment_methods' => 'nullable|array',
            'payment_methods.*' => 'in:mpesa,emola,visa',
        ]);
        $min = self::PRICE_MIN[$data['currency']] ?? 1;
        if ((float) $data['price'] < $min) {
            abort(redirect()->back()->withErrors(['price' => "O mínimo nesta moeda é {$data['currency']} ".number_format($min, 2).'.'])->withInput());
        }
        $product->fill([
            'currency' => $data['currency'],
            'price' => $data['price'],
            'warranty_days' => $data['warranty_days'],
            'payment_methods' => array_values($data['payment_methods'] ?? ['mpesa', 'emola']),
        ]);
        $product->save();
        $this->logActivity('product.updated', 'Preço e moeda: '.$product->title);
    }

    protected function saveAffiliates(Request $request, Product $product): void
    {
        $data = $request->validate([
            'affiliates_enabled' => 'nullable|boolean',
            'commission_rate' => 'nullable|numeric|min:0|max:90',
            'affiliation_mode' => 'required|in:automatic,manual',
            'affiliation_rules' => 'nullable|string|max:5000',
        ]);
        $enabled = $request->boolean('affiliates_enabled', true);
        $product->fill([
            'commission_rate' => $enabled ? ($data['commission_rate'] ?? $product->commission_rate ?? 10) : 0,
            'affiliation_mode' => $enabled ? $data['affiliation_mode'] : 'manual',
            'affiliation_rules' => $data['affiliation_rules'] ?? null,
        ]);
        $product->save();
        if (! $enabled) {
            Affiliate::where('product_id', $product->id)->where('status', 'pending')->update(['status' => 'blocked']);
        }
    }

    protected function saveSupport(Request $request, Product $product): void
    {
        $data = $request->validate([
            'support_email' => 'nullable|email|max:255',
            'support_phone' => 'nullable|string|max:30',
        ]);
        $product->fill($data);
        $product->save();
    }

    protected function saveMaterials(Request $request, Product $product): void
    {
        $data = $request->validate([
            'material_name' => 'nullable|string|max:255',
            'file' => ['nullable', 'file', 'max:'.config('uploads.max_file_kb')],
        ]);
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('products/materials', 'public');
            $product->file_path = $path;
            $product->save();
            $this->logActivity('product.material', 'Material carregado: '.($data['material_name'] ?? $request->file('file')->getClientOriginalName()));
        }
    }

    /** Estado de cada secção (para barra de progresso e vistos). */
    public static function completion(Product $product): array
    {
        $basic = filled($product->title) && filled($product->description) && filled($product->category);
        $min = self::PRICE_MIN[$product->currency ?? 'MZN'] ?? 1;
        $pricing = (float) ($product->price ?? 0) >= $min;
        $pendingPartners = 0;
        try {
            $pendingPartners = ProductPartner::where('product_id', $product->id)->where('status', 'pending')->count();
        } catch (\Throwable) {
        }
        $hasDelivery = filled($product->file_path) || filled($product->external_url);
        try {
            $hasDelivery = $hasDelivery || $product->modules()->exists();
        } catch (\Throwable) {
        }

        return [
            'overview' => true,
            'basic' => (bool) $basic,
            'pricing' => (bool) $pricing,
            'partners' => $pendingPartners === 0,
            'affiliates' => $product->commission_rate !== null,
            'links' => filled($product->slug),
            'support' => filled($product->support_email),
            'materials' => (bool) $hasDelivery,
        ];
    }
}
