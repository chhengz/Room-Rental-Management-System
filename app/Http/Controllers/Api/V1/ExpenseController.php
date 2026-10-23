<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Expense::query()->with(['category', 'building']);
        if ($request->filled('expense_category_id')) {
            $query->where('expense_category_id', $request->integer('expense_category_id'));
        }
        if ($request->filled('building_id')) {
            $query->where('building_id', $request->integer('building_id'));
        }
        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->string('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->string('to_date'));
        }

        return $this->success($query->latest('expense_date')->paginate((int) $request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'building_id' => ['nullable', 'exists:buildings,id'],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['required', 'string'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);
        $data['recorded_by'] = $request->user()?->id;

        return $this->success(Expense::create($data), 'Expense created.', 201);
    }

    public function show(Expense $expense): JsonResponse
    {
        return $this->success($expense->load(['category', 'building']));
    }

    public function update(Request $request, Expense $expense): JsonResponse
    {
        $data = $request->validate([
            'expense_category_id' => ['sometimes', 'exists:expense_categories,id'],
            'building_id' => ['nullable', 'exists:buildings,id'],
            'expense_date' => ['sometimes', 'date'],
            'amount' => ['sometimes', 'numeric', 'gt:0'],
            'description' => ['sometimes', 'string'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);
        $expense->update($data);

        return $this->success($expense->fresh(), 'Expense updated.');
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();

        return $this->success(null, 'Expense deleted.');
    }
}
