<?php

/**
 * Phase 4 (docs/agents/03-BILLING-ONBOARDING-ROUTING.md):
 * SaaS plan tier definitions and feature limits.
 */
return [
    'free' => [
        'name' => 'Free',
        'stripe_price_id' => null,
        'max_profiles' => 1,
        'ai_generations_per_month' => 3,
        'custom_domain' => false,
        'remove_branding' => false,
    ],
    'pro' => [
        'name' => 'Pro Developer',
        'stripe_price_id' => env('STRIPE_PRICE_PRO'),
        'max_profiles' => 1,
        'ai_generations_per_month' => null, // null = unlimited
        'custom_domain' => true,
        'remove_branding' => true,
    ],
    'agency' => [
        'name' => 'Agency / Team',
        'stripe_price_id' => env('STRIPE_PRICE_AGENCY'),
        'max_profiles' => null, // unlimited (Phase 8 feature)
        'ai_generations_per_month' => null,
        'custom_domain' => true,
        'remove_branding' => true,
    ],
];
