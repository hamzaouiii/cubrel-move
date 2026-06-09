<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\PdfTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PdfTemplatesController extends Controller
{
    public function index()
    {
        $templates = PdfTemplate::orderBy('module_slug')->orderByDesc('is_default')->get();

        return Inertia::render('Settings/PdfTemplates/Index', [
            'templates' => $templates,
        ]);
    }

    public function setDefault(Request $request, int $id)
    {
        $template = PdfTemplate::findOrFail($id);

        // Clear existing default for this module, then set the new one.
        PdfTemplate::where('module_slug', $template->module_slug)
            ->update(['is_default' => false]);

        $template->update(['is_default' => true]);

        return back()->with('success', 'Default template updated.');
    }
}
