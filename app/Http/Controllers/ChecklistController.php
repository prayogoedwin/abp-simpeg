<?php
namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\ChecklistDetail;
use App\Models\ChecklistTemplate;
use Illuminate\Http\Request;
use PHPUnit\Metadata\Test;

class ChecklistController extends Controller
{

    public function index()
    {
        // dd(auth()->user()->instansi_id);
        $member_instansi_id = auth()->user()->instansi_id;

        // only get templates that their instansi_id is the same as the logged in user's instansi_id
        
        $templates = ChecklistTemplate::where('instansi_id', $member_instansi_id)->get();

        $quickinfo = [
            'logged_in_user' => auth()->user()->name,
            'member_instansi_id' => $member_instansi_id,
        ];


        return view('checklists.pilihTemplate', compact('templates', 'quickinfo'));
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
        // dd($request->all());
        $checklistTemplate = ChecklistTemplate::find($request->template_id);

        // dd($checklistTemplate);

        $checklist = Checklist::create([
            'checklist_template_id' => $checklistTemplate->id,
            'instansi_id' => $checklistTemplate->instansi_id,
            'member_id' => auth()->user()->id,
        ]);


        foreach ($request->answers as $detailId => $answer) {
            
            // Jika tipe datanya checkbox, $answer akan berupa array. 
            // Kita ubah menjadi string (comma separated) agar bisa disimpan di kolom string/text.
            $finalAnswer = is_array($answer) ? implode(', ', $answer) : $answer;
            
            ChecklistDetail::create([
                'checklist_id' => $checklist->id,
                'type' => $checklistTemplate->details()->where('id', $detailId)->value('type'),
                'label' => $checklistTemplate->details()->where('id', $detailId)->value('label'),
                'options' => $checklistTemplate->details()->where('id', $detailId)->value('options'),
                'value' => $finalAnswer,
            ]);
            
        }

        // dd($checklist->load('details'));

        return redirect()->route('checklist.success');
    }


    public function success()
    {
        return view('checklists.success');
    }
}