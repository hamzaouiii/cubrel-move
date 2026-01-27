<?php

namespace App\Http\Controllers;

use App\Models\Modules\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
  public function index()
  {
    return Lead::latest()->get();
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'first_name'  => ['nullable', 'string', 'max:255'],
      'last_name'   => ['nullable', 'string', 'max:255'],
      'email'       => ['nullable', 'email', 'max:255'],
      'phone'       => ['nullable', 'string', 'max:255'],
      'company'     => ['nullable', 'string', 'max:255'],
      'street'      => ['nullable', 'string', 'max:255'],
      'city'        => ['nullable', 'string', 'max:255'],
      'zip'         => ['nullable', 'string', 'max:20'],
      'description' => ['nullable', 'string'],
    ]);

    $lead = Lead::create($data);

    return response()->json(['message' => 'Lead created successfully', 'lead' => $lead], 201);
  }

  public function show(Lead $lead)
  {
    return $lead;
  }

  public function update(Request $request, Lead $lead)
  {
    $data = $request->validate([
      'first_name'  => ['nullable', 'string', 'max:255'],
      'last_name'   => ['nullable', 'string', 'max:255'],
      'email'       => ['nullable', 'email', 'max:255'],
      'phone'       => ['nullable', 'string', 'max:255'],
      'company'     => ['nullable', 'string', 'max:255'],
      'street'      => ['nullable', 'string', 'max:255'],
      'city'        => ['nullable', 'string', 'max:255'],
      'zip'         => ['nullable', 'string', 'max:20'],
      'description' => ['nullable', 'string'],
    ]);

    $lead->update($data);

    return response()->json(['message' => 'Lead updated successfully', 'lead' => $lead]);
  }

  public function destroy(Lead $lead)
  {
    $lead->delete();
    return response()->json(['message' => 'Lead deleted successfully']);
  }
}
