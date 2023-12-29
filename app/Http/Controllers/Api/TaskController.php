<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\Task;
use App\Traits\ResponseAPI;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{

    use ResponseAPI;

    public function createTask(Request $request)
    {
        $validator = validator($request->all(), [
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:New,Incomplete,Complete',
            'priority' => 'required|in:High,Medium,Low',
            'notes' => "required|array",
            'notes.*.subject' => 'required|string|max:255',
            'notes.*.attachments' => 'nullable|file',
            'notes.*.note' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        try {
            DB::beginTransaction();
            $task = new Task();
            $task->subject = $request->subject;
            $task->description = $request->description;
            $task->start_date = $request->start_date;
            $task->due_date = $request->due_date;
            $task->status = $request->status;
            $task->priority = $request->priority;
            if ($task->save()) {
                foreach ($request->notes as $key => $data) {
                    $note = new Note();
                    $note->subject = $data['subject'];
                    $note->task_id = $task->id;
                    $attachments = [];
                    foreach ($data['attachments'] as $file) {
                        $attachments[] = \Storage::disk('public')->put("task", $file, 'public');
                    }
                    $note->attachments = json_encode($attachments);
                    $note->note = $data['note'];
                    if (!$note->save()) {
                        DB::rollback();
                        return $this->error('Something Wentwrong in notes');
                    }
                }
                DB::commit();
                return $this->success("Task created", 200);
            } else {
                DB::rollback();
                return $this->error('Something Wentwrong');
            }

        } catch (Exception $e) {
            DB::rollback();
            return $this->error($e->getMessage());
        }
    }

    public function listTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filter.status' => 'nullable|in:New,Incomplete,Complete',
            'filter.due_date' => 'nullable|date',
            'filter.priority' => 'nullable|in:High,Medium,Low',
            'filter.notes' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors());
        }

        try {
            $statusFilter = $request->input('filter.status');
            $dueDateFilter = $request->input('filter.due_date');
            $priorityFilter = $request->input('filter.priority');
            $notesFilter = $request->input('filter.notes');

            $tasks = Task::with('notes')->withCount('notes')->orderBy('notes_count', 'asc')
                ->orderByRaw("FIELD(priority, 'High', 'Medium', 'Low') DESC");
            if ($statusFilter) {
                $tasks->where('status', $statusFilter);
            }

            if ($dueDateFilter) {
                $tasks->where('due_date', $dueDateFilter);
            }

            if ($priorityFilter) {
                $tasks->where('priority', $priorityFilter);
            }

            if ($notesFilter) {
                $tasks->whereHas('notes', function ($query) use ($notesFilter) {
                    $query->where('note', 'LIKE', "%$notesFilter%");
                });
            }
            $tasks = $tasks->get();
            if ($tasks) {
                return $this->success($tasks, 200);
            } else {
                return $this->error('No data');
            }

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
