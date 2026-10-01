<?php

namespace App\Http\Controllers;

use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Product;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseContentController extends Controller
{
    use LogsActivity;

    protected function myProduct(Product $product): Product
    {
        abort_unless(
            (auth()->user()->type ?? '') === 'admin' || (int) $product->producer_id === (int) auth()->id(),
            403
        );

        return $product;
    }

    protected function myModule(CourseModule $module): CourseModule
    {
        $this->myProduct($module->product);

        return $module;
    }

    public function index(Product $product): View
    {
        $this->myProduct($product);
        $product->load(['modules.lessons']);

        return view('content.index', compact('product'));
    }

    public function storeModule(Request $request, Product $product): RedirectResponse
    {
        $this->myProduct($product);
        $data = $request->validate(['title' => 'required|string|max:255']);
        $position = (int) CourseModule::where('product_id', $product->id)->max('position') + 1;
        $product->modules()->create(['title' => $data['title'], 'position' => $position]);
        $this->logActivity('course.module.created', "Módulo criado em {$product->title}");

        return back()->with('success', 'Módulo criado.');
    }

    public function updateModule(Request $request, CourseModule $module): RedirectResponse
    {
        $this->myModule($module);
        $data = $request->validate(['title' => 'required|string|max:255']);
        $module->update($data);

        return back()->with('success', 'Módulo atualizado.');
    }

    public function destroyModule(CourseModule $module): RedirectResponse
    {
        $this->myModule($module);
        $module->delete();

        return back()->with('success', 'Módulo eliminado.');
    }

    public function storeLesson(Request $request, CourseModule $module): RedirectResponse
    {
        $this->myModule($module);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'kind' => 'required|in:video,text,file',
            'body' => 'nullable|string',
            'url' => 'nullable|url|max:2048',
            'file' => 'nullable|file|max:102400',
            'duration_minutes' => 'nullable|integer|min:1',
        ]);
        $path = $request->hasFile('file') ? $request->file('file')->store('lessons', 'public') : null;
        $position = (int) $module->lessons()->max('position') + 1;
        $module->lessons()->create([
            'title' => $data['title'],
            'kind' => $data['kind'],
            'body' => $data['body'] ?? null,
            'url' => $data['url'] ?? null,
            'file_path' => $path,
            'position' => $position,
            'duration_minutes' => $data['duration_minutes'] ?? null,
        ]);

        return back()->with('success', 'Aula criada.');
    }

    public function updateLesson(Request $request, CourseLesson $lesson): RedirectResponse
    {
        $this->myModule($lesson->module);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'kind' => 'required|in:video,text,file',
            'body' => 'nullable|string',
            'url' => 'nullable|url|max:2048',
            'file' => 'nullable|file|max:102400',
            'duration_minutes' => 'nullable|integer|min:1',
        ]);
        if ($request->hasFile('file')) {
            $lesson->file_path = $request->file('file')->store('lessons', 'public');
        }
        $lesson->fill([
            'title' => $data['title'],
            'kind' => $data['kind'],
            'body' => $data['body'] ?? null,
            'url' => $data['url'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? null,
        ])->save();

        return back()->with('success', 'Aula atualizada.');
    }

    public function destroyLesson(CourseLesson $lesson): RedirectResponse
    {
        $this->myModule($lesson->module);
        $lesson->delete();

        return back()->with('success', 'Aula eliminada.');
    }
}
