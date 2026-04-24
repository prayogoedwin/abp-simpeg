<?php
namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\ChecklistTemplate;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{

    public function index()
    {
        $templates = ChecklistTemplate::get();

        

        return view('checklists.pilihTemplate', compact('templates'));
    }



    public function inputdata(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:checklist_templates,id',
        ]);

        $checklist = ChecklistTemplate::find($request->template_id);

        $details = $checklist->details()->get();

        // dd($details);

        return view('checklists.form', compact('checklist', 'details'));
    }

    public function submit(Request $request)
    {
        dd($request->all());
        return back()->with('success', 'Checklist berhasil dikirim!');
    }
}