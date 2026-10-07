<?php

namespace App\Http\Controllers;

use App\Models\CourseLesson;
use App\Models\CourseQuiz;
use App\Models\QuizAttempt;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizController extends Controller
{
    use LogsActivity;

    protected function ownLessonOrFail(CourseLesson $lesson): CourseLesson
    {
        $lesson->load('module.product');
        $user = auth()->user();
        if (($user->type ?? '') !== 'admin') {
            abort_unless((int) ($lesson->module->product->producer_id ?? 0) === (int) $user->id, 403);
        }

        return $lesson;
    }

    public function index(CourseLesson $lesson): View
    {
        $this->ownLessonOrFail($lesson);
        $quizzes = CourseQuiz::where('lesson_id', $lesson->id)->withCount('attempts')->get();

        return view('producer.quizzes', compact('lesson', 'quizzes'));
    }

    public function store(Request $request, CourseLesson $lesson): RedirectResponse
    {
        $this->ownLessonOrFail($lesson);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'questions_json' => 'required|string',
        ]);
        $questions = json_decode($data['questions_json'], true);
        if (! is_array($questions)) {
            return back()->withErrors(['questions_json' => 'JSON inválido.'])->withInput();
        }
        CourseQuiz::create([
            'lesson_id' => $lesson->id,
            'title' => $data['title'],
            'questions' => $questions,
            'passing_score' => $data['passing_score'] ?? 70,
        ]);

        return back()->with('success', 'Quiz criado.');
    }

    public function destroy(CourseQuiz $quiz): RedirectResponse
    {
        $this->ownLessonOrFail($quiz->lesson);
        $quiz->delete();

        return back()->with('success', 'Quiz removido.');
    }

    /** Aluno responde ao quiz. */
    public function answer(Request $request, CourseQuiz $quiz): RedirectResponse
    {
        $data = $request->validate(['answers' => 'required|array']);
        $questions = $quiz->questions ?? [];
        $correct = 0;
        foreach ($questions as $i => $q) {
            $given = $data['answers'][$i] ?? null;
            if ($given !== null && (string) $given === (string) ($q['answer'] ?? null)) {
                $correct++;
            }
        }
        $total = max(1, count($questions));
        $score = (int) round(($correct / $total) * 100);
        QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => auth()->id(),
            'score' => $score,
            'passed' => $score >= (int) ($quiz->passing_score ?? 70),
            'answers' => $data['answers'],
        ]);

        return back()->with('success', "Pontuação: {$score}% (".($score >= $quiz->passing_score ? 'aprovado' : 'reprovado').').');
    }
}
