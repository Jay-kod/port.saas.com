<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Phase 4 (docs/agents/03-BILLING-ONBOARDING-ROUTING.md):
 * Thrown when an Account has exceeded its monthly AI generation quota.
 */
class AiQuotaExceededException extends RuntimeException
{
}
