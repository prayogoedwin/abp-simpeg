<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checklist;
use App\Models\ChecklistDetail;
use App\Models\ChecklistTemplate;
use Illuminate\Http\Request;

use OpenApi\Attributes as OA;



#[OA\Info(
    version: '1.0.0',
    title: 'Checklist API Documentation',
    description: 'API untuk mengelola checklist template dan pengisian checklist'
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
class ChecklistController extends Controller
{
    #[OA\Get(
        path: '/api/checklist/',
        operationId: 'getTemplates',
        summary: 'Mendapatkan semua template checklist untuk instansi user yang sedang login',
        description: 'Mengembalikan daftar template checklist untuk instansi member yang login',
        tags: ['Checklist'],
        // security: [new OA\SecurityRequirement(requirements: ['bearerAuth' => []])]
    )]
    #[OA\Response(
        response: 200,
        description: 'Sukses mengambil data template',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'Daily Cleaning Checklist'),
                            new OA\Property(property: 'instansi_id', type: 'integer', example: 1),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
                            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
                        ],
                        type: 'object'
                    )
                )
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Token tidak valid atau tidak disertakan',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated'),
            ],
            type: 'object'
        )
    )]
    public function index()
    {
        $member_instansi_id = auth()->user()->instansi_id;

        $templates = ChecklistTemplate::where('instansi_id', $member_instansi_id)->get();

        return response()->json([
            'success' => true,
            'data' => $templates
        ]);
    }

    #[OA\Post(
        path: '/api/checklist/inputdata',
        operationId: 'getInputData',
        summary: 'Mendapatkan detail template checklist',
        description: 'Mengambil detail template dan pertanyaan-pertanyaan di dalamnya sebelum user mengisi',
        tags: ['Checklist'],
        // security: [new OA\SecurityRequirement(requirements: ['bearerAuth' => []])]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['template_id'],
            properties: [
                new OA\Property(property: 'template_id', type: 'integer', description: 'ID template checklist', example: 1),
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Sukses mengambil data template dan detail pertanyaan',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(
                            property: 'checklist',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Daily Cleaning Checklist'),
                                new OA\Property(property: 'instansi_id', type: 'integer', example: 1),
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'details',

                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'label', type: 'string', example: 'Status Kehadiran'),
                                    new OA\Property(property: 'type', type: 'string', enum: ['radio', 'checkbox', 'text', 'textarea'], example: 'radio'),
                                    new OA\Property(property: 'options', type: 'string', nullable: true, example: 'Hadir, Izin, Sakit, Alfa'),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
                                ],
                                type: 'object'
                            )
                        ),
                    ],
                    type: 'object'
                )
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Validasi gagal (template_id tidak ditemukan)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'message', type: 'string', example: 'The selected template id is invalid.'),
            ],
            type: 'object'
        )
    )]
    public function inputdata(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:checklist_templates,id',
        ]);

        $checklist = ChecklistTemplate::find($request->template_id);

        $details = $checklist->details()->get();

        return response()->json([
            'success' => true,
            'data' => [
                'checklist' => $checklist,
                'details' => $details
            ]
        ]);
    }

    #[OA\Post(
        path: '/api/checklist/submit',
        operationId: 'submitChecklist',
        summary: 'Menyimpan jawaban checklist',
        description: 'Menyimpan hasil pengisian checklist yang sudah diisi oleh member',
        tags: ['Checklist'],
        // security: [new OA\SecurityRequirement(requirements: ['bearerAuth' => []])]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['template_id', 'answers'],
            properties: [
                new OA\Property(property: 'template_id', type: 'integer', description: 'ID template checklist', example: 1),
                new OA\Property(
                    property: 'answers',
                    type: 'array',
                    description: 'Array jawaban sesuai urutan detail pertanyaan dalam template, bisa pisahkan dengan koma saja atau bentuk array',
                    items: new OA\Items(
                        oneOf: [
                            new OA\Schema(type: 'string', description: 'Untuk tipe radio / text'),
                            new OA\Schema(type: 'array', description: 'Untuk tipe checkbox (multiple values)', items: new OA\Items(type: 'string'))
                        ]
                    ),
                    example: ['Alfa', ['Depan Lift', 'Depan Lavender'], ['Pembersihan'], 'Ahmad Ferry']
                ),
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Checklist berhasil disimpan',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Checklist tercatat'),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 10),
                        new OA\Property(property: 'instansi_id', type: 'integer', example: 1),
                        new OA\Property(property: 'member_id', type: 'integer', example: 1),
                        new OA\Property(property: 'template_name', type: 'string', example: 'Daily Cleaning Checklist'),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
                        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
                        new OA\Property(
                            property: 'details',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 45),
                                    new OA\Property(property: 'checklist_id', type: 'integer', example: 10),
                                    new OA\Property(property: 'type', type: 'string', example: 'radio'),
                                    new OA\Property(property: 'label', type: 'string', example: 'Status Kehadiran'),
                                    new OA\Property(property: 'options', type: 'string', nullable: true, example: 'Hadir, Izin, Sakit, Alfa'),
                                    new OA\Property(property: 'value', type: 'string', example: 'Alfa'),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
                                ],
                                type: 'object'
                            )
                        ),
                    ],
                    type: 'object'
                )
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Token tidak valid atau tidak disertakan',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated'),
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Validasi gagal (template_id tidak valid)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'message', type: 'string', example: 'The template id field is required.'),
                new OA\Property(property: 'errors', type: 'object', nullable: true),
            ],
            type: 'object'
        )
    )]
    public function submit(Request $request)
    {
        // dd($request->all());
        $checklistTemplate = ChecklistTemplate::find($request->template_id);


        $checklist = Checklist::create([
            'template_name' => $checklistTemplate->name,
            'instansi_id' => $checklistTemplate->instansi_id,
            'member_id' => auth()->user()->id,
        ]);



        foreach ($request->answers as $detailId => $answer) {

            // Jika tipe datanya checkbox, $answer akan berupa array. 
            // Kita ubah menjadi string (comma separated) agar bisa disimpan di kolom string/text.
            $finalAnswer = is_array($answer) ? implode(', ', $answer) : $answer;

            // dd($answer, $detailId, $finalAnswer);
            $detailIdforAPI = $detailId + 1; // gatau kenapa kalau lewat api, id nya jadi ke offset 1. Jadi misal id asli 1, di API jadi 2. Jadi kita offset -1 biar sesuai dengan id asli di database.

            ChecklistDetail::create([
                'checklist_id' => $checklist->id,
                'type' => $checklistTemplate->details()->where('id', $detailIdforAPI)->value('type'),
                'label' => $checklistTemplate->details()->where('id', $detailIdforAPI)->value('label'),
                'options' => $checklistTemplate->details()->where('id', $detailIdforAPI)->value('options'),
                'value' => $finalAnswer,
            ]);
        }

        // dd($checklist->load('details'));
        return response()->json([
            'success' => true,
            'message' => "Checklist tercatat",

            'data' => [
                'id' => $checklist->id,
                'instansi_id' => $checklist->instansi_id,
                'member_id' => $checklist->member_id,
                'details' => $checklist->details
            ],
        ]);
    }
}
