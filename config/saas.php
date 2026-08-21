<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SaaS Mode
    |--------------------------------------------------------------------------
    |
    | Controls whether this codebase runs as a single-tenant, self-hosted
    | product (false — the default, "Path A") or as a multi-tenant SaaS
    | (true — "Path B"). See AGENTS.md at the project root for the full
    | transformation plan. This flag is the rollback safety valve
    | referenced in Phase 10: if anything goes wrong post-launch, setting
    | this back to false restores the original single-tenant behavior,
    | since that code path is never deleted, only extended.
    |
    */
    'mode' => env('SAAS_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Plans (Phase 4)
    |--------------------------------------------------------------------------
    |
    | Placeholder for plan definitions. Populate this once Phase 4
    | (billing, plans, usage metering) is implemented. Intentionally kept
    | in config rather than a DB table until admin-editable pricing is
    | actually needed — see docs/agents/03-BILLING-ONBOARDING-ROUTING.md.
    |
    */
    'plans' => [],

];
