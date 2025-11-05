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
use App\Mail\ContactMessageConfirmation;
use App\Models\Email;

class ContactMessageController extends Controller
{
  public function store(Request $request)
  {
    // validate
    $data = $request->validate([
        'name'    => ['required', 'string', 'max:150'],
        'email'   => ['nullable', 'email', 'max:190'],
        'phone'   => ['nullable', 'string', 'max:50'],
        'message' => ['nullable', 'string', 'max:5000'],
    ]);

    $ip = $request->ip();
    $whitelisted = IpWhitelist::where('ip', $ip)->where('active', true)->exists();   

    // check white list or if visitor had already sent a message within the last 24h
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
    
    // store message
    $contactMessage = ContactMessage::create([
        'name'       => $data['name'],
        'email'      => $data['email'] ?? null,
        'phone'      => $data['phone'] ?? null,
        'message'    => $data['message'],
        'status'     => 'new',
        'ip'         => $request->ip(),
        'user_agent' => substr((string) $request->userAgent(), 0, 255),
    ]);
    
    /*// send confirmations and store emails sent
    try {
      // notify admin of a new lead
      Mail::to(config('mail.admin_address'))->send(new ContactMessageReceived($contactMessage));

      //store email in table and wait for sending confirmation fro the worker
      Email::create([
        'to' => config('mail.admin_address'),
        'subject' => 'New contact message received',
        'mailable_class' => ContactMessageReceived::class,
        'related_id' => $contactMessage->id,
      ]);

      // notify visitor that we recieved their message
      if (!empty($contactMessage->email)) {
        Mail::to($contactMessage->email)
            ->send(new ContactMessageConfirmation($contactMessage));

        Email::create([
            'to' => $contactMessage->email,
            'subject' => 'Confirmation: your message was received',
            'mailable_class' => ContactMessageConfirmation::class,
            'related_id' => $contactMessage->id,
        ]);
      }
    } catch (\Throwable $e) {
      \Log::error('Contact email failed', [
        'error' => $e->getMessage(),
        'contact_id' => $contactMessage->id,
      ]);
    }
*/

try {
    // Admin email queued
    $admin = "simo.hamzaoui.1993@gmail.com";

    $adminLog = Email::create([
        'to' =>  $admin,
        'subject' => 'New contact message received',
        'mailable_class' => ContactMessageReceived::class,
        'related_id' => $contactMessage->id,
        'status' => 'queued',
    ]);

    Mail::to($admin)
        ->queue((new ContactMessageReceived($contactMessage))
            ->withSentEmailId($adminLog->id));

    // Confirmation to user
    if (!empty($contactMessage->email)) {
        $userLog = Email::create([
            'to' => $contactMessage->email,
            'subject' => 'Confirmation: your message was received',
            'mailable_class' => ContactMessageConfirmation::class,
            'related_id' => $contactMessage->id,
            'status' => 'queued',
        ]);

        Mail::to($contactMessage->email)
            ->queue((new ContactMessageConfirmation($contactMessage))
                ->withSentEmailId($userLog->id));
    }

} catch (\Throwable $e) {
    \Log::error('Failed to queue contact mail', [
        'error' => $e->getMessage(),
        'contact_id' => $contactMessage->id,
    ]);
}

    if ($request->wantsJson()) {
        return response()->json(['ok' => true, 'message' => 'Nachricht gesendet.']);
    }

    return back()->with('success', 'Nachricht gesendet.');
  }
}






