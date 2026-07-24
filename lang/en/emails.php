<?php

return [
    // Shared notification chrome (used in vendor/notifications/email.blade.php)
    'hello' => 'Hello!',
    'whoops' => 'Whoops!',
    'regards' => 'Regards,',
    'trouble_link' => "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\ninto your web browser:",

    // Password reset
    'reset' => [
        'subject' => 'Reset your password',
        'intro' => 'You are receiving this email because we received a password reset request for your account.',
        'action' => 'Reset Password',
        'expires' => 'This password reset link will expire in :count minutes.',
        'no_action' => 'If you did not request a password reset, no further action is required.',
    ],

    'set_password' => [
        'subject' => 'Set your password',
        'intro' => 'An account has been created for you. Click the button below to set your password and get started.',
        'action' => 'Set Password',
        'expires' => 'This link will expire in :count minutes.',
        'no_action' => 'If you were not expecting this email, you can safely ignore it.',
    ],

    'invitation' => [
        'subject' => "You've been invited to :app",
        'title' => "You've been invited to :app",
        'heading' => "You've been invited",
        'body' => "You've been invited to join :app. Click the button below to set up your account and get started.",
        'cta' => 'Accept Invitation',
        'expires' => 'This link expires on :date.',
        'fallback' => "If the button doesn't work, copy and paste this URL into your browser:",
        'disclaimer' => "If you weren't expecting this invitation you can safely ignore this email.",
    ],

    'setup' => [
        'subject' => 'Set up your :app instance',
        'title' => 'Set up your :app instance',
        'heading' => 'Welcome to :app',
        'body' => 'Thanks for choosing :app. Click the button below to create your super admin account and finish setting up your instance.',
        'cta' => 'Complete setup',
        'expires' => 'This link expires on :date.',
        'fallback' => "If the button doesn't work, copy the following URL into your browser:",
        'disclaimer' => "If you weren't expecting this email, you can safely ignore it.",
    ],

    'contact_admin' => [
        'subject' => ':app — New contact form submission',
        'heading' => 'New contact form submission',
        'label_name' => 'Name',
        'label_email' => 'E-Mail',
        'label_phone' => 'Phone',
        'label_message' => 'Message',
        'sent_on' => 'Sent on :date',
    ],

    'contact_confirmation' => [
        'subject' => 'Confirmation: your message has been received',
        'heading' => 'Thank you for your message',
        'greeting' => 'Hello :name,',
        'body' => 'We have received your message and will get back to you as soon as possible.',
        'label' => 'Your message:',
        'regards' => 'Kind regards,',
    ],

    'common' => [
        'all_rights_reserved' => 'All rights reserved.',
    ],

    'notifications' => [
        'view_action' => 'View',
        'record_assigned' => [
            'subject' => ':module assigned to you',
            'body' => '**:user** assigned you the **:module**: **:record**.',
        ],
        'meeting_invite' => [
            'subject' => "You've been invited to a meeting",
            'body' => '**:user** invited you to **:meeting**.',
        ],
        'task_due_soon' => [
            'subject' => 'Task due soon',
            'body' => '**:task** is due **:due**.',
        ],
        'invite_accepted' => [
            'subject' => 'Your invite was accepted',
            'body' => '**:user** accepted their invitation and created an account.',
        ],
        'invite_expired' => [
            'subject' => 'Your invite has expired',
            'body' => 'The invitation sent to **:email** has expired.',
        ],
        'record_activity' => [
            'subject' => 'Activity on your :module',
            'body' => [
                'updated' => '**:user** updated the **:module**: **:record**.',
                'deleted' => '**:user** deleted the **:module**: **:record**.',
                'linked' => '**:user** linked a new activity to the **:module**: **:record**.',
            ],
        ],
        'impersonated' => [
            'subject' => 'Your account was accessed',
            'body' => '**:user** impersonated your account on **:time**.',
        ],
    ],
];
