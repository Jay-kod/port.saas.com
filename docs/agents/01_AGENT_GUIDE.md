# 01_AGENT_GUIDE.md — Dashboard Visual Hierarchy & Color Cue Reference

> **Canonical Architecture Reference:** This document defines the permanent visual design standard for DevFolio's multi-tenant dashboards and master control panels. All future AI sessions, developers, and UI components must follow these tokens and role boundaries without deviation.

---

## 1. Visual Hierarchy & Security Boundaries

DevFolio dashboards use a **4-tier color cue system** to provide instant visual context, preventing accidental cross-tenant actions and reinforcing platform security boundaries at a glance.

```
+-----------------------------------------------------------------------------------+
| TENANT WORKSPACES (Cohesive Family: Green -> Teal -> Slate)                       |
|                                                                                   |
|  [1] Portfolio Owner (Green)      [2] Agency Owner (Teal)     [3] Team Member     |
|      #16A34A / #22C55E                #0D9488 / #14B8A6           (Slate Blue)    |
|      Core Personal Portfolio          Multi-Client Hub            #475569/#64748B |
|      (Default Brand Home)             (One Tier Up)               (Supporting)    |
+-----------------------------------------------------------------------------------+
                                         │
                 DELIBERATE HARD VISUAL  │  SECURITY BOUNDARY
                 BREAK                   ▼
+-----------------------------------------------------------------------------------+
| PLATFORM ROOT (High-Privilege Security Zone)                                      |
|                                                                                   |
|  [4] Super Admin Master Control (Amber / Warning Orange)                          |
|      #D97706 / #F59E0B                                                            |
|      Global Platform Operations (System-wide Destructive & Audit Privileges)      |
+-----------------------------------------------------------------------------------+
```

### Why This Hierarchy (Visual & Security Logic):
1. **Green → Teal → Slate (Tenant Family)**: These three panels are part of the same tenant workspace system with varying permissions. Using closely related tones (brand green moving to premium teal, down to neutral slate) keeps the tenant experience unified without jarring color clashes.
2. **Amber / Orange (Super Admin Break)**: Breaks the green/teal/slate family hard. Super Admin is architecturally distinct with cross-tenant visibility and platform-wide destructive capabilities. The warm amber/orange warning tone ensures an operator instantly knows they are in high-privilege master control before making changes.

---

## 2. Token & Color Specification Matrix

| Dashboard Role | Context / Condition | Primary Accent | Dark Glow / Accent | CSS Variables Specification |
|---|---|---|---|---|
| **1. Portfolio Owner** | Default tenant owner on Free/Pro plan accessing `/dashboard` | `#16A34A` | `#22C55E` | `--panel-accent: #16A34A;`<br/>`--panel-accent-glow: rgba(34, 197, 94, 0.16);`<br/>`--panel-accent-subtle: rgba(34, 197, 94, 0.10);`<br/>`--panel-badge-bg: rgba(34, 197, 94, 0.15);`<br/>`--panel-badge-text: #4ade80;`<br/>`--panel-badge-border: rgba(34, 197, 94, 0.30);` |
| **2. Agency Owner** | Account owner on Agency tier plan or accessing `/agency` | `#0D9488` | `#14B8A6` | `--panel-accent: #0D9488;`<br/>`--panel-accent-glow: rgba(20, 184, 166, 0.16);`<br/>`--panel-accent-subtle: rgba(20, 184, 166, 0.10);`<br/>`--panel-badge-bg: rgba(20, 184, 166, 0.15);`<br/>`--panel-badge-text: #2dd4bf;`<br/>`--panel-badge-border: rgba(20, 184, 166, 0.30);` |
| **3. Team Member** | Invited member with `editor` or `viewer` role on shared account | `#475569` | `#64748B` | `--panel-accent: #475569;`<br/>`--panel-accent-glow: rgba(100, 116, 139, 0.16);`<br/>`--panel-accent-subtle: rgba(100, 116, 139, 0.10);`<br/>`--panel-badge-bg: rgba(100, 116, 139, 0.15);`<br/>`--panel-badge-text: #94a3b8;`<br/>`--panel-badge-border: rgba(100, 116, 139, 0.30);` |
| **4. Super Admin** | User with `is_super_admin = true` in `/super-admin` | `#D97706` | `#F59E0B` | `--panel-accent: #D97706;`<br/>`--panel-accent-glow: rgba(245, 158, 11, 0.18);`<br/>`--panel-accent-subtle: rgba(245, 158, 11, 0.10);`<br/>`--panel-badge-bg: rgba(245, 158, 11, 0.15);`<br/>`--panel-badge-text: #fbbf24;`<br/>`--panel-badge-border: rgba(245, 158, 11, 0.30);` |

---

## 3. Blade Layout Integration Pattern

All dashboard blade views inherit their color tokens via scoped CSS variables defined on the root layout (`resources/views/layouts/dashboard.blade.php` and `resources/views/layouts/super-admin.blade.php`).

### Dynamic Theme Resolution in `layouts/dashboard.blade.php`:

```php
@php
    $user = auth()->user();
    $account = $user?->defaultTenant ?? $user?->accounts()->first();
    $role = $account ? $account->getUserRole($user) : 'owner';
    $isAgency = ($account?->plan_slug === 'agency') || request()->routeIs('agency*');

    if ($isAgency && $role === 'owner') {
        $panelType = 'agency';
        $panelLabel = 'Agency Workspace';
        $accent = '#0D9488';
        $accentDark = '#14B8A6';
        $glowRgba = 'rgba(20, 184, 166, 0.16)';
        $subtleRgba = 'rgba(20, 184, 166, 0.10)';
        $badgeText = '#2dd4bf';
        $badgeBorder = 'rgba(20, 184, 166, 0.30)';
    } elseif ($role === 'editor' || $role === 'viewer') {
        $panelType = 'member';
        $panelLabel = 'Team Workspace (' . ucfirst($role) . ')';
        $accent = '#475569';
        $accentDark = '#64748B';
        $glowRgba = 'rgba(100, 116, 139, 0.16)';
        $subtleRgba = 'rgba(100, 116, 139, 0.10)';
        $badgeText = '#94a3b8';
        $badgeBorder = 'rgba(100, 116, 139, 0.30)';
    } else {
        $panelType = 'owner';
        $panelLabel = 'Portfolio Owner';
        $accent = '#16A34A';
        $accentDark = '#22C55E';
        $glowRgba = 'rgba(34, 197, 94, 0.16)';
        $subtleRgba = 'rgba(34, 197, 94, 0.10)';
        $badgeText = '#4ade80';
        $badgeBorder = 'rgba(34, 197, 94, 0.30)';
    }
@endphp

<style>
    :root {
        --panel-accent: {{ $accent }};
        --panel-accent-dark: {{ $accentDark }};
        --panel-accent-glow: {{ $glowRgba }};
        --panel-accent-subtle: {{ $subtleRgba }};
        --panel-badge-text: {{ $badgeText }};
        --panel-badge-border: {{ $badgeBorder }};
    }
</style>
```

### UI Elements Styled by CSS Variables:
1. **Sidebar Brand Logo Gradient & Icon**: Dynamically colored using `--panel-accent`.
2. **Radial Background Glow**: Uses radial gradients positioned at top and bottom with `--panel-accent-glow`.
3. **Active Sidebar Navigation Links**: Highlights active routes with `--panel-accent-subtle` background and `--panel-accent` active pill bar.
4. **Role Pill Badge**: Rendered in the sidebar and top navigation displaying `$panelLabel`.
5. **Glass Card Hover Accents**: Card borders and shadows illuminate with `--panel-accent` on hover.

---

## 4. Testing & Verification Requirements

When modifying any dashboard layout or adding new dashboard views:
- Verify that **Portfolio Owner** displays Green accents (`#16A34A` / `#22C55E`).
- Verify that **Agency Owner** displays Teal accents (`#0D9488` / `#14B8A6`).
- Verify that **Team Member** (Editor/Viewer) displays Slate Blue accents (`#475569` / `#64748B`).
- Verify that **Super Admin** displays Amber accents (`#D97706` / `#F59E0B`).
- Always run `php artisan test --filter=DashboardColorCueTest` to confirm no visual role regression.
