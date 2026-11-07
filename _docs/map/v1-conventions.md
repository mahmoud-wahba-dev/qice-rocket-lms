# V1 conventions

The v1 redesign runs beside the legacy panel. Keep them separate.

## Placement

- New panel routes go only in `routes/panel_v1.php` (prefix `/v1`, names `panel.v1.*`). Never add them to `routes/panel.php` or `routes/admin.php`.
- Public site routes go in `routes/web.php` under the landing group (names `landing.v1.*`, no URL prefix).
- Controllers: `app/Http/Controllers/PanelV1/`. Shared code: `PanelV1/Support/`.
- Views: `resources/views/panel_v1/{student,instructor,organization,admin}/{layouts,pages,components}/`.
- Assets: `resources/css/panel_v1/` and `resources/js/panel_v1/`. Vite entries are in `vite.config.js`.
- Never extend `design_1/*` layouts for new screens.

## Routes

- Student, instructor and organization routes use middleware `impersonate, panel, share, check_maintenance, check_restriction`.
- Admin routes use `web, admin`. Their `/{section}` catch-all must stay last in the system group.

## Role checks

The `panel` middleware admits every non-admin role, so each action must start with the controller's resolver and return early on a redirect:

```php
$user = $this->resolveStudent($request);
if ($user instanceof \Illuminate\Http\RedirectResponse) {
    return $user;
}
```

Resolvers: `resolveStudent()` (student, course player), `resolveInstructor()`, `resolveOrganization()`, and `resolveAdmin()` or `renderAdmin()` for admin. Instructor actions must also check ownership with `teacherWebinarOrFail()`.

## Pages

- The controller sets an Arabic `pageTitle` and passes plain arrays to the view. There are no view models.
- The page does `@extends('panel_v1.<role>.layouts.app')` and fills `@section('content')`. `@stack('head')` and `@stack('scripts')` are available.
- Panels reuse partials with `@include`. Only landing uses `<x-landing_v1::...>` components.
- JS is vanilla modules plus FlyonUI. No jQuery, Alpine or Livewire in v1.

## Styling gotchas

- Content must sit inside `#landing-v1-app`. Tailwind has `important: "#landing-v1-app"`, so utilities don't apply outside it.
- Tailwind `content` now scans `panel_v1/{student,instructor,admin,organization,components}`, `landing_v1`, and `views/components/v1`. It still omits `panel_v1/certificates`; a class used only there is not generated. Write full class names (never build them in JS) so Tailwind and iconify can see them.
- Use tokens: `primary` (`#0f4c45`), `secondary`, `gold`, `color2`, `d9`, `fa`, and px scales such as `rounded-12px`, `text-14px`. Don't add more hardcoded `#0F3D36`.
- Write for RTL: use logical `ps-`/`ms-` utilities and `rtl:` variants.
- Text: `trans('update.key')` with entries in both `lang/ar/update.php` and `lang/en/update.php`.

## Confirmations, alerts and dialogs (no browser dialogs)

Never use `alert()`, `confirm()`, `prompt()` or `onsubmit="return confirm(...)"`. Use the shared UI (brand colours, RTL):

- **Confirm an action:** `data-confirm="..."` on the form, or on the button/link. Optional `data-confirm-title`, `data-confirm-ok` (button label), `data-confirm-tone="danger|warning|primary"` (tone defaults to danger when the text mentions حذف/إزالة/رفض). In `panel_v1/components/actions-dropdown` items use `confirm`, `confirm_title`, `confirm_ok`, `tone`. From JS: `await window.confirmModal({title, message, ok, tone})`. Component: `resources/views/components/v1/confirm-modal.blade.php`, loaded in every layout through `components/v1/flash`.
- **Notify the user:** `window.showCartToast(title, msg, 'success'|'error')` or `session('toast')` from a controller.
- **Bigger message dialog:** copy `landing_v1/components/instructor-pending-modal.blade.php` (a modal the user closes themselves).

```blade
<form method="POST" action="{{ route('...') }}"
      data-confirm="سيتم حذف العنصر نهائياً." data-confirm-title="حذف العنصر" data-confirm-ok="حذف" data-confirm-tone="danger">
    @csrf
    <button type="submit">حذف</button>
</form>
```
