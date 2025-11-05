<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use \App\Models\ContactMessage;
use \App\Models\IpWhitelist;
use App\Mail\ContactMessageReceived;

class ContactMessageController extends Controller
{
  public function store(Request $request)
  {
    $data = $request->validate([
        'name'    => ['required', 'string', 'max:150'],
        'email'   => ['nullable', 'email', 'max:190'],
        'phone'   => ['nullable', 'string', 'max:50'],
        'message' => ['nullable', 'string', 'max:5000'],
    ]);

    $ip = $request->ip();
    $whitelisted = IpWhitelist::where('ip', $ip)->where('active', true)->exists();    
    
    if(! $whitelisted){
      $alreadySent = DB::table('contact_messages')
          ->where('ip', $ip)
          ->where('created_at', '>=', now()->subDay())
          ->exists();
          

      if ($alreadySent) {
        $lastMessageAt = DB::table('contact_messages')
        ->where('ip', $ip)
        ->latest('created_at')
        ->value('created_at');

        $last = $lastMessageAt instanceof Carbon ? $lastMessageAt : Carbon::parse($lastMessageAt);
        $resetAt = $last->copy()->addDay();
        $dateRateResets = $resetAt->timezone(config('app.timezone'))->format('d.m.Y H:i');

        $msg = "Um Spam zu vermeiden, ist das Senden von Nachrichten auf eine pro 24 Stunden und IP-Adresse begrenzt. "
          . "Sie können ab dem {$dateRateResets} erneut eine Nachricht senden.";
        if ($request->wantsJson()) {
          return response()->json([
            'errors' => [
              'message' =>  $msg 
            ]
          ], 429);
        }
        return back()->withErrors([
          'message' =>  $msg 
        ]);
      }
    }

    $contactMessage = ContactMessage::create([
        'name'       => $data['name'],
        'email'      => $data['email'] ?? null,
        'phone'      => $data['phone'] ?? null,
        'message'    => $data['message']?? '',
        'status'     => 'new',
        'ip'         => $request->ip(),
        'user_agent' => substr((string) $request->userAgent(), 0, 255),
    ]);

    try {
     Mail::to('simo.hamzaoui.1993@gmail.com')->send(new ContactMessageReceived($contactMessage));
    } catch (\Throwable $e) {
      dd('Contact email failed: '.$e->getMessage(), ['contact_id' => $contactMessage->id]);
    }
    if ($request->wantsJson()) {
        return response()->json(['ok' => true, 'message' => 'Nachricht gesendet.']);
    }

    return back()->with('success', 'Nachricht gesendet.');
  }
}






