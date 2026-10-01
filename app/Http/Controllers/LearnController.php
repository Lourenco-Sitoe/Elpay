<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CourseLesson;
use App\Models\LessonProgress;
use App\Models\Product;
use App\Models\ProductAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LearnController extends Controller
{
    protected function canLearn(Product $product): void
    {
        $user = auth()->user();
        $owner = (int) $product->producer_id === (int) $user->id;
        $admin = ($user->type ?? '') === 'admin';
        $bought = ProductAccess::where('user_id', $user->id)->where('product_id', $product->id)->exists();
        abort_unless($owner || $admin || $bought, 403, 'Sem acesso a este conteúdo.');
    }

    protected function progressMap(Product $product): array
    {
        return LessonProgress::where('user_id', auth()->id())
            ->whereIn('lesson_id', function ($q) use ($product) {
                $q->select('course_lessons.id')
                    ->from('course_lessons')
                    ->join('course_modules', 'course_modules.id', '=', 'course_lessons.module_id')
                    ->where('course_modules.product_id', $product->id);
            })
            ->whereNotNull('completed_at')
            ->pluck('completed_at', 'lesson_id')
            ->toArray();
    }

    public function course(Product $product): View
    {
        $this->canLearn($product);
        $product->load(['modules.lessons']);
        $done = $this->progressMap($product);
        $total = $product->modules->flatMap->lessons->count();
        $finished = collect($product->modules->flatMap->lessons)->filter(fn ($l) => isset($done[$l->id]))->count();
        $certificate = Certificate::where('user_id', auth()->id())->where('product_id', $product->id)->first();

        return view('learn.course', compact('product', 'done', 'total', 'finished', 'certificate'));
    }

    public function lesson(CourseLesson $lesson): View
    {
        $product = $lesson->module->product;
        $this->canLearn($product);
        $product->load(['modules.lessons']);
        $done = $this->progressMap($product);
        $completed = isset($done[$lesson->id]);

        return view('learn.lesson', compact('product', 'lesson', 'done', 'completed'));
    }

    public function complete(CourseLesson $lesson): RedirectResponse
    {
        $product = $lesson->module->product;
        $this->canLearn($product);

        LessonProgress::updateOrCreate(
            ['user_id' => auth()->id(), 'lesson_id' => $lesson->id],
            ['completed_at' => now()]
        );

        $lessonIds = $product->modules()->with('lessons:id,module_id')->get()->flatMap->lessons->pluck('id');
        $doneCount = LessonProgress::where('user_id', auth()->id())
            ->whereIn('lesson_id', $lessonIds)
            ->whereNotNull('completed_at')
            ->count();

        if ($lessonIds->isNotEmpty() && $doneCount >= $lessonIds->count()) {
            $cert = Certificate::firstOrCreate(
                ['user_id' => auth()->id(), 'product_id' => $product->id],
                ['code' => 'ELPAY-' . strtoupper(Str::random(8)), 'issued_at' => now()]
            );

            return redirect()->route('learn.course', $product->id)
                ->with('success', 'Curso concluído! Certificado emitido: ' . $cert->code);
        }

        return back()->with('success', 'Aula marcada como concluída.');
    }
}
