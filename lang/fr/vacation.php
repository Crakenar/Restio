<?php

return [
    'vacation_request' => 'Demande de congé',
    'vacation_requests' => 'Demandes de congé',
    'request_time_off' => 'Demander un congé',
    'my_requests' => 'Mes demandes',
    'team_requests' => 'Demandes de l\'équipe',

    'fields' => [
        'type' => 'Type',
        'start_date' => 'Date de début',
        'end_date' => 'Date de fin',
        'reason' => 'Raison',
        'status' => 'Statut',
        'days' => 'Jours',
    ],

    'types' => [
        'vacation' => 'Vacances',
        'sick_leave' => 'Congé maladie',
        'personal_day' => 'Jour personnel',
        'unpaid_leave' => 'Congé sans solde',
        'other' => 'Autre',
    ],

    'status' => [
        'pending' => 'En attente',
        'approved' => 'Approuvé',
        'rejected' => 'Rejeté',
    ],

    'actions' => [
        'submit_request' => 'Soumettre la demande',
        'approve' => 'Approuver',
        'reject' => 'Rejeter',
        'cancel' => 'Annuler la demande',
        'view' => 'Voir la demande',
    ],

    'messages' => [
        'request_submitted' => 'Votre demande de congé a été soumise',
        'request_approved' => 'La demande de congé a été approuvée',
        'request_rejected' => 'La demande de congé a été rejetée',
        'request_cancelled' => 'La demande de congé a été annulée',
        'insufficient_days' => 'Vous n\'avez pas assez de jours de congé',
        'overlapping_request' => 'Vous avez déjà une demande pour ces dates',
    ],

    'notifications' => [
        'new_request' => 'Nouvelle demande de congé de :name',
        'request_approved' => 'Votre demande de congé a été approuvée',
        'request_rejected' => 'Votre demande de congé a été rejetée',
    ],

    'stats' => [
        'days_remaining' => 'Jours restants',
        'days_used' => 'Jours utilisés',
        'days_total' => 'Total de jours',
        'pending_requests' => 'Demandes en attente',
    ],
];
