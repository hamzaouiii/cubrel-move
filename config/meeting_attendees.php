<?php

return [
    'rsvp_statuses' => [
        ['value' => 'invited', 'label' => 'modules.meeting_attendees.rsvp_statuses.invited'],
        ['value' => 'accepted', 'label' => 'modules.meeting_attendees.rsvp_statuses.accepted'],
        ['value' => 'declined', 'label' => 'modules.meeting_attendees.rsvp_statuses.declined'],
        ['value' => 'tentative', 'label' => 'modules.meeting_attendees.rsvp_statuses.tentative'],
    ],

    'attendance_statuses' => [
        ['value' => null, 'label' => 'modules.meeting_attendees.attendance_statuses.not_recorded'],
        ['value' => 'attended', 'label' => 'modules.meeting_attendees.attendance_statuses.attended'],
        ['value' => 'no_show', 'label' => 'modules.meeting_attendees.attendance_statuses.no_show'],
    ],

    'roles' => [
        ['value' => 'organizer', 'label' => 'modules.meeting_attendees.roles.organizer'],
        ['value' => 'required', 'label' => 'modules.meeting_attendees.roles.required'],
        ['value' => 'optional', 'label' => 'modules.meeting_attendees.roles.optional'],
    ],

   
    'source_modules' => [
        ['value' => 'contacts', 'label' => 'modules.contacts.single_label', 'source_type' => 'contact'],
        ['value' => 'leads', 'label' => 'modules.leads.single_label', 'source_type' => 'lead'],
        ['value' => 'users', 'label' => 'modules.users.single_label', 'source_type' => 'user'],
    ],
];
