<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     *
     * `users.email` cannot be changed here. The runtime role holds no UPDATE on
     * that column, because a changed address redirects the password-reset flow
     * and would be an account-takeover path. Changing an address is an audited
     * administrative action (AUTH.md §7), not a self-service profile edit.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->validated());

        $user->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     *
     * Self-service account deletion is deliberately not offered (Story 1.2).
     * User records are never hard-deleted (AUTH.md §7): historical
     * `locations`, `asset_custody`, `inspections`, `work_order_events`,
     * `attachments` and `audit_events` reference the acting user. Access is
     * revoked by suspending the membership instead, and NDPA erasure runs
     * through the audited maintenance path (Story 5.5).
     *
     * The runtime role additionally holds no DELETE privilege on `users`
     * (RLS-POLICIES.md §4.1), so this is enforced in the database as well as
     * here.
     */
    public function destroy(): never
    {
        abort(403, 'Accounts are closed by an organisation administrator.');
    }
}
